<?php

namespace App\Services;

use App\Mail\InvoiceMailable;
use App\Models\Donation;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use Throwable;

class InvoiceService
{
    /**
     * @return array{invoice: Invoice, path: string, url: string}
     *
     * @throws MpdfException
     * @throws Throwable
     */
    public function generateForOrder(
        Order $order,
        string $series = 'FP',
        bool $sendEmail = false,
        bool $force = false
    ): array {
        $order->loadMissing(['items.product', 'addresses']);

        $lines = $this->orderLines($order);
        $shippingCost = (float) $order->shipping_cost;
        $vatRate = $order->vatRate();

        $subtotal = $this->calculateSubtotal($lines, $shippingCost);
        $vatAmount = round($subtotal * $vatRate, 2);
        $total = round($subtotal + $vatAmount, 2);

        $meta = [
            'shipping_cost' => $shippingCost,
            'payment_method' => $order->payment_method,
        ];

        $result = $this->createInvoice(
            invoiceable: $order,
            series: $series,
            lines: $lines,
            subtotal: $subtotal,
            vatRate: $vatRate,
            vatAmount: $vatAmount,
            total: $total,
            meta: $meta,
            force: $force
        );

        if ($sendEmail) {
            $this->sendInvoiceEmailForOrder($order, $result['invoice']);
        }

        return $result;
    }

    /**
     * @return array<int, array{name: string, quantity: int, unit_price: float, line_total: float}>
     */
    protected function orderLines(Order $order): array
    {
        return $order->items->map(function ($item) {
            $unitPrice = (float) ($item->product?->getPrice() ?? 0);

            return [
                'name' => (string) $item->product?->name,
                'quantity' => (int) $item->quantity,
                'unit_price' => $unitPrice,
                'line_total' => round($unitPrice * (int) $item->quantity, 2),
            ];
        })->toArray();
    }

    /**
     * @param  array<int, array{line_total: float}>  $lines
     */
    protected function calculateSubtotal(array $lines, float $additionalCost = 0): float
    {
        $linesTotal = array_sum(array_column($lines, 'line_total'));

        return round($linesTotal + $additionalCost, 2);
    }

    /**
     * @param  array<int, array{name: string, quantity: int, unit_price: float, line_total: float}>  $lines
     * @return array{invoice: Invoice, path: string, url: string}
     *
     * @throws MpdfException*@throws Throwable
     * @throws Throwable
     */
    protected function createInvoice(
        Model $invoiceable,
        string $series,
        array $lines,
        float $subtotal,
        float $vatRate,
        float $vatAmount,
        float $total,
        array $meta = [],
        bool $force = false
    ): array {
        // Enforce single invoice per invoiceable: reuse and update if it exists
        $existing = $invoiceable->invoices()->first();

        if ($existing) {
            // Update monetary fields and keep numbering
            $existing->update([
                'subtotal' => $subtotal,
                'vat_rate' => $vatRate,
                'vat_amount' => $vatAmount,
                'total' => $total,
            ]);
            $invoice = $existing;
            $series = $invoice->series; // ensure path matches existing series
        } else {
            $invoice = DB::transaction(function () use (
                $invoiceable,
                $series,
                $subtotal,
                $vatRate,
                $vatAmount,
                $total
            ) {
                $year = now()->year;
                $last = Invoice::query()
                    ->where('series', $series)
                    ->where('year', $year)
                    ->orderByDesc('sequence')
                    ->lockForUpdate()
                    ->first();
                $nextSeq = ($last?->sequence ?? 0) + 1;
                $number = sprintf('%s-%d-%06d', $series, $year, $nextSeq);

                return $invoiceable->invoices()->create([
                    'series' => $series,
                    'year' => $year,
                    'sequence' => $nextSeq,
                    'number' => $number,
                    'subtotal' => $subtotal,
                    'vat_rate' => $vatRate,
                    'vat_amount' => $vatAmount,
                    'total' => $total,
                    'currency' => 'EUR',
                    'storage_path' => '',
                ]);
            });
        }

        $html = View::make('pdf.invoice', [
            'invoice' => $invoice,
            'invoiceable' => $invoiceable,
            'subtotal' => $subtotal,
            'vatRate' => $vatRate,
            'vatAmount' => $vatAmount,
            'total' => $total,
            'lines' => $lines,
            'meta' => $meta,
            'settings' => $this->getSettings(),
        ])->render();

        $pdfContent = $this->generatePdf($html);
        $relativePath = $this->storePdf($pdfContent, $series, $invoice, $force);

        // Save storage path (overwrite if changed)
        if ($invoice->storage_path !== $relativePath) {
            $invoice->update(['storage_path' => $relativePath]);
        }

        // Debug: verify presence before attaching
        $disk = Storage::disk('public');
        if (config('app.debug') || env('INVOICE_DEBUG', false)) {
            \Log::info('[invoice-pdf] Pre-attach exists check', [
                'invoice_id' => $invoice->id,
                'number' => $invoice->number,
                'relative' => $relativePath,
                'exists_disk' => $disk->exists($relativePath),
                'exists_abs' => file_exists(storage_path('app/public/' . $relativePath)),
            ]);
        }

        $this->attachMedia($invoiceable, $relativePath, $force, $pdfContent);

        // Debug: verify presence after attaching
        if (config('app.debug') || env('INVOICE_DEBUG', false)) {
            \Log::info('[invoice-pdf] Post-attach exists check', [
                'invoice_id' => $invoice->id,
                'number' => $invoice->number,
                'relative' => $relativePath,
                'exists_disk' => $disk->exists($relativePath),
                'exists_abs' => file_exists(storage_path('app/public/' . $relativePath)),
            ]);
        }

        return [
            'invoice' => $invoice,
            'path' => $relativePath,
            'url' => $disk->url($relativePath),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function getSettings(): array
    {
        // Resolve logo path and content with support for SVG (inline) and raster (PNG/JPG)
        $logoAbsPath = '';
        $logoSvgContent = '';

        $configured = (string) setting('billing.logo_path', '');
        if ($configured !== '') {
            $candidate = storage_path('app/public/'.ltrim($configured, '/'));
            if (file_exists($candidate)) {
                $logoAbsPath = $candidate;
            }
        }
        if ($logoAbsPath === '') {
            $fallbackSvg = public_path('images/logo-fundacion-horizontal.svg');
            $fallbackPng = public_path('images/logo-fundacion-horizontal.png');
            if (file_exists($fallbackSvg)) {
                $logoAbsPath = $fallbackSvg;
            } elseif (file_exists($fallbackPng)) {
                $logoAbsPath = $fallbackPng;
            }
        }

        // If SVG, read content for inline embedding (mPDF works best with inline SVG)
        if ($logoAbsPath !== '' && str_ends_with(strtolower($logoAbsPath), '.svg')) {
            try {
                $logoSvgContent = (string) @file_get_contents($logoAbsPath);
            } catch (Throwable) {
                $logoSvgContent = '';
            }
        }

        // Build optional data URI for raster images to improve mPDF compatibility
        $logoDataUri = '';
        if ($logoAbsPath !== '' && ! str_ends_with(strtolower($logoAbsPath), '.svg')) {
            try {
                $content = @file_get_contents($logoAbsPath);
                if ($content !== false) {
                    $ext = strtolower(pathinfo($logoAbsPath, PATHINFO_EXTENSION));
                    $mime = match ($ext) {
                        'png' => 'image/png',
                        'jpg', 'jpeg' => 'image/jpeg',
                        'gif' => 'image/gif',
                        default => 'application/octet-stream',
                    };
                    $logoDataUri = 'data:'.$mime.';base64,'.base64_encode($content);
                }
            } catch (Throwable) {
                $logoDataUri = '';
            }
        }

        return [
            'company' => (string) setting('billing.company', ''),
            'nif' => (string) setting('billing.nif', ''),
            'email' => (string) setting('billing.email', ''),
            'phone' => (string) setting('billing.phone', ''),
            'address' => (string) setting('billing.address', ''),
            'postal_code' => (string) setting('billing.postal_code', ''),
            'city' => (string) setting('billing.city', ''),
            'country' => (string) setting('billing.country', ''),
            'logo_abs_path' => $logoAbsPath,
            'logo_svg_content' => $logoSvgContent,
            'logo_data_uri' => $logoDataUri,
        ];
    }

    /**
     * @throws MpdfException
     */
    protected function generatePdf(string $html): string
    {
        $tempDir = storage_path('app/tmp/mpdf');
        if (! is_dir($tempDir)) {
            @mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'tempDir' => $tempDir,
            'default_font' => 'DejaVuSans',
        ]);

        $mpdf->WriteHTML($html);

        return $mpdf->Output('', 'S');
    }

    protected function storePdf(string $pdfContent, string $series, Invoice $invoice, bool $force = false): string
    {
        $disk = Storage::disk('public');
        $driver = config('filesystems.disks.public.driver');
        $root = rtrim((string) config('filesystems.disks.public.root'), '/');
        $relativePath = sprintf('invoices/%s/%d/%s.pdf', $series, $invoice->year, $invoice->number);
        $directory = dirname($relativePath);
        $absoluteDir = storage_path('app/public/' . $directory);
        $absolutePath = storage_path('app/public/' . $relativePath);

        $debug = (bool) (config('app.debug') || env('INVOICE_DEBUG', false));

        // Ensure directories exist
        try {
            if (! is_dir($absoluteDir)) {
                @mkdir($absoluteDir, 0775, true);
            }
        } catch (Throwable $e) {
            Log::error('[invoice-pdf] Failed to create directory', [
                'invoice_id' => $invoice->id,
                'number' => $invoice->number,
                'dir' => $absoluteDir,
                'error' => $e->getMessage(),
            ]);
        }

        // If forcing, remove any previous file first to guarantee overwrite
        if ($force) {
            try {
                if (file_exists($absolutePath)) {
                    @unlink($absolutePath);
                }
                if ($disk->exists($relativePath)) {
                    $disk->delete($relativePath);
                }
            } catch (Throwable $e) {
                Log::warning('[invoice-pdf] Failed to delete previous invoice file on force', [
                    'invoice_id' => $invoice->id,
                    'number' => $invoice->number,
                    'relative' => $relativePath,
                    'absolute' => $absolutePath,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Preferred strategy: write to absolute filesystem when using local disk
        $wroteAbsolute = false;
        try {
            $bytes = @file_put_contents($absolutePath, $pdfContent);
            $wroteAbsolute = $bytes !== false;
            if ($debug) {
                Log::info('[invoice-pdf] Absolute write result', [
                    'invoice_id' => $invoice->id,
                    'number' => $invoice->number,
                    'bytes' => $bytes,
                    'exists_after' => file_exists($absolutePath),
                    'driver' => $driver,
                    'root' => $root,
                    'relative' => $relativePath,
                    'absolute' => $absolutePath,
                ]);
            }
        } catch (Throwable $e) {
            Log::error('[invoice-pdf] Absolute write failed', [
                'invoice_id' => $invoice->id,
                'number' => $invoice->number,
                'path' => $absolutePath,
                'error' => $e->getMessage(),
            ]);
        }

        // If disk is not local or disk->exists won't see the absolute write (custom drivers), ensure the disk also has it
        $savedOnDisk = false;
        try {
            // If driver is not local, or disk cannot see the absolute file, put via disk API as well
            if ($driver !== 'local' || ! $disk->exists($relativePath)) {
                $savedOnDisk = $disk->put($relativePath, $pdfContent) === true;
                if ($debug) {
                    Log::info('[invoice-pdf] Disk put result', [
                        'invoice_id' => $invoice->id,
                        'number' => $invoice->number,
                        'saved' => $savedOnDisk,
                        'exists_after' => $disk->exists($relativePath),
                        'driver' => $driver,
                        'relative' => $relativePath,
                    ]);
                }
            }
        } catch (Throwable $e) {
            Log::error('[invoice-pdf] Disk write failed', [
                'invoice_id' => $invoice->id,
                'number' => $invoice->number,
                'relative' => $relativePath,
                'error' => $e->getMessage(),
            ]);
        }

        // Final verification, retry once if missing, and fail-fast if still missing
        if (! file_exists($absolutePath) && ! $disk->exists($relativePath)) {
            Log::warning('[invoice-pdf] PDF not found after first write attempt, retrying once', [
                'invoice_id' => $invoice->id,
                'number' => $invoice->number,
                'relative' => $relativePath,
                'absolute' => $absolutePath,
            ]);

            try {
                $bytes = @file_put_contents($absolutePath, $pdfContent);
                if ($driver !== 'local') {
                    $disk->put($relativePath, $pdfContent);
                }
                if ($debug) {
                    Log::info('[invoice-pdf] Retry write done', [
                        'invoice_id' => $invoice->id,
                        'number' => $invoice->number,
                        'bytes' => $bytes,
                        'exists_absolute' => file_exists($absolutePath),
                        'exists_disk' => $disk->exists($relativePath),
                    ]);
                }
            } catch (Throwable $e) {
                Log::error('[invoice-pdf] Retry to write invoice PDF failed', [
                    'invoice_id' => $invoice->id,
                    'number' => $invoice->number,
                    'path' => $absolutePath,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (! file_exists($absolutePath) && ! $disk->exists($relativePath)) {
            Log::error('[invoice-pdf] PDF not found after write attempts, aborting', [
                'invoice_id' => $invoice->id,
                'number' => $invoice->number,
                'relative' => $relativePath,
                'absolute' => $absolutePath,
                'driver' => $driver,
                'root' => $root,
            ]);
            throw new \RuntimeException('No se pudo generar el archivo PDF de la factura.');
        }

        return $relativePath;
    }

    /** @noinspection PhpUndefinedMethodInspection */
    protected function attachMedia(Model $model, string $relativePath, bool $force = false, ?string $pdfContent = null): void
    {
        try {
            $disk = Storage::disk('public');

            // Ensure the source file exists on the public disk before attaching
            $absPath = storage_path('app/public/' . ltrim($relativePath, '/'));
            $exists = $disk->exists($relativePath) || file_exists($absPath);
            if (! $exists) {
                Log::warning('[invoice-pdf] Invoice media source file missing on disk when attaching', [
                    'model_type' => $model->getMorphClass(),
                    'model_id' => $model->getKey(),
                    'relativePath' => $relativePath,
                    'abs' => $absPath,
                ]);
                return;
            }

            // Attach from memory to avoid the media library moving or deleting the original file
            if ($pdfContent === null) {
                try {
                    $pdfContent = @file_get_contents($absPath);
                } catch (Throwable) {
                    $pdfContent = null;
                }
            }

            if ($pdfContent !== null) {
                $adder = $model->addMediaFromString($pdfContent)
                    ->usingFileName(basename($relativePath));

                // Some versions of Spatie Media Library don't have usingMimeType
                if (method_exists($adder, 'usingMimeType')) {
                    $adder = $adder->usingMimeType('application/pdf');
                }

                $media = $adder->toMediaCollection('invoices');

                // Keep only the latest media in the collection to avoid confusion
                try {
                    $all = $model->getMedia('invoices');
                    foreach ($all as $item) {
                        if ($media && $item->id !== $media->id) {
                            $item->delete();
                        }
                    }
                } catch (Throwable $e) {
                    Log::warning('[invoice-pdf] Failed to trim old invoice media: ' . $e->getMessage());
                }
            } else {
                // Fallback: copy from disk without moving the original (older versions may still move); as a backup, don't clear collection first
                $model->addMediaFromDisk($relativePath, 'public')
                    ->usingFileName(basename($relativePath))
                    ->toMediaCollection('invoices');
            }
        } catch (Throwable $e) {
            Log::warning('Failed to attach invoice media: ' . $e->getMessage());
        }
    }

    protected function sendInvoiceEmailForOrder(Order $order, Invoice $invoice): void
    {
        $recipients = collect();

        if ($billing = $order->billing_address()) {
            if ($billing->email) {
                $recipients->push($billing->email);
            }
        }

        if ($shipping = $order->shipping_address()) {
            if ($shipping->email && ! $recipients->contains($shipping->email)) {
                $recipients->push($shipping->email);
            }
        }

        if ($recipients->isEmpty()) {
            return;
        }

        $this->sendInvoiceEmail($recipients->toArray(), $invoice);
    }

    /**
     * @param  array<int, string>  $recipients
     */
    protected function sendInvoiceEmail(array $recipients, Invoice $invoice): void
    {
        if (empty($recipients)) {
            return;
        }

        $cc = [];
        $admin = User::query()->find(1);

        if ($admin?->email) {
            $cc[] = $admin->email;
        }

        Mail::to($recipients)
            ->cc($cc)
            ->send(new InvoiceMailable($invoice));

        Notification::make()
            ->success()
            ->title('Email de factura enviado')
            ->body('Se ha enviado un email a '.implode(', ', $recipients).' con la factura')
            ->send();

        $invoice->update([
            'sent_at' => now(),
            'emailed_to' => $recipients,
        ]);
    }

    /**
     * @return array{invoice: Invoice, path: string, url: string}
     *
     * @throws MpdfException|Throwable
     */
    public function generateForDonation(
        Donation $donation,
        string $series = 'FD',
        bool $sendEmail = false,
        bool $force = false
    ): array {
        $donation->loadMissing('addresses');

        $lines = $this->donationLines($donation);
        $vatRate = $donation->vatRate();

        $subtotal = $this->calculateSubtotal($lines);
        $vatAmount = round($subtotal * $vatRate, 2);
        $total = round($subtotal + $vatAmount, 2);

        $meta = [
            'donation_type' => $donation->type,
            'frequency' => $donation->frequency,
        ];

        $result = $this->createInvoice(
            invoiceable: $donation,
            series: $series,
            lines: $lines,
            subtotal: $subtotal,
            vatRate: $vatRate,
            vatAmount: $vatAmount,
            total: $total,
            meta: $meta,
            force: $force
        );

        if ($sendEmail) {
            $this->sendInvoiceEmailForDonation($donation, $result['invoice']);
        }

        return $result;
    }

    /**
     * @return array<int, array{name: string, quantity: int, unit_price: float, line_total: float}>
     */
    protected function donationLines(Donation $donation): array
    {
        $amount = (float) $donation->amount;

        return [
            [
                'name' => 'Donación',
                'quantity' => 1,
                'unit_price' => $amount,
                'line_total' => $amount,
            ],
        ];
    }

    protected function sendInvoiceEmailForDonation(Donation $donation, Invoice $invoice): void
    {
        $certificate = $donation->certificate();

        // Donation::certificate() may return false; guard it and missing email
        if (! $certificate || ! ($certificate->email)) {
            return;
        }

        $this->sendInvoiceEmail([$certificate->email], $invoice);
    }
}
