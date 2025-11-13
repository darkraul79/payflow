<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\Invoice as InvoiceModel;
use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage as StorageFacade;
use Throwable;

class InvoiceController extends Controller
{
    public function __invoke(InvoiceModel $invoice)
    {
        $disk = StorageFacade::disk('public');
        $regenerated = false;
        $refreshed = false;
        $forceRefresh = request()->boolean('refresh');

        // If forcing refresh, always regenerate even if the file exists
        if ($forceRefresh) {
            try {
                $invoiceable = $invoice->invoiceable; // Order or Donation
                $service = app(InvoiceService::class);
                if ($invoiceable instanceof Order) {
                    $service->generateForOrder($invoiceable, series: $invoice->series, sendEmail: false, force: true);
                } elseif ($invoiceable instanceof Donation) {
                    $service->generateForDonation($invoiceable, series: $invoice->series, sendEmail: false,
                        force: true);
                }
                $refreshed = true;
            } catch (Throwable $e) {
                Log::warning('Failed to refresh invoice PDF', [
                    'invoice_id' => $invoice->id,
                    'number' => $invoice->number,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // If a file missing, try to regenerate it at once
        if (! ($invoice->storage_path && $disk->exists($invoice->storage_path))) {
            try {
                $invoiceable = $invoice->invoiceable; // Order or Donation
                $service = app(InvoiceService::class);
                if ($invoiceable instanceof Order) {
                    $service->generateForOrder($invoiceable, series: $invoice->series, sendEmail: false);
                    $regenerated = true;
                } elseif ($invoiceable instanceof Donation) {
                    $service->generateForDonation($invoiceable, series: $invoice->series, sendEmail: false);
                    $regenerated = true;
                }
            } catch (Throwable $e) {
                Log::warning('Failed to regenerate invoice PDF', [
                    'invoice_id' => $invoice->id,
                    'number' => $invoice->number,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (! ($invoice->storage_path && $disk->exists($invoice->storage_path))) {
            // Final fallback: try to use the Media Library file if it exists
            try {
                $media = optional($invoice->invoiceable)->getMedia('invoices')->first();
                if ($media && file_exists($media->getPath())) {
                    // Try to restore the expected file path from the media file

                    try {
                        $content = @file_get_contents($media->getPath());
                        if ($content !== false && $invoice->storage_path) {
                            $disk->put($invoice->storage_path, $content);
                            $regenerated = true;
                        }
                    } catch (Throwable $e) {
                        Log::warning('Failed to restore invoice from media', [
                            'invoice_id' => $invoice->id,
                            'number' => $invoice->number,
                            'media_path' => $media->getPath(),
                            'error' => $e->getMessage(),
                        ]);
                    }

                    // If still missing, stream directly from a media path
                    if (! ($invoice->storage_path && $disk->exists($invoice->storage_path))) {
                        return response()->file($media->getPath(), [
                            'Content-Type' => 'application/pdf',
                            'Cache-Control' => 'private, max-age=0, no-store, no-cache, must-revalidate',
                            'X-Invoice-Regenerated' => $regenerated ? '1' : '0',
                            'X-Invoice-Refreshed' => $refreshed ? '1' : '0',
                        ]);
                    }
                }
            } catch (Throwable $e) {
                Log::warning('Invoice media fallback failed', [
                    'invoice_id' => $invoice->id,
                    'number' => $invoice->number,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        abort_unless($invoice->storage_path && $disk->exists($invoice->storage_path), 404);

        // Stream inline to the browser
        return $disk->response($invoice->storage_path, basename($invoice->storage_path), [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, max-age=0, no-store, no-cache, must-revalidate',
            'X-Invoice-Regenerated' => $regenerated ? '1' : '0',
            'X-Invoice-Refreshed' => $refreshed ? '1' : '0',
        ]);
    }
}
