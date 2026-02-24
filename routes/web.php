<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Pre-registration Notice (Attention to all applicants)
Route::get('/register', [RegisterController::class, 'notice'])->name('register.notice');
Route::post('/register', [RegisterController::class, 'postNotice'])->name('register.notice.post');

// Registration Routes
Route::get('/register/step1', [RegisterController::class, 'step1'])->name('register.step1');
Route::post('/register/step1', [RegisterController::class, 'postStep1']);
Route::get('/register/step2', [RegisterController::class, 'step2'])->name('register.step2');
Route::post('/register/step2', [RegisterController::class, 'postStep2'])->name('register.step2.post');
Route::get('/register/step3', [RegisterController::class, 'step3'])->name('register.step3');
Route::post('/register/step3', [RegisterController::class, 'postStep3'])->name('register.step3.post');

// Legal pages (public)
Route::view('/terms', 'legal.terms')->name('terms');
Route::view('/privacy', 'legal.privacy')->name('privacy');

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// PUBLIC ROUTE - QR Code Verification (NO AUTH REQUIRED!)
Route::get('/appointments/verify/{id}/{token}', [AppointmentController::class, 'verifyAppointment'])
    ->name('appointments.verify');

// Forgot Password Routes
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])
    ->middleware('guest')
    ->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])
    ->middleware('guest')
    ->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])
    ->middleware('guest')
    ->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])
    ->middleware('guest')
    ->name('password.update');

// ============================================================================
// AUTHENTICATED ROUTES - Accessible to ALL authenticated users (verified or not)
// ============================================================================
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/verification-pending', function () {
        return view('verification.pending');
    })->name('verification.pending');

    Route::get('/verify-account', [RegisterController::class, 'showReverifyForm'])->name('verify.reverify');
    Route::post('/verify-account', [RegisterController::class, 'processReverify'])->name('verify.process');
});

// ============================================================================
// VERIFIED USER ROUTES - Features require verification
// ============================================================================
Route::middleware(['auth', 'verified'])->group(function () {
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
    Route::get('/appointments/check-limit', [AppointmentController::class, 'checkAppointmentLimit'])
        ->name('appointments.checkLimit');
    
    Route::post('/appointments/preview', [AppointmentController::class, 'preview'])->name('appointments.preview');
    Route::post('/appointments/confirm', [AppointmentController::class, 'confirm'])->name('appointments.confirm');

    // Appointment utility routes
    Route::get('/appointments/time-slots', [AppointmentController::class, 'getTimeSlots'])
        ->name('appointments.timeSlots');
    Route::get('/appointments/taken-times', [AppointmentController::class, 'getTakenTimes'])
        ->name('appointments.takenTimes');
    Route::get('/appointments/fully-booked', [AppointmentController::class, 'getFullyBookedDates'])
        ->name('appointments.fullyBooked');
    Route::get('/appointments/clinic-schedule', [AppointmentController::class, 'getClinicSchedule'])
        ->name('appointments.clinic-schedule');
    
    Route::get('/appointments/{id}', [AppointmentController::class, 'show'])->name('appointments.show');
    Route::post('/appointments/{id}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');

    // QR Code Routes
    Route::get('/appointments/{id}/qrcode', [AppointmentController::class, 'showQRCode'])
        ->name('appointments.qrcode');
    Route::get('/appointments/{id}/qrcode/download', [AppointmentController::class, 'downloadQRCode'])
        ->name('appointments.qrcode.download');

    Route::get('/appointments/{id}/check-status', [AppointmentController::class, 'checkStatus'])
        ->name('appointments.checkStatus');

    // Notifications
    Route::get('/notifications/view/{id}', [NotificationController::class, 'viewAppointment'])
        ->name('notifications.viewAppointment');
    Route::post('/notifications/mark-all-seen', [NotificationController::class, 'markAllSeen'])
        ->name('notifications.markAllSeen');

    Route::post('/appointments/notifications/mark-seen', [AppointmentController::class, 'markNotificationSeen'])
        ->name('appointments.notifications.markSeen');
    Route::post('/appointments/notifications/mark-all-seen', [AppointmentController::class, 'markAllNotificationsSeen'])
        ->name('appointments.notifications.markAllSeen');

    // User Certificate Routes
    Route::get('/my-certificates', [AppointmentController::class, 'certificatesIndex'])
        ->name('certificates.index');
    Route::get('/my-certificates/{id}/download', [AppointmentController::class, 'certificateDownload'])
        ->name('certificates.download');
    Route::get('/my-certificates/{id}/view', [AppointmentController::class, 'certificateDownload'])
        ->name('certificates.view');
});

// ============================================================================
// ADMIN ROUTES - All admin roles can access dashboard and user details
// ============================================================================
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard - accessible to ALL admin roles
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users/{id}', [AdminController::class, 'showUser'])->name('user.show');
});

// ============================================================================
// STAFF ROUTES - Verification, Reports, Attendance
// ============================================================================
Route::middleware(['auth', 'verified', 'admin', 'role:staff'])->prefix('admin')->name('admin.')->group(function () {
    // User Verification
    Route::get('/verifications', [AdminController::class, 'pendingVerifications'])->name('verifications');
    Route::post('/verifications/{id}/approve', [AdminController::class, 'approveUser'])->name('user.approve');
    Route::post('/verifications/{id}/reject', [AdminController::class, 'rejectUser'])->name('user.reject');
});

// ============================================================================
// DOCTOR ROUTES - Appointments, Certificates
// ============================================================================
Route::middleware(['auth', 'verified', 'admin', 'role:doctor'])->prefix('admin')->name('admin.')->group(function () {
    // Appointment Management
    Route::get('/appointments', [AdminController::class, 'appointments'])->name('appointment_index');
    Route::post('/appointments/{id}/approve', [AdminController::class, 'approveAppointment'])->name('appointments.approve');
    Route::post('/appointments/{id}/reject', [AdminController::class, 'rejectAppointment'])->name('appointments.reject');
    Route::post('/appointments/{id}/release-qr', [AdminController::class, 'releaseQRCode'])->name('appointments.release-qr');
    Route::post('/appointments/{id}/no-show', [AdminController::class, 'markNoShow'])->name('appointments.no-show');
    Route::post('/appointments/{id}/cancel', [AdminController::class, 'cancelAppointment'])->name('appointments.cancel');

    // Schedule Management
    Route::post('/schedule/toggle', [AdminController::class, 'toggleDateStatus'])->name('schedule.toggle');

    // Certificate Management
    Route::get('/certificates', [AdminController::class, 'certificatesIndex'])->name('certificates.index');
    Route::get('/certificates/create/{appointmentId}', [AdminController::class, 'certificatesCreate'])->name('certificates.create');
    Route::post('/certificates', [AdminController::class, 'certificatesStore'])->name('certificates.store');
    Route::get('/certificates/{id}/edit', [AdminController::class, 'certificatesEdit'])->name('certificates.edit');
    Route::put('/certificates/{id}', [AdminController::class, 'certificatesUpdate'])->name('certificates.update');
    Route::post('/certificates/{id}/approve', [AdminController::class, 'certificatesApprove'])->name('certificates.approve');
    Route::get('/certificates/{id}/view', [AdminController::class, 'certificatesView'])->name('certificates.view');
    Route::delete('/certificates/{id}', [AdminController::class, 'certificatesDelete'])->name('certificates.delete');
});

// ============================================================================
// SHARED ROUTES - Staff + Doctor can access Reports & Attendance
// ============================================================================
Route::middleware(['auth', 'verified', 'admin', 'role:staff,doctor'])->prefix('admin')->name('admin.')->group(function () {
    // Attendance Logs
    Route::get('/attendance', [AdminController::class, 'attendance'])->name('attendance');

    // Reports
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::post('/reports/generate', [AdminController::class, 'generateReport'])->name('reports.generate');
    Route::get('/reports/{id}/anti-rabies', [AdminController::class, 'viewAntiRabiesReport'])->name('reports.anti-rabies');
    Route::get('/reports/{id}/routine-services', [AdminController::class, 'viewRoutineServicesReport'])->name('reports.routine-services');
    Route::delete('/reports/{id}', [AdminController::class, 'deleteReport'])->name('reports.delete');
});

// ============================================================================
// ADMIN-ONLY ROUTES - System logs, Role management (replaces super admin)
// ============================================================================
Route::middleware(['auth', 'verified', 'admin', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin/Role Management
    Route::get('/admins', [AdminController::class, 'adminsIndex'])->name('admins.index');
    Route::get('/admins/create', [AdminController::class, 'adminsCreate'])->name('admins.create');
    Route::post('/admins', [AdminController::class, 'adminsStore'])->name('admins.store');
    Route::put('/admins/{id}', [AdminController::class, 'adminsUpdate'])->name('admins.update');
    Route::delete('/admins/{id}', [AdminController::class, 'adminsDestroy'])->name('admins.destroy');

    // Activity Logs
    Route::get('/logs', [AdminController::class, 'activityLogs'])->name('logs');
});