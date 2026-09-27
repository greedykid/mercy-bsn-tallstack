<?php

use App\Actions\Auth\Logout;
use App\Livewire\Assessments\AssessmentForm;
use App\Livewire\Assessments\AssessmentIndex;
use App\Livewire\Assessments\AssessmentShow;
use App\Livewire\Auth\ConfirmPassword;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Auth\VerifyEmail;
use App\Livewire\Calendar\CalendarView;
use App\Livewire\Dashboard;
use App\Livewire\Deadlines\DeadlineIndex;
use App\Livewire\Lpks\LpkForm;
use App\Livewire\Lpks\LpkIndex;
use App\Livewire\Lpks\LpkShow;
use App\Livewire\Settings\Account;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Root & Portal redirect directly to login
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
})->name('home');

Route::redirect('/portal', '/login');

/** AUTH ROUTES (Signup removed - accounts created by admin) */
Route::livewire('/login', Login::class)->name('login');
Route::livewire('/forgot-password', ForgotPassword::class)->name('forgot-password');
Route::livewire('reset-password/{token}', ResetPassword::class)->name('password.reset');

/** PROTECTED MERCY INTERNAL ROUTES */
Route::middleware('auth')->group(function () {
    // Executive & PIC Dashboard
    Route::livewire('/dashboard', Dashboard::class)->name('dashboard');

    // Notifikasi Deadline Siklus Akreditasi
    Route::livewire('/deadlines', DeadlineIndex::class)->name('deadlines.index');

    // LPK Master Management
    Route::livewire('/lpks', LpkIndex::class)->name('lpks.index');
    Route::livewire('/lpks/create', LpkForm::class)->name('lpks.create');
    Route::livewire('/lpks/{id}', LpkShow::class)->name('lpks.show');
    Route::livewire('/lpks/{id}/edit', LpkForm::class)->name('lpks.edit');

    // Assessments & TP Management
    Route::livewire('/assessments', AssessmentIndex::class)->name('assessments.index');
    Route::livewire('/assessments/create', AssessmentForm::class)->name('assessments.create');
    Route::livewire('/assessments/{id}', AssessmentShow::class)->name('assessments.show');
    Route::livewire('/assessments/{id}/edit', AssessmentForm::class)->name('assessments.edit');

    // Calendar Agenda & Batas Waktu
    Route::livewire('/calendar', CalendarView::class)->name('calendar');

    // Account Settings
    Route::livewire('/settings/account', Account::class)->name('settings.account');
});

Route::middleware(['auth'])->group(function () {
    Route::livewire('/auth/verify-email', VerifyEmail::class)
        ->name('verification.notice');
    Route::post('/logout', Logout::class)
        ->name('app.auth.logout');
    Route::livewire('confirm-password', ConfirmPassword::class)
        ->name('password.confirm');
});

Route::middleware(['auth', 'signed'])->group(function () {
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect(route('home'));
    })->name('verification.verify');
});
