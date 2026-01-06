<?php

namespace App\Http\Controllers;

use App\Models\LoyaltyPoint;
use App\Models\Voucher;
use App\Models\Booking;
use App\Models\LoyaltyHistory;
use App\Models\Reward; // <--- PASTIKAN MODEL INI WUJUD
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LoyaltyController extends Controller
{
    // =========================================================================
    // CUSTOMER SIDE
    // =========================================================================

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
            ->where('status', 'active') 
            ->where('isUsed', false)
            ->whereDate('validUntil', '>=', now())
            ->get();

        // 4. Get Past Vouchers (History)
        $pastVouchers = Voucher::where(function($query) use ($userId) {
                $query->where('user_id', $userId)->orWhere('customerID', $userId);
            })
            ->where(function($q) {
                $q->where('isUsed', true) // Dah guna
                  ->orWhereDate('validUntil', '<', now()); // Atau dah expired
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        // 5. PROGRESS BAR LOGIC (Loyalty Road)
        $allBookings = Booking::where('customerID', $userId)
            ->where('bookingStatus', 'Completed')
            ->get();

        // Filter: Hanya kira booking yang tempoh > 9 jam
        $qualifiedBookingsCount = $allBookings->filter(function ($booking) {
            $start = \Carbon\Carbon::parse($booking->originalDate . ' ' . $booking->bookingTime);
            $end = \Carbon\Carbon::parse($booking->returnDate . ' ' . $booking->returnTime);
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

        // 6. Rankings
        $userRank = LoyaltyPoint::where('points', '>', $loyalty->points)->count() + 1;
        $totalUsers = LoyaltyPoint::count();
        
        $rankings = LoyaltyPoint::with('customer')
            ->orderByDesc('points')
            ->take(10)
            ->get();

        // 7. [UPDATED] FETCH DYNAMIC REWARDS FROM DB
        // Ambil rewards yang aktif sahaja
        $rewards = Reward::where('is_active', true)->get();

        return view('loyalty.index', compact(
            'loyalty',
            'vouchers',
            'pastVouchers',
            'qualifiedBookingsCount',
            'currentInCycle',
            'progressPercent',
            'bookingsNeeded',
            'nextReward',
            'rankings',
            'userRank',
            'totalUsers',
            'rewards', // Ini sekarang data dari DB
            'pointsEarned',
            'pointsRedeemed'
        ));
    }

    // --- LOGIC: REDEEM REWARD (Non-Rental Vouchers) [UPDATED] ---
    public function redeemReward(Request $request)
    {
        $userId = Auth::id();
        $user = Auth::user();
        
        // 1. Check penalties first
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
                'message' => 'You have unpaid penalties totaling MYR ' . number_format($totalPenalty, 2) . '. Please pay first.'
            ]);
        }
        
        // 2. Find Reward in DB
        $rewardId = $request->input('reward_id');
        $reward = Reward::find($rewardId);

        if (!$reward || !$reward->is_active) {
            return response()->json(['success' => false, 'message' => 'Reward not found or inactive.']);
        }

        // 3. Check Points
        $loyalty = LoyaltyPoint::where('user_id', $userId)->first();
        if (!$loyalty || $loyalty->points < $reward->points_required) {
            return response()->json(['success' => false, 'message' => 'Insufficient points.']);
        }

        // 4. Deduct Points & Log
        $loyalty->decrement('points', $reward->points_required);
        
        LoyaltyHistory::create([
            'user_id' => $userId,
            'points_change' => -$reward->points_required,
            'reason' => "Redeemed {$reward->name} Voucher"
        ]);

        // 5. Generate Voucher
        $uniqueCode = $reward->code_prefix . rand(1000,9999) . Str::upper(Str::random(3)); 

        Voucher::create([
            'customerID' => $userId,
            'user_id' => $userId,
            'voucherCode' => $uniqueCode,
            'code' => $uniqueCode,
            'voucherAmount' => 0,
            'voucherType' => 'Merchant Reward',
            'redeem_place' => $reward->name,
            'validFrom' => now(),
            'validUntil' => now()->addMonths($reward->validity_months),
            'conditions' => "Show this code at {$reward->name} to claim. {$reward->offer_description}",
            'isUsed' => false,
            'status' => 'active'
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Voucher Successfully Claimed!',
            'code' => $uniqueCode,
            'voucher_name' => $reward->name
        ]);
    }

    // --- CUSTOMER SELF-REDEEM (Guna Depan Kaunter) ---
    public function useVoucherNow(Request $request)
    {
        $userId = Auth::id();
        $code = $request->code;

        $voucher = Voucher::where('code', $code)
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)->orWhere('customerID', $userId);
            })
            ->first();

        if (!$voucher) {
            return response()->json(['success' => false, 'message' => 'Voucher tidak dijumpai.']);
        }

        if ($voucher->isUsed) {
            return response()->json(['success' => false, 'message' => 'Voucher ini sudah digunakan.']);
        }

        $voucher->update([
            'isUsed' => true,
            'status' => 'redeemed'
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Voucher berjaya ditebus!',
            'date' => now()->format('d M Y, h:i A')
        ]);
    }

    // =========================================================================
    // STAFF SIDE
    // =========================================================================

    // --- STAFF DASHBOARD: VIEW LOYALTY & REWARDS [UPDATED WITH FILTER] ---
    public function staffIndex(Request $request)
    {
        // 1. Get all loyalty data
        $loyaltyStats = LoyaltyPoint::with('customer')
            ->orderByDesc('points')
            ->get();

        // 2. Total points distributed
        $totalPointsDistributed = LoyaltyHistory::where('points_change', '>', 0)->sum('points_change');
        $totalPointsRedeemed = abs(LoyaltyHistory::where('points_change', '<', 0)->sum('points_change'));

        // 3. [UPDATED] Recent Activities with Filtering
        $activityQuery = LoyaltyHistory::with('customer')->orderByDesc('created_at');

        if ($request->filled('activity_type') && $request->activity_type != 'all') {
            $type = $request->activity_type;
            if ($type == 'earned') {
                $activityQuery->where('points_change', '>', 0);
            } elseif ($type == 'redeemed') {
                $activityQuery->where('points_change', '<', 0);
            } elseif ($type == 'rental') {
                $activityQuery->where('reason', 'LIKE', '%Rental Reward%');
            } elseif ($type == 'merchant') {
                $activityQuery->where('reason', 'LIKE', '%Redeemed%Voucher%');
            }
        }
        
        $recentActivities = $activityQuery->take(50)->get();

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

        // 7. [NEW] Manage Rewards Data
        $manageRewards = Reward::all();

        return view('staff.loyalty.index', compact(
            'loyaltyStats', 'totalPointsDistributed', 'totalPointsRedeemed',
            'recentActivities', 'rentalVouchers', 'merchantVouchers', 
            'tierBreakdown', 'topPerformers', 'manageRewards'
        ));
    }

    // --- STAFF: MANAGE REWARDS (CRUD) [NEW] ---
    
    // Create Reward
    public function staffStoreReward(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'offer' => 'required',
            'points' => 'required|integer',
            'code_prefix' => 'required|max:10',
        ]);

        Reward::create([
            'name' => $request->name,
            'offer_description' => $request->offer,
            'points_required' => $request->points,
            'code_prefix' => strtoupper($request->code_prefix),
            'validity_months' => $request->validity ?? 3,
            'icon_class' => $request->icon ?? 'fa-utensils',
            'color_class' => $request->color ?? 'bg-gray-600/20 border-gray-500/30',
            'is_active' => true
        ]);

        return back()->with('success', 'Reward added to Customer Site successfully!');
    }

    // Update Reward
    public function staffUpdateReward(Request $request, $id)
    {
        $reward = Reward::findOrFail($id);
        $reward->update([
            'name' => $request->name,
            'offer_description' => $request->offer,
            'points_required' => $request->points,
            'discount_percent' => $request->discount_percent, // <--- Tambah ini
            'is_active' => $request->has('is_active') ? true : false
        ]);
        return back()->with('success', 'Reward Updated');
    }

    // Delete Reward
    public function staffDeleteReward($id)
    {
        Reward::destroy($id);
        return back()->with('success', 'Reward Deleted');
    }


    // --- STAFF: STORE NEW VOUCHER [UPDATED WITH TIME] ---
    public function staffStoreVoucher(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:vouchers,voucherCode',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:Rental Discount,Merchant Reward',
            'valid_from' => 'required', // Boleh terima datetime string
            'valid_until' => 'required',
            'description' => 'nullable|string|max:255',
        ]);

        Voucher::create([
            'voucherCode' => $request->code,
            'code' => $request->code,
            'voucherAmount' => $request->amount,
            'voucherType' => $request->type,
            // Carbon::parse akan handle datetime-local input
            'validFrom' => Carbon::parse($request->valid_from), 
            'validUntil' => Carbon::parse($request->valid_until),
            'conditions' => $request->description,
            'isUsed' => false,
            'status' => 'active'
        ]);

        return back()->with('success', 'Voucher created successfully with exact time!');
    }

    // --- STAFF: VIEW CUSTOMER LOYALTY DETAILS ---
    public function staffShowCustomer($customerId)
    {
        $loyalty = LoyaltyPoint::where('user_id', $customerId)->firstOrFail();
        $customer = $loyalty->customer;

        $history = LoyaltyHistory::where('user_id', $customerId)
            ->orderByDesc('created_at')
            ->get();

        $vouchers = Voucher::where('customerID', $customerId)
            ->orderByDesc('created_at')
            ->get();

        // --- TAMBAHAN: LOGIC LOYALTY ROAD PROGRESS ---
        $allBookings = Booking::where('customerID', $customerId)
            ->where('bookingStatus', 'Completed')
            ->get();

        $bookingCount = $allBookings->count(); // Total semua booking

        // Kira booking yang layak (> 9 jam)
        $qualifiedCount = $allBookings->filter(function ($b) {
            $start = Carbon::parse($b->originalDate . ' ' . $b->bookingTime);
            $end = Carbon::parse($b->returnDate . ' ' . $b->returnTime);
            return $start->diffInHours($end) > 9;
        })->count();

        // Kira posisi dalam cycle (1-12)
        $currentInCycle = $qualifiedCount % 12;
        
        // Kira Total Spent
        $totalSpent = Booking::where('customerID', $customerId)
            ->where('bookingStatus', 'Completed')
            ->sum('totalCost');

        return view('staff.loyalty.show-customer', compact(
            'loyalty', 'customer', 'history', 'vouchers', 
            'bookingCount', 'totalSpent', 'currentInCycle', 'qualifiedCount'
        ));
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
            'validFrom' => $voucher->validFrom, // Ini akan return string date/time
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
            'valid_from' => 'required',
            'valid_until' => 'required',
            'description' => 'nullable|string|max:255',
        ]);

        $voucher = Voucher::findOrFail($voucherId);
        $voucher->update([
            'voucherCode' => $request->code,
            'code' => $request->code,
            'voucherAmount' => $request->amount,
            'voucherType' => $request->type,
            'validFrom' => Carbon::parse($request->valid_from),
            'validUntil' => Carbon::parse($request->valid_until),
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

    // =========================================================================
    // SYSTEM HELPERS (Background Logic)
    // =========================================================================

    // --- HELPER: ISSUE RENTAL VOUCHER (Dynamic) ---
    private function issueRentalVoucher($userId, $reward) {
        
        $percent = $reward->discount_percent;
        $codePrefix = $reward->code_prefix;
        $desc = $reward->offer_description;

        Voucher::create([
            'customerID' => $userId,
            'user_id' => $userId,
            'voucherCode' => $codePrefix . '-' . strtoupper(Str::random(6)),
            'code' => $codePrefix . '-' . strtoupper(Str::random(6)),
            'voucherAmount' => 0, 
            'discount_percent' => $percent,
            'voucherType' => 'Rental Discount',
            'redeem_place' => 'HASTA Platform',
            'validFrom' => now(),
            'validUntil' => now()->addMonths($reward->validity_months), 
            'conditions' => $desc . ". Valid Mon-Thu only.",
            'isUsed' => false,
            'status' => 'active'
        ]);
    }

    // --- BOOKING COMPLETED ---
    public function bookingCompleted($bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        $userId = $booking->customerID;

        // Prevent Duplicate
        if (LoyaltyHistory::where('user_id', $userId)->where('reason', 'like', "%Booking #$bookingId%")->exists()) {
            return false;
        }

        // 1. Give Points
        $pointsEarned = (int) $booking->totalCost;
        $loyalty = LoyaltyPoint::firstOrCreate(
            ['user_id' => $userId],
            ['points' => 0, 'tier' => 'Bronze', 'rental_bookings_count' => 0]
        );
        $loyalty->points += $pointsEarned;
        $this->updateTier($loyalty);
        $loyalty->save();

        // 2. Log History
        LoyaltyHistory::create([
            'user_id' => $userId,
            'points_change' => $pointsEarned,
            'reason' => "Rental Reward (Booking #{$booking->bookingID})"
        ]);

        // 3. Loyalty Road Logic (Dynamic from DB)
        $allBookings = Booking::where('customerID', $userId)
            ->where('bookingStatus', 'Completed')
            ->get();

        $qualifiedCount = $allBookings->filter(function ($b) {
            $start = \Carbon\Carbon::parse($b->originalDate . ' ' . $b->bookingTime);
            $end = \Carbon\Carbon::parse($b->returnDate . ' ' . $b->returnTime);
            return $start->diffInHours($end) > 9;
        })->count();

        if ($qualifiedCount > 0) {
            // Cari posisi dalam cycle (1-12)
            // Jika count = 12, modulo adalah 0, kita set jadi 12
            $positionInCycle = $qualifiedCount % 12; 
            if ($positionInCycle == 0) $positionInCycle = 12;

            // Cari Reward dalam Database yang match step ini
            $milestoneReward = Reward::where('category', 'Milestone')
                ->where('milestone_step', $positionInCycle)
                ->where('is_active', true)
                ->first();

            if ($milestoneReward) {
                $this->issueRentalVoucher($userId, $milestoneReward);
            }
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
}