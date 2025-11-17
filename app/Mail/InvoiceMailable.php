<?php

namespace App\Mail;

use App\Models\Donation;
use App\Models\Invoice;
use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class InvoiceMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Invoice $invoice) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Factura '.$this->invoice->number,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.invoice',
            with: [
                'invoice' => $this->invoice,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        // Ensure the PDF exists; try to regenerate if missing
        try {
            $disk = Storage::disk('public');
            $path = $this->invoice->storage_path ?? '';

            if ($path === '' || ! $disk->exists($path)) {
                // Try regenerating using the related model and existing series/number
                $invoiceable = $this->invoice->invoiceable; // Order or Donation
                /** @var InvoiceService $service */
                $service = app(InvoiceService::class);

                if ($invoiceable instanceof Order) {
                    $service->generateForOrder($invoiceable, series: $this->invoice->series, sendEmail: false,
                        force: true);
                } elseif ($invoiceable instanceof Donation) {
                    $service->generateForDonation($invoiceable, series: $this->invoice->series, sendEmail: false,
                        force: true);
                }

                // Reload path and re-check
                $this->invoice->refresh();
                $path = $this->invoice->storage_path ?? '';
            }

            if ($path !== '' && $disk->exists($path)) {
                return [
                    Attachment::fromStorageDisk('public', $path)
                        ->as($this->invoice->number.'.pdf')
                        ->withMime('application/pdf'),
                ];
            }
        } catch (Throwable) {
            // Continue without attachment; avoid hard failures
        }

        // If we reach here, skip attachments to avoid the TypeError / null body
        return [];
    }
}
