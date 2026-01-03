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
use App\Http\Controllers\FleetController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\StaffCustomerController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- Public / Guest Routes ---
Route::get('/', [HomeController::class, 'index'])->name('home');

// Login & Register
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/staff/login', [AuthController::class, 'showStaffLogin'])->name('staff.login');
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

// ====================================================
//  CUSTOMER ROUTES (Middleware: auth)
// ====================================================
Route::middleware('auth')->group(function () {
    
    // 1. Profile & Auth
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password'); 
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

    // 4. Finance
    Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
    Route::post('/finance/claim/{id}', [FinanceController::class, 'requestRefund'])->name('finance.claim');
    
    Route::get('/finance/pay/{id}', [FinanceController::class, 'payBalance'])->name('finance.pay');
    Route::post('/finance/pay/{id}', [FinanceController::class, 'submitBalance'])->name('finance.submit_balance');
    
    Route::get('/finance/pay-fine/{id}', [FinanceController::class, 'payFine'])->name('finance.pay_fine');
    Route::post('/finance/pay-fine/{id}', [FinanceController::class, 'submitFine'])->name('finance.submit_fine');

    // 5. Loyalty & Vouchers (FIXED SECTION)
    Route::get('/loyalty', [LoyaltyController::class, 'index'])->name('loyalty.index');
    Route::post('/loyalty/redeem', [LoyaltyController::class, 'redeemReward'])->name('loyalty.redeem'); // Route PENTING ini
    Route::post('/voucher/apply', [VoucherController::class, 'apply'])->name('voucher.apply');
    Route::get('/voucher/available', [VoucherController::class, 'getAvailableVouchers'])->name('voucher.available');

    Route::resource('penalties', PenaltyController::class);

    // Feedback
    Route::post('/bookings/{id}/feedback', [App\Http\Controllers\FeedbackController::class, 'store'])->name('feedback.store');


    Route::post('/notifications/mark-read', [App\Http\Controllers\BookingController::class, 'markNotificationsRead'])->name('notifications.markRead');
});

 // <--- PENUTUP UNTUK CUSTOMER AUTH (JANGAN PADAM)



// ====================================================
//  STAFF ROUTES (Middleware: auth:staff)
// ====================================================
Route::prefix('staff')->middleware(['auth:staff'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [StaffBookingController::class, 'dashboard'])->name('staff.dashboard');
    
    // Booking Management
    Route::get('/bookings', [StaffBookingController::class, 'index'])->name('staff.bookings.index');
    Route::get('/bookings/{id}', [StaffBookingController::class, 'show'])->name('staff.bookings.show');

    // Workflow
    Route::post('/bookings/{id}/verify-payment', [StaffBookingController::class, 'verifyPayment'])->name('staff.bookings.verify_payment');
    Route::post('/bookings/{id}/approve-agreement', [StaffBookingController::class, 'approveAgreement'])->name('staff.bookings.approve_agreement');
    Route::post('/bookings/{id}/pickup', [StaffBookingController::class, 'pickup'])->name('staff.bookings.pickup');
    Route::post('/bookings/{id}/return', [StaffBookingController::class, 'processReturn'])->name('staff.bookings.return');
    Route::post('/bookings/{id}/refund', [StaffBookingController::class, 'processRefund'])->name('staff.bookings.refund');
    Route::post('/bookings/{id}/reject', [StaffBookingController::class, 'reject'])->name('staff.bookings.reject');

    // Inspections
    Route::post('/inspections/{id}/store', [StaffBookingController::class, 'storeInspection'])->name('staff.inspections.store');

    // Fleet
    // --- FLEET MANAGEMENT ---
    // List Vehicles
    Route::get('/fleet', [FleetController::class, 'index'])->name('staff.fleet.index');
    
    // Create
    Route::get('/fleet/create', [FleetController::class, 'create'])->name('staff.fleet.create');
    Route::post('/fleet/store', [FleetController::class, 'store'])->name('staff.fleet.store');
    
    // SHOW DETAILS (The new page)
    Route::get('/fleet/{id}', [FleetController::class, 'show'])->name('staff.fleet.show');
    
    // Edit/Update
    Route::get('/fleet/{id}/edit', [FleetController::class, 'edit'])->name('staff.fleet.edit');
    Route::put('/fleet/{id}', [FleetController::class, 'update'])->name('staff.fleet.update');
    
    // Status & Delete
    Route::post('/fleet/status/{id}', [FleetController::class, 'updateStatus'])->name('staff.fleet.status');
    Route::delete('/fleet/{id}', [FleetController::class, 'destroy'])->name('staff.fleet.destroy');

    // BLOCK DATE (Add to JSON)
    Route::post('/fleet/{id}/block', [FleetController::class, 'blockDate'])->name('staff.fleet.block');
    
    // UNBLOCK DATE (Remove from JSON)
    Route::post('/fleet/{id}/unblock', [FleetController::class, 'unblockDate'])->name('staff.fleet.unblock');

    // Separate Inspection Mode

    // Customer Management
    Route::get('/customers', [App\Http\Controllers\StaffCustomerController::class, 'index'])->name('staff.customers.index');
    Route::get('/customers/{id}', [App\Http\Controllers\StaffCustomerController::class, 'show'])->name('staff.customers.show');
    Route::post('/customers/{id}/approve', [App\Http\Controllers\StaffCustomerController::class, 'approve'])->name('staff.customers.approve');
    Route::post('/customers/{id}/reject', [App\Http\Controllers\StaffCustomerController::class, 'reject'])->name('staff.customers.reject');
    Route::post('/customers/{id}/blacklist', [App\Http\Controllers\StaffCustomerController::class, 'toggleBlacklist'])->name('staff.customers.blacklist');

    // --- SEPARATE INSPECTION MODE (If needed for dedicated page) ---
    // Renamed to avoid conflict with 'storeInspection' above
    Route::get('/inspections', [InspectionController::class, 'index'])->name('staff.inspections.index');
    Route::get('/inspections/{id}/create', [InspectionController::class, 'create'])->name('staff.inspections.create');
    Route::post('/inspections/{id}', [InspectionController::class, 'store'])->name('staff.inspections.create_record');

    // Staff Assignment
    Route::post('/bookings/{id}/assign', [StaffBookingController::class, 'assignStaff'])->name('staff.bookings.assign');


    // --- STAFF MANAGEMENT (Admin Only) ---
    Route::middleware(['staff.admin'])->group(function () {
        Route::get('/management', [App\Http\Controllers\StaffManagementController::class, 'index'])->name('staff.management.index');
        Route::get('/management/create', [App\Http\Controllers\StaffManagementController::class, 'create'])->name('staff.management.create');
        Route::post('/management', [App\Http\Controllers\StaffManagementController::class, 'store'])->name('staff.management.store');
        Route::get('/management/{id}/edit', [App\Http\Controllers\StaffManagementController::class, 'edit'])->name('staff.management.edit');
        Route::put('/management/{id}', [App\Http\Controllers\StaffManagementController::class, 'update'])->name('staff.management.update');
        Route::delete('/management/{id}', [App\Http\Controllers\StaffManagementController::class, 'destroy'])->name('staff.management.destroy');
    });

    // Penalty & Loyalty manual trigger
    Route::post('/bookings/{id}/complete', [LoyaltyController::class, 'bookingCompleted'])->name('staff.bookings.complete');
    Route::resource('penalty', PenaltyController::class);
    
    // Staff Loyalty & Rewards Management
    Route::get('/loyalty', [LoyaltyController::class, 'staffIndex'])->name('staff.loyalty.index');
    Route::get('/loyalty/customer/{customerId}', [LoyaltyController::class, 'staffShowCustomer'])->name('staff.loyalty.show_customer');
    Route::post('/loyalty/voucher/store', [LoyaltyController::class, 'staffStoreVoucher'])->name('staff.loyalty.store_voucher');
    Route::get('/loyalty/voucher/{voucherId}/edit', [LoyaltyController::class, 'staffEditVoucher'])->name('staff.loyalty.edit_voucher');
    Route::put('/loyalty/voucher/{voucherId}', [LoyaltyController::class, 'staffUpdateVoucher'])->name('staff.loyalty.update_voucher');
    Route::delete('/loyalty/voucher/{voucherId}', [LoyaltyController::class, 'staffDeleteVoucher'])->name('staff.loyalty.delete_voucher');
    
    // Reporting Routes
    Route::get('/reports', [App\Http\Controllers\ReportController::class, 'index'])->name('staff.reports.index');
    Route::post('/reports/export', [App\Http\Controllers\ReportController::class, 'exportToDrive'])->name('staff.reports.export');
});