<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// ዋናው መነሻ ገጽ ወደ login እንዲመራ
Route::get('/', function () {
    return redirect()->route('login');
});

// የመግቢያ እና መመዝገቢያ መንገዶች
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// የተማሪ ዳሽቦርድ
Route::middleware(['auth', 'role:student'])->group(function () {
    Route::get('/student/dashboard', function () {
        return view('student.dashboard');
    })->name('student.dashboard');
});

// የዲፓርትመንት ኃላፊ ዳሽቦርድ
Route::middleware(['auth', 'role:department'])->group(function () {
    Route::get('/department/dashboard', function () {
        return view('department.dashboard');
    })->name('department.dashboard');
});

// የአድሚን ዳሽቦርድ
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});