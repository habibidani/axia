<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Livewire\AdminPrompts;
use App\Livewire\AllAnalyses;
use App\Livewire\CompanyEdit;
use App\Livewire\GoalsEdit;
use App\Livewire\Home;
use App\Livewire\PromptTester;
use App\Livewire\Results;
use App\Livewire\Settings;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

// Epic Landing Page
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Authentication routes
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');

Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

// Axia routes (require auth)
Route::middleware(['auth'])->group(function () {
    Route::get('/home', Home::class)->name('home');
    Route::get('/company/edit', CompanyEdit::class)->name('company.edit');
    Route::get('/goals/edit', GoalsEdit::class)->name('goals.edit');
    Route::get('/results/{run}', Results::class)->name('results.show');
    Route::get('/analyses', AllAnalyses::class)->name('analyses.index');
    Route::get('/settings/webhooks', Settings::class)->name('settings.webhooks');

    // Admin routes (system prompts)
    Route::get('/admin/prompts', AdminPrompts::class)->name('admin.prompts');
    Route::get('/admin/prompts/test', PromptTester::class)->name('admin.prompts.test');

    // Redirect old dashboard to home
    Route::redirect('/dashboard', '/home')->name('dashboard');
});

// Settings routes
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');
    Route::get('settings/api-tokens', \App\Livewire\Settings\ApiTokens::class)->name('api-tokens.index');

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
