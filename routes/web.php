<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OfficerClearanceController;
use App\Http\Controllers\RegistrarController;
use App\Http\Controllers\StudentClearanceController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/verify', [VerificationController::class, 'show'])->name('verify.show');

Route::middleware('auth')->group(function () {
    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [StudentClearanceController::class, 'dashboard'])->name('dashboard');
        Route::post('/clearance', [StudentClearanceController::class, 'store'])->name('clearance.store');
        Route::post('/clearance/{clearance}/resubmit', [StudentClearanceController::class, 'resubmit'])->name('clearance.resubmit');
        Route::post('/notifications/read', [StudentClearanceController::class, 'markNotificationsRead'])->name('notifications.read');
        Route::get('/clearance/{clearance}/certificate', [StudentClearanceController::class, 'downloadCertificate'])->name('clearance.certificate');
    });

    Route::middleware('role:department_head,library,store,finance')->prefix('officer')->name('officer.')->group(function () {
        Route::get('/dashboard', [OfficerClearanceController::class, 'dashboard'])->name('dashboard');
        Route::post('/clearance/{clearance}/decide', [OfficerClearanceController::class, 'decide'])->name('decide');
    });

    Route::middleware('role:registrar')->prefix('registrar')->name('registrar.')->group(function () {
        Route::get('/dashboard', [RegistrarController::class, 'dashboard'])->name('dashboard');
        Route::post('/clearance/{clearance}/issue', [RegistrarController::class, 'issueCertificate'])->name('issue');
        Route::post('/clearance/{clearance}/reject', [RegistrarController::class, 'rejectRequest'])->name('reject');
        Route::post('/clearance/{clearance}/revoke', [RegistrarController::class, 'revokeCertificate'])->name('revoke');
        Route::get('/clearance/{clearance}/pdf', [RegistrarController::class, 'downloadPdf'])->name('certificate.pdf');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::post('/staff', [AdminController::class, 'createStaff'])->name('staff.create');
    });
});