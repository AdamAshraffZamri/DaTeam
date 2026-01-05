<?php

namespace App\Http\Controllers;

use App\Models\LoyaltyPoint;
use App\Models\Voucher;
use App\Models\Booking;
use App\Models\LoyaltyHistory;
use App\Services\RentalRewardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

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

    // 2. Calculate Points Stats
    $pointsEarned = LoyaltyHistory::where('user_id', $userId)
        ->where('points_change', '>', 0)
        ->sum('points_change');

    $pointsRedeemed = abs(LoyaltyHistory::where('user_id', $userId)
        ->where('points_change', '<', 0)
        ->sum('points_change'));

    // 3. Get Active Vouchers
    $vouchers = Voucher::where(function($query) use ($userId) {
            $query->where('user_id', $userId)->orWhere('customerID', $userId);
        })
        ->where('status', 'active') // Check status active
        ->where('isUsed', false)
        ->whereDate('validUntil', '>=', now())
        ->get();

    // 4. PROGRESS BAR LOGIC (Yang baru update tadi)
    $allBookings = Booking::where('customerID', $userId)
        ->where('bookingStatus', 'Completed')
        ->get();

    // Filter: Hanya kira booking yang tempoh > 9 jam
    $qualifiedBookingsCount = $allBookings->filter(function ($booking) {
        $start = \Carbon\Carbon::parse($booking->pickupDate);
        $end = \Carbon\Carbon::parse($booking->returnDate);
        return $start->diffInHours($end) > 9;
    })->count();

    // Logic Cycle 12 Steps
    $cycleSize = 12;
    $currentInCycle = $qualifiedBookingsCount % $cycleSize; 
    
    if ($currentInCycle < 3) {
        $nextReward = "20% OFF";
        $targetStep = 3;
    } elseif ($currentInCycle < 6) {
        $nextReward = "50% OFF";
        $targetStep = 6;
    } elseif ($currentInCycle < 9) {
        $nextReward = "70% OFF";
        $targetStep = 9;
    } else {
        $nextReward = "Free Half Day";
        $targetStep = 12;
    }

    $bookingsNeeded = $targetStep - $currentInCycle;
    $progressPercent = ($currentInCycle / $cycleSize) * 100;

    // --- JANGAN LETAK RETURN KAT SINI! ---
    // (Awak ter-return kat sini tadi, so code bawah ni tak jalan)

    // 5. Rankings
    $userRank = LoyaltyPoint::where('points', '>', $loyalty->points)->count() + 1;
    $totalUsers = LoyaltyPoint::count();
    
    // (Optional) Kalau nak list top 10 rankings
    $rankings = LoyaltyPoint::with('customer')
        ->orderByDesc('points')
        ->take(10)
        ->get();

    // 6. Rewards List (Hardcoded)
    // Variable ni yang hilang tadi sebab code stop awal
    $rewards = [
        [
            'id' => 'sedap_kitchen',
            'name' => 'SEDAP KITCHEN', 
            'offer' => 'RM 5 OFF Discount',
            'points' => 200,
            'code' => 'SDKTN102478',
            'duration' => 3, 
            'icon' => 'fa-solid fa-utensils',
            'color' => 'bg-yellow-600/20 border-yellow-500/30'
        ],
        [
            'id' => 'pak_atong',
            'name' => 'PAK ATONG', 
            'offer' => 'RM5 Off Discount',
            'points' => 200, 
            'code' => 'PKATNG102589',
            'duration' => 3, 
            'icon' => 'fa-solid fa-utensils',
            'color' => 'bg-black-600/20 border-gray-500/30' // Betulkan typo grey -> gray
        ],
        [
            'id' => 'restoran_rafi',
            'name' => 'RESTORAN RAFI', 
            'offer' => 'RM5 Off Discount',
            'points' => 200, 
            'code' => 'RSTRN102690',
            'duration' => 3, 
            'icon' => 'fa-solid fa-utensils',
            'color' => 'bg-green-600/20 border-green-500/30' // Betulkan typo color class
        ],
        [
            'id' => 'pak_lah',
            'name' => 'PAK LAH', 
            'offer' => 'RM5 off Discount',
            'points' => 200, 
            'code' => 'PKLH102791',
            'duration' => 3, 
            'icon' => 'fa-solid fa-utensils',
            'color' => 'bg-red-600/20 border-red-500/30'
        ],
    ];

    // FINAL RETURN (Satu-satunya pintu keluar)
    return view('loyalty.index', compact(
        'loyalty',
        'vouchers',
        'qualifiedBookingsCount', // Saya tukar dari totalCompleted sbb kita guna qualified logic
        'currentInCycle',
        'progressPercent',
        'bookingsNeeded',
        'nextReward',
        'rankings',
        'userRank',
        'totalUsers',
        'rewards',
        'pointsEarned',
        'pointsRedeemed'
    ));
}

    // --- STAFF DASHBOARD: VIEW LOYALTY & REWARDS ---
    public function staffIndex()
    {
        // 1. Get all loyalty data
        $loyaltyStats = LoyaltyPoint::with('customer')
            ->orderByDesc('points')
            ->get();

        // 2. Total points distributed
        $totalPointsDistributed = LoyaltyHistory::where('points_change', '>', 0)->sum('points_change');
        $totalPointsRedeemed = abs(LoyaltyHistory::where('points_change', '<', 0)->sum('points_change'));

        // 3. Recent loyalty activities
        $recentActivities = LoyaltyHistory::with('customer')
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        // 4. All active vouchers (split by type)
        $rentalVouchers = Voucher::where('voucherType', 'Rental Discount')
            ->with('customer')
            ->orderByDesc('created_at')
            ->get();
        
        $merchantVouchers = Voucher::where('voucherType', 'Merchant Reward')
            ->with('customer')
            ->orderByDesc('created_at')
            ->get();

        // 5. Tier breakdown
        $tierBreakdown = [
            'Bronze' => LoyaltyPoint::where('tier', 'Bronze')->count(),
            'Silver' => LoyaltyPoint::where('tier', 'Silver')->count(),
            'Gold' => LoyaltyPoint::where('tier', 'Gold')->count(),
            'Platinum' => LoyaltyPoint::where('tier', 'Platinum')->count(),
        ];

        // 6. Top performers
        $topPerformers = LoyaltyPoint::with('customer')
            ->orderByDesc('points')
            ->take(10)
            ->get();

        return view('staff.loyalty.index', compact(
            'loyaltyStats', 'totalPointsDistributed', 'totalPointsRedeemed',
            'recentActivities', 'rentalVouchers', 'merchantVouchers', 'tierBreakdown', 'topPerformers'
        ));
    }

    // --- STAFF: VIEW CUSTOMER LOYALTY DETAILS ---
    public function staffShowCustomer($customerId)
    {
        // Get customer loyalty data
        $loyalty = LoyaltyPoint::where('user_id', $customerId)->firstOrFail();
        $customer = $loyalty->customer;

        // Get loyalty history
        $history = LoyaltyHistory::where('user_id', $customerId)
            ->orderByDesc('created_at')
            ->get();

        // Get customer vouchers
        $vouchers = Voucher::where('customerID', $customerId)
            ->orderByDesc('created_at')
            ->get();

        // Get booking count and stats
        $bookingCount = Booking::where('customerID', $customerId)
            ->where('bookingStatus', 'Completed')
            ->count();

        $totalSpent = Booking::where('customerID', $customerId)
            ->where('bookingStatus', 'Completed')
            ->sum('totalCost');

        return view('staff.loyalty.show-customer', compact(
            'loyalty', 'customer', 'history', 'vouchers', 'bookingCount', 'totalSpent'
        ));
    }

    // --- STAFF: STORE NEW VOUCHER ---
    public function staffStoreVoucher(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:vouchers,voucherCode',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:Rental Discount,Merchant Reward',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'description' => 'nullable|string|max:255',
        ]);

        Voucher::create([
            'voucherCode' => $request->code,
            'code' => $request->code,
            'voucherAmount' => $request->amount,
            'voucherType' => $request->type,
            'validFrom' => $request->valid_from,
            'validUntil' => $request->valid_until,
            'conditions' => $request->description,
            'isUsed' => false,
            'status' => 'active'
        ]);

        return back()->with('success', 'Voucher created successfully!');
    }

    // --- STAFF: GET VOUCHER FOR EDITING (JSON) ---
    public function staffEditVoucher($voucherId)
    {
        $voucher = Voucher::findOrFail($voucherId);
        
        return response()->json([
            'voucherID' => $voucher->voucherID,
            'voucherCode' => $voucher->voucherCode,
            'voucherAmount' => $voucher->voucherAmount,
            'voucherType' => $voucher->voucherType,
            'validFrom' => $voucher->validFrom,
            'validUntil' => $voucher->validUntil,
            'conditions' => $voucher->conditions,
        ]);
    }

    // --- STAFF: UPDATE VOUCHER ---
    public function staffUpdateVoucher(Request $request, $voucherId)
    {
        $request->validate([
            'code' => 'required|unique:vouchers,voucherCode,' . $voucherId . ',voucherID',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:Rental Discount,Merchant Reward',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'description' => 'nullable|string|max:255',
        ]);

        $voucher = Voucher::findOrFail($voucherId);
        $voucher->update([
            'voucherCode' => $request->code,
            'code' => $request->code,
            'voucherAmount' => $request->amount,
            'voucherType' => $request->type,
            'validFrom' => $request->valid_from,
            'validUntil' => $request->valid_until,
            'conditions' => $request->description,
        ]);

        return back()->with('success', 'Voucher updated successfully!');
    }

    // --- STAFF: DELETE VOUCHER ---
    public function staffDeleteVoucher($voucherId)
    {
        $voucher = Voucher::findOrFail($voucherId);
        $voucher->delete();

        return back()->with('success', 'Voucher deleted successfully!');
    }

    // --- LOGIC: BOOKING COMPLETED ---
    // CALL THIS from StaffBookingController when status -> 'Completed'
    // --- GET AVAILABLE VOUCHERS FOR CUSTOMER (JSON) ---
    public function getAvailableVouchers()
    {
        try {
            // Get active vouchers that are not expired
            $vouchers = Voucher::whereIn('status', ['active', 'Active'])
                ->orWhere('isUsed', false)
                ->get();

            // Filter in PHP for better control
            $filtered = $vouchers->filter(function($voucher) {
                // Check if valid until is greater than or equal to today
                $validUntil = $voucher->validUntil ?? $voucher->expires_at;
                if ($validUntil && $validUntil < now()) {
                    return false;
                }
                return true;
            })->map(function($voucher) {
                $code = $voucher->code ?? $voucher->voucherCode ?? 'N/A';
                $amount = $voucher->voucherAmount ?? $voucher->discount_percent ?? 0;
                $type = $voucher->voucherType ?? 'Voucher';
                $expires = $voucher->validUntil ?? $voucher->expires_at;
                
                return [
                    'id' => $voucher->voucherID ?? $voucher->id ?? 0,
                    'code' => $code,
                    'amount' => $amount,
                    'type' => $type,
                    'expires' => $expires ? $expires->format('d M Y') : 'No expiry',
                ];
            })->values();

            return response()->json($filtered);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // --- BOOKING COMPLETED ---
    public function bookingCompleted($bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        $userId = $booking->customerID;

        // CHECK IF ALREADY PROCESSED: Don't process the same booking twice
        $alreadyProcessed = LoyaltyHistory::where('user_id', $userId)
            ->where('reason', 'like', "%Booking #$bookingId%")
            ->exists();
        
        if ($alreadyProcessed) {
            \Log::warning("Booking $bookingId already processed for customer $userId - skipping duplicate");
            return false;
        }

        // 1. AWARD LOYALTY POINTS (Based on Total Cost: RM1 = 1 Point)
        $pointsEarned = (int) $booking->totalCost;
        
        $loyalty = LoyaltyPoint::firstOrCreate(
            ['user_id' => $userId],
            ['points' => 0, 'tier' => 'Bronze', 'rental_bookings_count' => 0]
        );

        $loyalty->points += $pointsEarned;
        $this->updateTier($loyalty);
        $loyalty->save();

        // 2. LOG POINTS EARNED
        LoyaltyHistory::create([
            'user_id' => $userId,
            'points_change' => $pointsEarned,
            'reason' => "Rental Reward (Booking #{$booking->bookingID})"
        ]);

        // 3. PROCESS RENTAL REWARD (Service handles 9-hour tracking & voucher generation)
        $rewardService = new RentalRewardService();
        $rewardService->processBookingCompletion($booking);

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
        $user = Auth::user();
        
        // Check for unpaid penalties - block merchant voucher redemption
        $unpaidPenalties = \App\Models\Penalties::where('customerID', $user->customerID)
            ->where(function($query) {
                $query->where('status', 'Pending')
                      ->orWhere('penaltyStatus', 'Unpaid');
            })
            ->get();
        
        if ($unpaidPenalties->count() > 0) {
            $totalPenalty = $unpaidPenalties->sum(function($penalty) {
                return $penalty->amount ?? ($penalty->penaltyFees + $penalty->fuelSurcharge + $penalty->mileageSurcharge);
            });
            
            return response()->json([
                'success' => false, 
                'message' => 'You have unpaid penalties totaling MYR ' . number_format($totalPenalty, 2) . '. Please pay your outstanding penalties before redeeming merchant vouchers.'
            ]);
        }
        
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