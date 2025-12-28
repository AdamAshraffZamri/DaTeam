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
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\FleetController; // Ensure this is imported
use App\Http\Controllers\InspectionController; // Ensure this is imported

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- Public / Guest Routes ---
Route::get('/', [HomeController::class, 'index'])->name('home');

// Login & Register
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Password Reset
Route::get('/forgot-password', [AuthController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'reset'])->name('password.update');

// Static Pages
Route::get('/about', function() { return view('pages.about'); })->name('pages.about');
Route::get('/faq', function() { return view('pages.faq'); })->name('pages.faq');
Route::get('/contact', function() { return view('pages.contact'); })->name('pages.contact');

// --- Protected Customer Routes ---
Route::middleware('auth')->group(function () {
    
    // 1. Profile & Auth
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // 2. Booking Process
    Route::get('/book', [BookingController::class, 'create'])->name('book.create'); 
    Route::get('/book/search', [BookingController::class, 'search'])->name('book.search'); 
    Route::get('/book/details/{id}', [BookingController::class, 'show'])->name('book.show'); 
    Route::get('/book/payment/{id}', [BookingController::class, 'payment'])->name('book.payment'); 
    Route::post('/book/payment/submit/{id}', [BookingController::class, 'submitPayment'])->name('book.payment.submit');

    // 3. Customer Dashboard
    Route::get('/my-bookings', [BookingController::class, 'index'])->name('book.index');
    Route::post('/my-bookings/cancel/{id}', [BookingController::class, 'cancel'])->name('book.cancel');   
    
    // Documents & Inspections
    Route::get('/book/agreement/preview', [BookingController::class, 'previewAgreement'])->name('book.agreement.preview');
    Route::get('/book/agreement/{id}', [BookingController::class, 'showAgreement'])->name('book.agreement');
    Route::post('/my-bookings/inspection/{id}', [BookingController::class, 'uploadInspection'])->name('book.inspection.upload');

    // 4. Finance, Loyalty & Penalties
    Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
    Route::post('/finance/claim/{id}', [FinanceController::class, 'requestRefund'])->name('finance.claim');
    
    Route::get('/finance/pay/{id}', [FinanceController::class, 'payBalance'])->name('finance.pay');
    Route::post('/finance/pay/{id}', [FinanceController::class, 'submitBalance'])->name('finance.submit_balance');
    
    Route::get('/finance/pay-fine/{id}', [FinanceController::class, 'payFine'])->name('finance.pay_fine');
    Route::post('/finance/pay-fine/{id}', [FinanceController::class, 'submitFine'])->name('finance.submit_fine');

    Route::resource('loyalty', LoyaltyController::class);
    Route::resource('penalties', PenaltyController::class);
    Route::post('/voucher/apply', [VoucherController::class, 'apply'])->name('voucher.apply');

    // Feedback / Review Route
    Route::post('/bookings/{id}/feedback', [App\Http\Controllers\FeedbackController::class, 'store'])->name('feedback.store');
});


// --- Staff / Admin Routes ---
Route::prefix('staff')->middleware(['auth:staff'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [StaffBookingController::class, 'dashboard'])->name('staff.dashboard');
    
    // Booking Management
    Route::get('/bookings', [StaffBookingController::class, 'index'])->name('staff.bookings.index');
    Route::get('/bookings/{id}', [StaffBookingController::class, 'show'])->name('staff.bookings.show');

    // --- RENTAL WORKFLOW ACTIONS ---
    // 1. Verify Payment
    Route::post('/bookings/{id}/verify-payment', [StaffBookingController::class, 'verifyPayment'])->name('staff.bookings.verify_payment');
    
    // 2. Approve Agreement
    Route::post('/bookings/{id}/approve-agreement', [StaffBookingController::class, 'approveAgreement'])->name('staff.bookings.approve_agreement');
    
    // 3. Pickup / Handover
    Route::post('/bookings/{id}/pickup', [StaffBookingController::class, 'pickup'])->name('staff.bookings.pickup');
    
    // 4. Return / Finalize (Complete Rental)
    Route::post('/bookings/{id}/return', [StaffBookingController::class, 'processReturn'])->name('staff.bookings.return');

    // 5. Refund (For Cancelled Bookings) - FIX: Removed double 'staff' prefix
    Route::post('/bookings/{id}/refund', [StaffBookingController::class, 'processRefund'])->name('staff.bookings.refund');

    // --- INSPECTIONS ---
    // Used for the "Staff Upload" modal in Booking Details
    Route::post('/inspections/{id}/store', [StaffBookingController::class, 'storeInspection'])->name('staff.inspections.store');

    // --- FLEET MANAGEMENT ---
    Route::get('/fleet', [FleetController::class, 'index'])->name('staff.fleet.index');
    Route::get('/fleet/create', [FleetController::class, 'create'])->name('staff.fleet.create');
    Route::post('/fleet/store', [FleetController::class, 'store'])->name('staff.fleet.store');
    Route::get('/fleet/{vehicle}', [FleetController::class, 'show'])->name('staff.fleet.show');
    Route::get('/fleet/{vehicle}/edit', [FleetController::class, 'edit'])->name('staff.fleet.edit');
    Route::put('/fleet/{vehicle}', [FleetController::class, 'update'])->name('staff.fleet.update');
    Route::delete('/fleet/{vehicle}', [FleetController::class, 'destroy'])->name('staff.fleet.destroy');
    Route::post('/fleet/status/{vehicle}', [FleetController::class, 'updateStatus'])->name('staff.fleet.update_status');

    // --- SEPARATE INSPECTION MODE (If needed for dedicated page) ---
    // Renamed to avoid conflict with 'storeInspection' above
    Route::get('/inspections', [InspectionController::class, 'index'])->name('staff.inspections.index');
    Route::get('/inspections/{id}/create', [InspectionController::class, 'create'])->name('staff.inspections.create');
    Route::post('/inspections/{id}', [InspectionController::class, 'store'])->name('staff.inspections.create_record');

    // Staff Assignment
    Route::post('/bookings/{id}/assign', [StaffBookingController::class, 'assignStaff'])->name('staff.bookings.assign');
});