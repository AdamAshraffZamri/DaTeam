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
    // --- DISPLAY DASHBOARD ---
    public function index()
    {
        $userId = Auth::id();
        
        // 1. Get or Init Loyalty
        $loyalty = LoyaltyPoint::firstOrCreate(
            ['user_id' => $userId],
            ['points' => 0, 'tier' => 'Bronze']
        );
        
        // 2. Calculate Stats
        $pointsEarned = LoyaltyHistory::where('user_id', $userId)->where('points_change', '>', 0)->sum('points_change');
        $pointsRedeemed = abs(LoyaltyHistory::where('user_id', $userId)->where('points_change', '<', 0)->sum('points_change'));
        
        $loyalty->points_earned = $pointsEarned;
        $loyalty->points_redeemed = $pointsRedeemed;
        
        // 3. Get User's Active Vouchers
        $vouchers = Voucher::where(function($query) use ($userId) {
                $query->where('user_id', $userId)->orWhere('customerID', $userId);
            })
            ->where('isUsed', false)
            ->whereDate('validUntil', '>=', now())
            ->get();
        
        // 4. Calculate Progress (Cycle of 6: 3 for 10%, 6 for 30%)
        $completedBookings = Booking::where('customerID', $userId)
            ->where('bookingStatus', 'Completed')
            ->count();
            
        $cyclePosition = $completedBookings % 6; 
        // Logic: 
        // 0, 1, 2 -> Aiming for 3 (10%)
        // 3, 4, 5 -> Aiming for 6 (30%)
        
        if ($cyclePosition < 3) {
            $nextReward = "10% OFF Voucher";
            $bookingsNeeded = 3 - $cyclePosition;
            $progressPercent = ($cyclePosition / 3) * 100;
        } else {
            $nextReward = "30% OFF Voucher";
            $bookingsNeeded = 6 - $cyclePosition;
            $progressPercent = (($cyclePosition - 3) / 3) * 100;
        }

        // 5. Rankings
        $rankings = LoyaltyPoint::with('customer')
            ->orderByDesc('points')
            ->take(10)
            ->get();
            
        $userRank = LoyaltyPoint::where('points', '>', $loyalty->points)->count() + 1;
        $totalUsers = LoyaltyPoint::count();

        // 6. Define Rewards List (Hardcoded as per requirements)
        $rewards = [
            [
                'id' => 'zus_coffee',
                'name' => 'ZUS COFFEE', 
                'offer' => '10% OFF Discount',
                'points' => 150, // Updated to 150 as per text
                'code' => 'ZXSOD102263',
                'duration' => 2, // months
                'icon' => 'fa-coffee',
                'color' => 'bg-blue-600/20 border-blue-500/30'
            ],
            [
                'id' => 'tealive',
                'name' => 'TEALIVE', 
                'offer' => 'BUY 2 FREE 1',
                'points' => 300, 
                'code' => 'TLOMN102356',
                'duration' => 2, 
                'icon' => 'fa-mug-hot',
                'color' => 'bg-purple-600/20 border-purple-500/30'
            ],
            [
                'id' => 'grab',
                'name' => 'GRAB FOOD', 
                'offer' => 'FREE DELIVERY',
                'points' => 750, 
                'code' => 'GRB12903X01',
                'duration' => 4, 
                'icon' => 'fa-utensils',
                'color' => 'bg-green-600/20 border-green-500/30'
            ],
            [
                'id' => 'tgv',
                'name' => 'TGV CINEMAS', 
                'offer' => '50% OFF FOOD',
                'points' => 1000, 
                'code' => 'TGV00DJ812',
                'duration' => 8, 
                'icon' => 'fa-film',
                'color' => 'bg-red-600/20 border-red-500/30'
            ],
        ];
        
        return view('loyalty.index', compact(
            'loyalty', 'vouchers', 'nextReward', 'bookingsNeeded', 
            'progressPercent', 'rankings', 'userRank', 'totalUsers', 'rewards'
        ));
    }

    // --- LOGIC: BOOKING COMPLETED ---
    // CALL THIS from StaffBookingController when status -> 'Completed'
    public function bookingCompleted($bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        $userId = $booking->customerID;

        // 1. AWARD POINTS (Based on Total Cost: RM1 = 1 Point)
        $pointsEarned = (int) $booking->totalCost;
        
        $loyalty = LoyaltyPoint::firstOrCreate(
            ['user_id' => $userId],
            ['points' => 0, 'tier' => 'Bronze']
        );

        $loyalty->points += $pointsEarned;
        $this->updateTier($loyalty); // Update Tier Logic
        $loyalty->save();

        LoyaltyHistory::create([
            'user_id' => $userId,
            'points_change' => $pointsEarned,
            'reason' => "Rental Reward (Booking #{$booking->bookingID})"
        ]);

        // 2. CHECK VOUCHER CYCLE
        $completedCount = Booking::where('customerID', $userId)
            ->where('bookingStatus', 'Completed')
            ->count(); // This count includes the one just completed

        // Cycle Logic: 3rd, 9th, 15th... -> 10%
        // Cycle Logic: 6th, 12th, 18th... -> 30%
        
        if ($completedCount > 0 && $completedCount % 6 == 3) {
            $this->issueRentalVoucher($userId, 10, 12, 'Hours'); // 10% Off, Min 12 Hours
        } elseif ($completedCount > 0 && $completedCount % 6 == 0) {
            $this->issueRentalVoucher($userId, 30, 1, 'Days');   // 30% Off, Min 1 Day
        }

        return true;
    }

    // --- HELPER: UPDATE TIER ---
    private function updateTier($loyalty) {
        if ($loyalty->points >= 5000) $loyalty->tier = 'Platinum';
        elseif ($loyalty->points >= 2500) $loyalty->tier = 'Gold';
        elseif ($loyalty->points >= 1000) $loyalty->tier = 'Silver';
        else $loyalty->tier = 'Bronze';
    }

    // --- HELPER: ISSUE RENTAL VOUCHER ---
    private function issueRentalVoucher($userId, $percent, $minDuration, $unit) {
        Voucher::create([
            'customerID' => $userId,
            'user_id' => $userId,
            'voucherCode' => 'HASTA' . $percent . '-' . strtoupper(Str::random(6)),
            'code' => 'HASTA' . $percent . '-' . strtoupper(Str::random(6)),
            'voucherAmount' => 0, // Calculated at runtime
            'discount_percent' => $percent,
            'voucherType' => 'Rental Discount',
            'redeem_place' => 'HASTA Platform',
            'validFrom' => now(),
            'validUntil' => now()->addMonths(2), // Generic 2 month validity
            'conditions' => "Minimum rental of $minDuration $unit.",
            'isUsed' => false,
            'status' => 'unused'
        ]);
    }

    // --- LOGIC: REDEEM REWARD (Non-Rental Vouchers) ---
    public function redeemReward(Request $request)
    {
        $userId = Auth::id();
        $loyalty = LoyaltyPoint::where('user_id', $userId)->first();
        
        $rewardId = $request->input('reward_id');
        
        // Define Rewards Map (Should match index)
        $rewardsMap = [
            'zus_coffee' => ['points' => 150, 'code' => 'ZXSOD102263', 'months' => 2, 'name' => 'ZUS Coffee'],
            'tealive' =>    ['points' => 300, 'code' => 'TLOMN102356', 'months' => 2, 'name' => 'Tealive'],
            'grab' =>       ['points' => 750, 'code' => 'GRB12903X01', 'months' => 4, 'name' => 'Grab Food'],
            'tgv' =>        ['points' => 1000, 'code' => 'TGV00DJ812', 'months' => 8, 'name' => 'TGV Cinemas'],
        ];

        if (!array_key_exists($rewardId, $rewardsMap)) {
            return response()->json(['success' => false, 'message' => 'Invalid reward.']);
        }

        $reward = $rewardsMap[$rewardId];

        // 1. Validation: Check Points
        if (!$loyalty || $loyalty->points < $reward['points']) {
            return response()->json(['success' => false, 'message' => 'Insufficient points to redeem this reward.']);
        }

        // 2. Deduct Points
        $loyalty->decrement('points', $reward['points']);
        
        LoyaltyHistory::create([
            'user_id' => $userId,
            'points_change' => -$reward['points'],
            'reason' => "Redeemed {$reward['name']} Voucher"
        ]);

        // 3. Create Voucher Record
        // We append a random string to the code to ensure uniqueness in DB if the code column is unique,
        // otherwise users share the same code string. Assuming unique requirement:
        $uniqueCode = $reward['code'] . '-' . rand(100,999); 

        Voucher::create([
            'customerID' => $userId,
            'user_id' => $userId,
            'voucherCode' => $uniqueCode,
            'code' => $reward['code'], // The actual code to show the shop
            'voucherAmount' => 0,
            'voucherType' => 'Merchant Reward',
            'redeem_place' => $reward['name'],
            'validFrom' => now(),
            'validUntil' => now()->addMonths($reward['months']),
            'conditions' => "Show this code at {$reward['name']} to claim.",
            'isUsed' => false,
            'status' => 'active'
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Voucher Successfully Claimed!',
            'code' => $reward['code'],
            'voucher_name' => $reward['name']
        ]);
    }
}