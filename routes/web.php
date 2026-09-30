<?php

use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DictionaryController as AdminDictionaryController;
use App\Http\Controllers\Admin\EmailTemplateController as AdminEmailTemplateController;
use App\Http\Controllers\Admin\FilterConfigController as AdminFilterConfigController;
use App\Http\Controllers\Admin\FormConfigController as AdminFormConfigController;
use App\Http\Controllers\Admin\JobOfferController as AdminJobOfferController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Candidate\ApplicationCancellationController;
use App\Http\Controllers\Candidate\CandidateApplicationController;
use App\Http\Controllers\Candidate\PublicOfferController;
use App\Http\Controllers\Candidate\StaticPagesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Publiczne trasy kandydata (bez rejestracji i logowania)
|--------------------------------------------------------------------------
*/

Route::get('/', [PublicOfferController::class, 'index'])->name('public.offers.index');
Route::get('/oferty/{slug}', [PublicOfferController::class, 'show'])->name('public.offers.show');
Route::get('/oferty/{slug}/aplikuj', [CandidateApplicationController::class, 'create'])->name('public.applications.create');
Route::post('/oferty/{slug}/aplikuj', [CandidateApplicationController::class, 'store'])
    ->name('public.applications.store')
    ->middleware('throttle:15,1');

Route::get('/zgloszenie/dziekujemy', [CandidateApplicationController::class, 'thankYou'])->name('public.applications.thank_you');

// Dwuetapowy proces anulowania zgłoszenia linkiem z maila (GET nie zmienia stanu, zmiana tylko przez POST)
Route::get('/zgloszenie/anuluj/{token}', [ApplicationCancellationController::class, 'showConfirm'])
    ->name('applications.cancel.confirm')
    ->middleware('throttle:30,1');
Route::post('/zgloszenie/anuluj/{token}', [ApplicationCancellationController::class, 'processCancel'])
    ->name('applications.cancel.process')
    ->middleware('throttle:15,1');

// Dostępność cyfrowa i RODO
Route::get('/deklaracja-dostepnosci', [StaticPagesController::class, 'accessibility'])->name('public.accessibility');
Route::get('/polityka-prywatnosci', [StaticPagesController::class, 'privacy'])->name('public.privacy');

/*
|--------------------------------------------------------------------------
| Panel administracyjny (/admin) - dostęp wyłącznie z sieci lokalnej (EnsureLocalNetwork)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->group(function () {

    // Trasy logowania i 2FA (tylko sieć lokalna)
    Route::middleware(['local_network'])->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
        Route::get('/auth/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');

        Route::get('/2fa', [AdminAuthController::class, 'show2faForm'])->name('admin.2fa.show');
        Route::post('/2fa', [AdminAuthController::class, 'verify2fa'])->name('admin.2fa.verify');
        Route::post('/2fa/resend', [AdminAuthController::class, 'resend2fa'])->name('admin.2fa.resend');

        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    });

    // Chroniona część panelu (sieć lokalna + autoryzacja + rola)
    Route::middleware(['local_network', 'auth', 'role:viewer,recruiter,admin'])->group(function () {
        // Pulpit
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

        // Oferty pracy
        Route::get('/oferty', [AdminJobOfferController::class, 'index'])->name('admin.offers.index');
        Route::get('/oferty/utworz', [AdminJobOfferController::class, 'create'])->name('admin.offers.create')->middleware('role:recruiter,admin');
        Route::post('/oferty', [AdminJobOfferController::class, 'store'])->name('admin.offers.store')->middleware('role:recruiter,admin');
        Route::get('/oferty/{public_id}/edytuj', [AdminJobOfferController::class, 'edit'])->name('admin.offers.edit')->middleware('role:recruiter,admin');
        Route::put('/oferty/{public_id}', [AdminJobOfferController::class, 'update'])->name('admin.offers.update')->middleware('role:recruiter,admin');
        Route::post('/oferty/{public_id}/zakoncz', [AdminJobOfferController::class, 'complete'])->name('admin.offers.complete')->middleware('role:recruiter,admin');
        Route::post('/oferty/{public_id}/duplicate', [AdminJobOfferController::class, 'duplicate'])->name('admin.offers.duplicate')->middleware('role:recruiter,admin');
        Route::delete('/oferty/{public_id}', [AdminJobOfferController::class, 'destroy'])->name('admin.offers.destroy')->middleware('role:recruiter,admin');

        // Zgłoszenia kandydatów
        Route::get('/zgloszenia', [AdminApplicationController::class, 'index'])->name('admin.applications.index');
        Route::get('/zgloszenia/eksport-csv', [AdminApplicationController::class, 'exportCsv'])->name('admin.applications.export-csv');
        Route::get('/zgloszenia/reczne', [AdminApplicationController::class, 'create'])->name('admin.applications.create-manual')->middleware('role:recruiter,admin');
        Route::post('/zgloszenia/reczne', [AdminApplicationController::class, 'storeManual'])->name('admin.applications.store-manual')->middleware('role:recruiter,admin');
        Route::get('/zgloszenia/{public_id}', [AdminApplicationController::class, 'show'])->name('admin.applications.show');
        Route::get('/zgloszenia/{public_id}/edytuj', [AdminApplicationController::class, 'edit'])->name('admin.applications.edit')->middleware('role:recruiter,admin');
        Route::put('/zgloszenia/{public_id}', [AdminApplicationController::class, 'update'])->name('admin.applications.update')->middleware('role:recruiter,admin');
        Route::post('/zgloszenia/{public_id}/status', [AdminApplicationController::class, 'updateStatus'])->name('admin.applications.update-status')->middleware('role:recruiter,admin');
        Route::post('/zgloszenia/{public_id}/mail', [AdminApplicationController::class, 'sendMail'])->name('admin.applications.send-mail')->middleware('role:recruiter,admin');
        Route::get('/zgloszenia/{public_id}/pliki/{file_id}', [AdminApplicationController::class, 'downloadFile'])->name('admin.applications.download-file');
        Route::post('/zgloszenia/masowy-status', [AdminApplicationController::class, 'bulkStatus'])->name('admin.applications.bulk-status')->middleware('role:recruiter,admin');

        // Słowniki
        Route::get('/slowniki/{type}', [AdminDictionaryController::class, 'index'])->name('admin.dictionaries.index');
        Route::post('/slowniki/{type}', [AdminDictionaryController::class, 'store'])->name('admin.dictionaries.store')->middleware('role:recruiter,admin');
        Route::post('/slowniki/{type}/{id}/toggle', [AdminDictionaryController::class, 'toggle'])->name('admin.dictionaries.toggle')->middleware('role:recruiter,admin');

        // Filtry (konfigurator strony głównej)
        Route::get('/filtry', [AdminFilterConfigController::class, 'index'])->name('admin.filters.index');
        Route::post('/filtry', [AdminFilterConfigController::class, 'store'])->name('admin.filters.store')->middleware('role:recruiter,admin');
        Route::put('/filtry/{id}', [AdminFilterConfigController::class, 'update'])->name('admin.filters.update')->middleware('role:recruiter,admin');
        Route::post('/filtry/{id}/toggle', [AdminFilterConfigController::class, 'toggle'])->name('admin.filters.toggle')->middleware('role:recruiter,admin');
        Route::post('/filtry/{id}/move/{direction}', [AdminFilterConfigController::class, 'move'])->name('admin.filters.move')->middleware('role:recruiter,admin');

        // Formularze (szablony i pola dynamiczne)
        Route::get('/formularze', [AdminFormConfigController::class, 'index'])->name('admin.forms.index');
        Route::post('/formularze', [AdminFormConfigController::class, 'storeForm'])->name('admin.forms.store')->middleware('role:recruiter,admin');
        Route::get('/formularze/{id}', [AdminFormConfigController::class, 'show'])->name('admin.forms.show');
        Route::post('/formularze/{id}/pola', [AdminFormConfigController::class, 'storeField'])->name('admin.forms.store-field')->middleware('role:recruiter,admin');
        Route::post('/formularze/pola/{id}/toggle', [AdminFormConfigController::class, 'toggleField'])->name('admin.forms.toggle-field')->middleware('role:recruiter,admin');
        Route::post('/formularze/pola/{id}/move/{direction}', [AdminFormConfigController::class, 'moveField'])->name('admin.forms.move-field')->middleware('role:recruiter,admin');

        // Tylko Administrator (Zarządzanie użytkownikami, logi audytu, ustawienia, szablony maili)
        Route::middleware(['role:admin'])->group(function () {
            // Użytkownicy i rekruterzy
            Route::get('/uzytkownicy', [AdminUserController::class, 'index'])->name('admin.users.index');
            Route::post('/uzytkownicy', [AdminUserController::class, 'store'])->name('admin.users.store');
            Route::post('/uzytkownicy/{id}/role', [AdminUserController::class, 'updateRole'])->name('admin.users.update-role');
            Route::post('/uzytkownicy/{id}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('admin.users.toggle-active');
            Route::post('/uzytkownicy/{id}/reset-password', [AdminUserController::class, 'resetPassword'])->name('admin.users.reset-password');

            // Logi audytu
            Route::get('/logi', [AdminAuditLogController::class, 'index'])->name('admin.logs.index');
            Route::get('/logi/eksport-csv', [AdminAuditLogController::class, 'exportCsv'])->name('admin.logs.export-csv');

            // Ustawienia
            Route::get('/ustawienia', [AdminSettingController::class, 'index'])->name('admin.settings.index');
            Route::post('/ustawienia', [AdminSettingController::class, 'update'])->name('admin.settings.update');

            // Szablony e-mail
            Route::get('/szablony-maili', [AdminEmailTemplateController::class, 'index'])->name('admin.email-templates.index');
            Route::put('/szablony-maili/{id}', [AdminEmailTemplateController::class, 'update'])->name('admin.email-templates.update');
        });
    });
});
