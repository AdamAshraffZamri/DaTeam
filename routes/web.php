<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\PageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- Public / Guest Routes ---

// Home Page
Route::get('/', [HomeController::class, 'index'])->name('home');

// Login & Register
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Password Reset
Route::get('/password/reset', [AuthController::class, 'showReset'])->name('password.reset.custom');
Route::post('/password/reset', [AuthController::class, 'resetPassword']);

// Static Pages
Route::get('/about', [PageController::class, 'about'])->name('pages.about');
Route::get('/faq', [PageController::class, 'faq'])->name('pages.faq');
Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');


// --- Protected Routes (Require Login) ---
Route::middleware('auth')->group(function () {
    
    // 1. Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // 2. Booking Process (The full flow)
    Route::get('/book', [BookingController::class, 'create'])->name('book.create'); // Step 1: Search Form
    Route::get('/book/search', [BookingController::class, 'search'])->name('book.search'); // Step 2: Results List
    Route::get('/book/details/{id}', [BookingController::class, 'show'])->name('book.show'); // Step 3: Details
    Route::get('/book/payment/{id}', [BookingController::class, 'payment'])->name('book.payment'); // Step 4: Payment
    Route::get('/book/payment/submit/{id}', [BookingController::class, 'submitPayment'])->name('book.payment.submit'); // Step 5: Submit

    // 3. Dashboard Pages
    Route::get('/my-bookings', [BookingController::class, 'index'])->name('book.index');
    Route::get('/loyalty', [LoyaltyController::class, 'index'])->name('loyalty.index');
    Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
});