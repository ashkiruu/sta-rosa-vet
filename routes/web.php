<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AdminController;




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


// PUBLIC ROUTE - QR Code Verification (NO AUTH REQUIRED!)
// This must be outside auth middleware so receptionist can scan
Route::get('/appointments/verify/{id}/{token}', [AppointmentController::class, 'verifyAppointment'])
    ->name('appointments.verify');

    


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

    // Appointment utility routes
    Route::get('/appointments/taken-times', [AppointmentController::class, 'getTakenTimes'])
        ->name('appointments.takenTimes');
    Route::get('/appointments/fully-booked', [AppointmentController::class, 'getFullyBookedDates'])
        ->name('appointments.fullyBooked');
    Route::get('/appointments/clinic-schedule', [AppointmentController::class, 'getClinicSchedule'])
        ->name('appointments.clinic-schedule');
    
    Route::get('/appointments/{id}', [AppointmentController::class, 'show'])->name('appointments.show');
    Route::post('/appointments/{id}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');

    // QR Code Routes (for authenticated users to view their QR codes)
    Route::get('/appointments/{id}/qrcode', [AppointmentController::class, 'showQRCode'])
        ->name('appointments.qrcode');
    Route::get('/appointments/{id}/qrcode/download', [AppointmentController::class, 'downloadQRCode'])
        ->name('appointments.qrcode.download');

    // Check appointment status (AJAX polling for auto-redirect)
    Route::get('/appointments/{id}/check-status', [AppointmentController::class, 'checkStatus'])
        ->name('appointments.checkStatus');

    // Notifications
    Route::get('/notifications/view/{id}', [NotificationController::class, 'viewAppointment'])
        ->name('notifications.viewAppointment');
    Route::post('/notifications/mark-all-seen', [NotificationController::class, 'markAllSeen'])
        ->name('notifications.markAllSeen');

    // Appointment Notifications (file-based)
    Route::post('/appointments/notifications/mark-seen', [AppointmentController::class, 'markNotificationSeen'])
        ->name('appointments.notifications.markSeen');
    Route::post('/appointments/notifications/mark-all-seen', [AppointmentController::class, 'markAllNotificationsSeen'])
        ->name('appointments.notifications.markAllSeen');

    // User Certificate Routes (for regular users to view/download their certificates)
    Route::get('/my-certificates', [AppointmentController::class, 'certificatesIndex'])
        ->name('certificates.index');
    Route::get('/my-certificates/{id}/download', [AppointmentController::class, 'certificateDownload'])
        ->name('certificates.download');
    Route::get('/my-certificates/{id}/view', [AppointmentController::class, 'certificateDownload'])
        ->name('certificates.view');
});

// Admin-Only Routes (Both Normal Admin and Super Admin can access)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard Overview
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users/{id}', [AdminController::class, 'showUser'])->name('user.show'); 

    // 1. User Verification (The OCR Review Module)
    Route::get('/verifications', [AdminController::class, 'pendingVerifications'])->name('verifications');
    Route::post('/verifications/{id}/approve', [AdminController::class, 'approveUser'])->name('user.approve');
    Route::post('/verifications/{id}/reject', [AdminController::class, 'rejectUser'])->name('user.reject');

    // 2. Appointment Management (The Conflict Resolution Module)
    Route::get('/appointments', [AdminController::class, 'appointments'])->name('appointment_index');
    Route::post('/appointments/{id}/approve', [AdminController::class, 'approveAppointment'])->name('appointments.approve');
    Route::post('/appointments/{id}/reject', [AdminController::class, 'rejectAppointment'])->name('appointments.reject');

    // 3. Schedule Management
    Route::post('/schedule/toggle', [AdminController::class, 'toggleDateStatus'])->name('schedule.toggle');
    
    // 4. Attendance Logs
    Route::get('/attendance', [AdminController::class, 'attendance'])->name('attendance');

    // 5. Certificate Management (Admin)
    Route::get('/certificates', [AdminController::class, 'certificatesIndex'])->name('certificates.index');
    Route::get('/certificates/create/{appointmentId}', [AdminController::class, 'certificatesCreate'])->name('certificates.create');
    Route::post('/certificates', [AdminController::class, 'certificatesStore'])->name('certificates.store');
    Route::get('/certificates/{id}/edit', [AdminController::class, 'certificatesEdit'])->name('certificates.edit');
    Route::put('/certificates/{id}', [AdminController::class, 'certificatesUpdate'])->name('certificates.update');
    Route::post('/certificates/{id}/approve', [AdminController::class, 'certificatesApprove'])->name('certificates.approve');
    Route::get('/certificates/{id}/view', [AdminController::class, 'certificatesView'])->name('certificates.view');
    Route::delete('/certificates/{id}', [AdminController::class, 'certificatesDelete'])->name('certificates.delete');

    // 6. Reports (The Summary Report Module)
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::post('/reports/generate', [AdminController::class, 'generateReport'])->name('reports.generate');
    Route::get('/reports/{id}/anti-rabies', [AdminController::class, 'viewAntiRabiesReport'])->name('reports.anti-rabies');
    Route::get('/reports/{id}/routine-services', [AdminController::class, 'viewRoutineServicesReport'])->name('reports.routine-services');
    Route::delete('/reports/{id}', [AdminController::class, 'deleteReport'])->name('reports.delete');
});

// Super Admin Only Routes
Route::middleware(['auth', 'admin', 'superadmin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin Management (Super Admin Only)
    Route::get('/admins', [AdminController::class, 'adminsIndex'])->name('admins.index');
    Route::get('/admins/create', [AdminController::class, 'adminsCreate'])->name('admins.create');
    Route::post('/admins', [AdminController::class, 'adminsStore'])->name('admins.store');
    Route::put('/admins/{id}', [AdminController::class, 'adminsUpdate'])->name('admins.update');
    Route::delete('/admins/{id}', [AdminController::class, 'adminsDestroy'])->name('admins.destroy');

    // Activity Logs (Super Admin Only)
    Route::get('/logs', [AdminController::class, 'activityLogs'])->name('logs');
});