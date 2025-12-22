<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\StaffBookingController;
use App\Http\Controllers\LoyaltyController; 
use App\Http\Controllers\PenaltyController; 
use App\Http\Controllers\FinanceController; 



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

// --- Password Reset Routes (Email Flow) ---
Route::get('/forgot-password', [AuthController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'reset'])->name('password.update');

// --- Placeholder Static Pages (Fixes Navbar Errors) ---
// These return simple text/views so clicking the links doesn't crash the app
Route::get('/about', function() { return view('pages.about'); })->name('pages.about');
Route::get('/faq', function() { return view('pages.faq'); })->name('pages.faq');
Route::get('/contact', function() { return view('pages.contact'); })->name('pages.contact');

// --- Protected Customer Routes (Require Login) ---
Route::middleware('auth')->group(function () {
    
    // 1. Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit'); // To show the form
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update'); // To save the data
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // 2. Booking Process (Customer Flow)
    Route::get('/book', [BookingController::class, 'create'])->name('book.create'); 
    Route::get('/book/search', [BookingController::class, 'search'])->name('book.search'); 
    Route::get('/book/details/{id}', [BookingController::class, 'show'])->name('book.show'); 
    Route::get('/book/payment/{id}', [BookingController::class, 'payment'])->name('book.payment'); 
    Route::post('/book/payment/submit/{id}', [BookingController::class, 'submitPayment'])->name('book.payment.submit');
    Route::get('/book/create', [BookingController::class, 'create'])->name('book.create');

    // 3. Customer Dashboard Pages
    // REMOVED DUPLICATE /my-bookings line here
    Route::get('/my-bookings', [BookingController::class, 'index'])->name('book.index');
    Route::post('/my-bookings/cancel/{id}', [App\Http\Controllers\BookingController::class, 'cancel'])->name('book.cancel');   
    Route::get('/my-bookings/edit/{id}', [BookingController::class, 'edit'])->name('book.edit');
    Route::put('/my-bookings/update/{id}', [BookingController::class, 'update'])->name('book.update');
    Route::get('/my-bookings/agreement/{id}', [App\Http\Controllers\BookingController::class, 'showAgreement'])->name('book.agreement');
    // Placeholders for Loyalty/Finance (Prevents Crash) (ADAM)
    
    Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
    Route::resource('loyalty', LoyaltyController::class);
    Route::resource('penalties', PenaltyController::class);



    // View Digital Agreement
    Route::get('/book/agreement/{id}', [BookingController::class, 'showAgreement'])->name('book.agreement');
});


// --- NEW: Staff / Admin Routes (Require Login + Staff Access) ---
Route::prefix('staff')->middleware(['auth:staff'])->group(function () {
    
    // Staff Dashboard
    Route::get('/dashboard', [StaffBookingController::class, 'dashboard'])->name('staff.dashboard');

    // Booking Management
    Route::get('/bookings', [StaffBookingController::class, 'index'])->name('staff.bookings.index');
    
    // Actions (Approve / Reject)
    Route::post('/bookings/{id}/approve', [StaffBookingController::class, 'approve'])->name('staff.bookings.approve');
    Route::post('/bookings/{id}/finalize', [StaffBookingController::class, 'finalize'])->name('staff.bookings.finalize');

    Route::get('/fleet', [FleetController::class, 'index']);
    Route::get('/fleet/create', [FleetController::class, 'create']);
    Route::post('/fleet/store', [FleetController::class, 'store']);

    Route::post('/fleet/status/{vehicle}', [FleetController::class, 'updateStatus']);
});
