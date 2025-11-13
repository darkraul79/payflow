<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\FrontEndController;
use App\Http\Controllers\RedsysController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Models\Activity;
use App\Models\Donation;
use App\Models\News;
use App\Models\Order;
use App\Models\Product;
use App\Models\Proyect;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Route;

Route::get('/', [FrontEndController::class, 'index'])->name('home');

Route::get('/tienda-solidaria/cesta', [CartController::class, 'index'])->name('cart');
Route::get('/tienda-solidaria/cesta/pedido', [CartController::class, 'form'])->name('checkout');

Route::any('/pedido/response', [RedsysController::class, 'responseOrder'])->name('pedido.response');
Route::any('/donacion/response', [RedsysController::class, 'donationResponse'])->name('donation.response');
Route::any('/pago/response', [RedsysController::class, 'pagoResponse'])->name('pago.response');
Route::get('/tienda-solidaria/cesta/pedido/{pedido}', [RedsysController::class, 'result'])->name('pedido.finalizado');
Route::get('/donacion/{donacion}', [RedsysController::class, 'result'])->name('donacion.finalizada');

// Secured invoice streaming
use App\Models\Invoice as InvoiceModel;
use Illuminate\Support\Facades\Storage as StorageFacade;

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/invoices/{invoice}', function (InvoiceModel $invoice) {
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
                \Illuminate\Support\Facades\Log::warning('Failed to refresh invoice PDF', [
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
                \Illuminate\Support\Facades\Log::warning('Failed to regenerate invoice PDF', [
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
                        \Illuminate\Support\Facades\Log::warning('Failed to restore invoice from media', [
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
                \Illuminate\Support\Facades\Log::warning('Invoice media fallback failed', [
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
    })->name('invoices.show');
});

Route::get(Activity::getStaticUrlPrefix().'/{slug}',
    [FrontEndController::class, 'activities'])->name('activities.show');
Route::get(News::getStaticUrlPrefix().'/{slug}', [FrontEndController::class, 'news'])->name('news.show');
Route::get(Proyect::getStaticUrlPrefix().'/{slug}', [FrontEndController::class, 'proyects'])->name('proyects.show');
Route::get(Product::getStaticUrlPrefix().'/{slug}', [FrontEndController::class, 'products'])->name('products.show');

// Route::get('/pagina', function () {
//    return view('page');
// })->name('pagina');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

Route::get('kk', function () {

    $pedido = Order::find(1);

    $user = User::find(1);

    $user->notify(new \App\Notifications\OrderCreated($pedido));
});
require __DIR__.'/auth.php';
