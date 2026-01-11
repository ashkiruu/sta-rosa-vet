<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\NotificationController;



Route::get('/', function () {
    return view('welcome');
});

// Registration Routes
Route::get('/register/step1', [RegisterController::class, 'step1'])->name('register.step1');
Route::post('/register/step1', [RegisterController::class, 'postStep1']);
// Registration Step 2
Route::get('/register/step2', [RegisterController::class, 'step2'])->name('register.step2');
Route::post('/register/step2', [RegisterController::class, 'postStep2'])->name('register.step2.post');
// Registration Step 3
Route::get('/register/step3', [RegisterController::class, 'step3'])->name('register.step3');
Route::post('/register/step3', [RegisterController::class, 'postStep3'])->name('register.step3.post');

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Verify Process
    Route::get('/verify-account', [RegisterController::class, 'showReverifyForm'])->name('verify.reverify');
    Route::post('/verify-account', [RegisterController::class, 'processReverify'])->name('verify.process');

    // Pet Management Routes
    Route::get('/pets', [PetController::class, 'index'])->name('pets.index');
    Route::get('/pets/create', [PetController::class, 'create'])->name('pets.create');
    Route::post('/pets', [PetController::class, 'store'])->name('pets.store');
    Route::get('/pets/{id}', [PetController::class, 'show'])->name('pets.show');
    Route::delete('/pets/{id}', [PetController::class, 'destroy'])->name('pets.destroy');

    // Appointments
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    
    Route::post('/appointments/preview', [AppointmentController::class, 'preview'])->name('appointments.preview');
    Route::post('/appointments/confirm', [AppointmentController::class, 'confirm'])->name('appointments.confirm');
    
    Route::get('/appointments/{id}', [AppointmentController::class, 'show'])->name('appointments.show');
    Route::post('/appointments/{id}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');

    Route::get('/notifications/view/{id}', [NotificationController::class, 'viewAppointment'])
    ->name('notifications.viewAppointment');

    Route::post('/notifications/mark-all-seen', [NotificationController::class, 'markAllSeen'])
    ->name('notifications.markAllSeen');
});

// Admin-Only Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // Admin Dashboard Overview
    Route::get('/dashboard', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/users/{id}', [App\Http\Controllers\AdminController::class, 'showUser'])->name('admin.user.show'); 

    // 1. User Verification (The OCR Review Module)
    Route::get('/verifications', [App\Http\Controllers\AdminController::class, 'pendingVerifications'])->name('admin.verifications');
    Route::post('/verifications/{id}/approve', [App\Http\Controllers\AdminController::class, 'approveUser'])->name('admin.user.approve');
    Route::post('/verifications/{id}/reject', [App\Http\Controllers\AdminController::class, 'rejectUser'])->name('admin.user.reject');

    // 2. Appointment Management (The Conflict Resolution Module)
    Route::get('/appointments', [App\Http\Controllers\AdminController::class, 'appointments'])->name('admin.appointments.index');
    Route::post('/appointments/{id}/approve', [App\Http\Controllers\AdminController::class, 'approveAppointment'])->name('admin.appointments.approve');
    
    // 3. Reports (The Summary Report Module)
    Route::get('/reports', [App\Http\Controllers\AdminController::class, 'reports'])->name('admin.reports');
});


