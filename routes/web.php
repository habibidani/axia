<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Livewire\CompanyEdit;
use App\Livewire\GoalsEdit;
use App\Livewire\Home;
use App\Livewire\Onboarding;
use App\Livewire\Results;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

// Redirect root to login
Route::redirect('/', '/login')->name('welcome');

// Custom registration routes
Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

// Axia routes (require auth)
Route::middleware(['auth'])->group(function () {
    Route::get('/onboarding', Onboarding::class)->name('onboarding');
    Route::get('/home', Home::class)->name('home');
    Route::get('/company/edit', CompanyEdit::class)->name('company.edit');
    Route::get('/goals/edit', GoalsEdit::class)->name('goals.edit');
    Route::get('/results/{run}', Results::class)->name('results.show');
    
    // Redirect old dashboard to home
    Route::redirect('/dashboard', '/home')->name('dashboard');
});

// Settings routes
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});
