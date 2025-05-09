<?php

use App\Http\Controllers\FrontEndController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Models\Activity;
use App\Models\News;
use App\Models\Proyect;
use Illuminate\Support\Facades\Route;

Route::get('/', [FrontEndController::class, 'index'])->name('home');

Route::get(Activity::getStaticUrlPrefix().'/{slug}', [FrontEndController::class, 'activities'])->name('activities.show');
Route::get(News::getStaticUrlPrefix().'/{slug}', [FrontEndController::class, 'news'])->name('news.show');
Route::get(Proyect::getStaticUrlPrefix().'/{slug}', [FrontEndController::class, 'proyects'])->name('proyects.show');

Route::get('/pagina', function () {
    return view('page');
})->name('pagina');

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
