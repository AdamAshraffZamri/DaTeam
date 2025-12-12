<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// --- Public / Guest Routes ---

// Home Page (Landing page from Screenshot 5)
Route::get('/', [HomeController::class, 'index'])->name('home');

// Login Routes (Screenshot 1)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Registration Routes (Sign Up)
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Custom Password Reset (Screenshot 2 & 4 - using Security Question)
Route::get('/password/reset', [AuthController::class, 'showReset'])->name('password.reset.custom');
Route::post('/password/reset', [AuthController::class, 'resetPassword']);


// --- Protected Routes (Require Login) ---
Route::middleware('auth')->group(function () {
    
    // Profile Management (Screenshot 3)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});