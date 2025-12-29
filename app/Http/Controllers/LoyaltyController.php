<?php

namespace App\Http\Controllers;

use App\Models\LoyaltyPoint;
use App\Models\Voucher;
use App\Models\Booking;
use App\Models\LoyaltyHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LoyaltyController extends Controller
{

    public function index()
    {
        $userId = Auth::id();
        
        // Get loyalty points data
        $loyalty = LoyaltyPoint::where('user_id', $userId)->first();
        
        // If no loyalty record exists, create one
        if (!$loyalty) {
            $loyalty = LoyaltyPoint::create([
                'user_id' => $userId,
                'points' => 0,
                'tier' => 'bronze'
            ]);
        }
        
        // Calculate points earned and redeemed from history
        $pointsEarned = LoyaltyHistory::where('user_id', $userId)
            ->where('points_change', '>', 0)
            ->sum('points_change');
        
        $pointsRedeemed = abs(LoyaltyHistory::where('user_id', $userId)
            ->where('points_change', '<', 0)
            ->sum('points_change'));
        
        // Add calculated values to loyalty object
        $loyalty->points_earned = $pointsEarned;
        $loyalty->points_redeemed = $pointsRedeemed;
        
        // Get vouchers - check both user_id and customerID columns
        $vouchers = Voucher::where(function($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->orWhere('customerID', $userId);
            })
            ->where(function($query) {
                $query->where('status', 'unused')
                      ->orWhere('isUsed', false);
            })
            ->get();
        
        // Count completed bookings for voucher progress
        $completedBookings = Booking::where('customerID', $userId)
            ->where('bookingStatus', 'Completed')
            ->count();
        
        $voucherProgress = $completedBookings % 3;
        $bookingsUntilVoucher = 3 - $voucherProgress;
        
        // Get top rankings for leaderboard
        $rankings = LoyaltyPoint::with('customer')
            ->orderByDesc('points')
            ->take(10)
            ->get();
        
        // Find user's rank
        $userRank = LoyaltyPoint::where('points', '>', $loyalty->points)->count() + 1;
        $totalUsers = LoyaltyPoint::count();
        
        return view('loyalty.index', compact(
            'loyalty', 
            'vouchers', 
            'voucherProgress', 
            'bookingsUntilVoucher',
            'rankings',
            'userRank',
            'totalUsers'
        ));
    }

    /**
     * Bila booking berjaya
     */
    public function bookingCompleted($bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        $userId = $booking->customerID;

        /* ===============================
           1️⃣ TAMBAH LOYALTY POINT
        =============================== */

        $loyalty = LoyaltyPoint::firstOrCreate(
            ['user_id' => $userId],
            ['points' => 0, 'tier' => 'bronze']
        );

        $pointsToAdd = 100; // contoh 100 point setiap booking
        $loyalty->points += $pointsToAdd;

        // Tentukan tier
        if ($loyalty->points >= 5000) {
            $loyalty->tier = 'platinum';
        } elseif ($loyalty->points >= 2000) {
            $loyalty->tier = 'gold';
        } elseif ($loyalty->points >= 500) {
            $loyalty->tier = 'silver';
        } else {
            $loyalty->tier = 'bronze';
        }

        $loyalty->save();

        // Simpan sejarah point
        LoyaltyHistory::create([
            'user_id' => $userId,
            'points_change' => $pointsToAdd,
            'reason' => 'Successful booking'
        ]);

        /* ===============================
           2️⃣ CHECK SETIAP 3 BOOKING → VOUCHER
        =============================== */

        $completedBookings = Booking::where('customerID', $userId)
            ->where('bookingStatus', 'Completed')
            ->count();

        if ($completedBookings % 3 == 0) {
            $discountPercent = collect([5, 10, 20, 30])->random();
            Voucher::create([
                'customerID' => $userId,
                'user_id' => $userId,
                'voucherCode' => 'HASTA-' . strtoupper(Str::random(8)),
                'code' => 'HASTA-' . strtoupper(Str::random(8)),
                'voucherAmount' => 0, // Will be calculated based on discount_percent when used
                'discount_percent' => $discountPercent,
                'voucherType' => 'Discount',
                'redeem_place' => 'HASTA Platform',
                'validFrom' => now(),
                'validUntil' => now()->addMonth(),
                'expires_at' => now()->addMonth(),
                'conditions' => 'Valid for any car or motorcycle rental. One-time use only.',
                'terms_conditions' => 'Valid for any car or motorcycle rental. One-time use only.',
                'isUsed' => false,
                'status' => 'unused'
            ]);
        }

        return back()->with('success', 'Booking completed. Loyalty points updated.');
    }

    public function create()
    {
        // Keep for compatibility if needed
    }

    public function store(Request $request)
    {
        // Keep for compatibility if needed
    }

    public function edit($id)
    {
        // Keep for compatibility if needed
    }

    public function update(Request $request, $id)
    {
        // Keep for compatibility if needed
    }

    public function destroy($id)
    {
        // Keep for compatibility if needed
    }
}
