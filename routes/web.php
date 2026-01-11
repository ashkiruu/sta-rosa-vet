<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Registration Step 1
Route::get('register/step-1', [RegisterController::class, 'step1'])->name('register.step1');
Route::post('register/step-1', [RegisterController::class, 'postStep1'])->name('register.step1.post');

// Registration Step 2

Route::get('/register/step2', [RegisterController::class, 'step2'])->name('register.step2');
Route::post('/register/step2', [RegisterController::class, 'postStep2'])->name('register.step2.post');


// Registration Step 3

Route::get('/register/step3', [RegisterController::class, 'step3'])->name('register.step3');
Route::post('/register/step3', [RegisterController::class, 'postStep3'])->name('register.step3.post');
