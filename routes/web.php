<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\FrontEndController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\RedsysController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Models\Activity;
use App\Models\News;
use App\Models\Product;
use App\Models\Proyect;
use Illuminate\Support\Facades\Route;

Route::get('/', [FrontEndController::class, 'index'])->name('home');

Route::get('/tienda-solidaria/cesta', [CartController::class, 'index'])->name('cart');
Route::get('/tienda-solidaria/cesta/pedido', [CartController::class, 'form'])->name('checkout');

Route::any('/pedido/response', [RedsysController::class, 'responseOrder'])->name('pedido.response');
Route::any('/donacion/response', [RedsysController::class, 'donationResponse'])->name('donation.response');
Route::any('/pago/response', [RedsysController::class, 'pagoResponse'])->name('pago.response');
Route::get('/tienda-solidaria/cesta/pedido/{pedido}', [RedsysController::class, 'result'])->name('pedido.finalizado');
Route::get('/donacion/{donacion}', [RedsysController::class, 'result'])->name('donacion.finalizada');

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/invoices/{invoice}', InvoiceController::class)->name('invoices.show');
});

Route::get(Activity::getStaticUrlPrefix().'/{slug}',
    [FrontEndController::class, 'activities'])->name('activities.show');
Route::get(News::getStaticUrlPrefix().'/{slug}', [FrontEndController::class, 'news'])->name('news.show');
Route::get(Proyect::getStaticUrlPrefix().'/{slug}', [FrontEndController::class, 'proyects'])->name('proyects.show');
Route::get(Product::getStaticUrlPrefix().'/{slug}', [FrontEndController::class, 'products'])->name('products.show');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

require __DIR__.'/auth.php';
