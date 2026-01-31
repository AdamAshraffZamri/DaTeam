<?php

namespace App\Http\Controllers;

use App\Models\LoyaltyPoint;
use App\Models\Voucher;
use App\Models\Booking;
use App\Models\LoyaltyHistory;
use App\Models\Reward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * LoyaltyController
 * 
 * Manages customer loyalty program including points earning, redemption, rewards, and tier progression.
 * Handles both customer-side dashboard and staff-side reward administration.
 * 
 * Key Features:
 * - Loyalty points tracking and calculation
 * - Tier management (Bronze, Silver, Gold, etc.)
 * - Milestone-based rewards (every 12 qualified bookings)
 * - Voucher generation and management
 * - Reward redemption and history
 * - Leaderboard/rankings display
 * - Activity tracking (points earned/redeemed)
 * - Staff reward administration and creation
 * 
 * Database Constraints:
 * - points: integer, non-negative
 * - tier: max 50 characters (Bronze, Silver, Gold, Platinum)
 * - description: max 150 characters (reward/voucher descriptions)
 * - voucherCode, code: max 50 characters
 * - voucherAmount: decimal(10,2)
 * - discount_percent: decimal(5,2) - percentage discount value
 * 
 * Loyalty Mechanics:
 * 1. Points Earning: 1 point per booking over 9 hours
 * 2. Milestone Rewards: Every 12 qualified bookings triggers a reward
 * 3. Tier Progression: Based on total points accumulated
 * 4. Voucher Redemption: Convert points to vouchers or discounts
 * 5. Leaderboard: Top 10 customers by points
 * 
 * Authentication:
 * - web guard: Customer loyalty dashboard
 * - staff guard: Reward administration (admin only)
 * 
 * Dependencies:
 * - LoyaltyPoint model: Customer loyalty records
 * - Reward model: Reward definitions and catalog
 * - Voucher model: Voucher management
 * - Booking model: Points calculation basis
 * - Carbon: Date calculations for voucher validity
 */
class LoyaltyController extends Controller
{
    /**
     * index()
     * 
     * Display customer loyalty dashboard with points, tier, rewards, and activity.
     * Shows loyalty progress, available and past vouchers, leaderboard, and upcoming rewards.
     * 
     * Dashboard Sections:
     * 1. Loyalty Status: Current points, tier, and progression to next tier
     * 2. Rewards Progress: Milestone tracking (12-booking cycle), next reward preview
     * 3. Vouchers: Active/usable vouchers and redemption history
     * 4. Leaderboard: Top 10 customers by points and user's current rank
     * 5. Activity: Recent points transactions (earned/redeemed) with filtering
     * 
     * Filters (Activity Section):
     * - activity_type: Filter by earned, redeemed, or all
     * - date: Filter by specific date
     * 
     * @param Request $request The HTTP request containing filter parameters
     * @return \Illuminate\View\View The loyalty dashboard view with all metrics
     */
    public function index(Request $request)
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

        // 3. Get Active Vouchers [FIXED]
    $vouchers = Voucher::where('customerID', $userId) // Guna customerID sahaja
        ->where('status', 'active') 
        ->where('isUsed', false)
        ->whereDate('validUntil', '>=', now())
        ->get();

    // 4. Get Past Vouchers (History) [FIXED]
    $pastVouchers = Voucher::where('customerID', $userId) // Guna customerID sahaja
        ->where(function($q) {
            $q->where('isUsed', true)
              ->orWhereDate('validUntil', '<', now());
        })
        ->orderBy('updated_at', 'desc')
        ->get();

        // 5. PROGRESS BAR LOGIC (Loyalty Road)
        $allBookings = Booking::where('customerID', $userId)
            ->where('bookingStatus', 'Completed')
            ->get();

        // Filter: Hanya kira booking yang tempoh > 9 jam
        $qualifiedBookingsCount = $allBookings->filter(function ($booking) {
            $start = Carbon::parse($booking->originalDate . ' ' . $booking->bookingTime);
            $end = Carbon::parse($booking->returnDate . ' ' . $booking->returnTime);
            return $start->diffInHours($end) > 9;
        })->count();

        // Logic Cycle 12 Steps
        $cycleSize = 12;
        $currentInCycle = $qualifiedBookingsCount % $cycleSize; 
        
        // ✅ INI BETUL: DINAMIK DARI DB
        // Cari reward milestone seterusnya yang step dia lagi besar dari current count
        $nextMilestoneData = Reward::where('category', 'Milestone')
            ->where('milestone_step', '>', $currentInCycle)
            ->where('is_active', true)
            ->orderBy('milestone_step', 'asc')
            ->first();

        if ($nextMilestoneData) {
            $targetStep = $nextMilestoneData->milestone_step;
            $nextReward = $nextMilestoneData->name; // Atau $nextMilestoneData->offer_description
        } else {
            // Kalau dah lepas semua level (cth: dah lepas 12), pusing balik ke reward pertama
            $firstReward = Reward::where('category', 'Milestone')
                ->where('is_active', true)
                ->orderBy('milestone_step', 'asc')
                ->first();
                
            $targetStep = $cycleSize; // Atau logic pusingan seterusnya
            $nextReward = $firstReward ? $firstReward->name : "Max Level Reached";
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
        $rewards = Reward::where('is_active', true)
        ->where('category', '!=', 'Milestone')
        ->get();

        $query = LoyaltyHistory::with('customer')->latest();

        // Filter by Type
        if ($request->activity_type && $request->activity_type != 'all') {
            if ($request->activity_type == 'earned') {
                $query->where('points_change', '>', 0);
            } elseif ($request->activity_type == 'redeemed') {
                $query->where('points_change', '<', 0);
            }
            // Tambah logic lain jika ada column 'type' spesifik
        }

        // Filter by Date
        if ($request->date) {
            $query->whereDate('created_at', $request->date);
        }

    $recentActivities = $query->take(20)->get(); // Atau ->paginate(10);

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
            'pointsRedeemed',
            'recentActivities'
        ));
        
    }


    // --- LOGIC: REDEEM REWARD (Non-Rental Vouchers) [FIXED] --- wildan
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

        if ($reward->points_required <= 0 || $reward->category == 'Milestone') {
            return response()->json(['success' => false, 'message' => 'This is a Milestone Reward and cannot be redeemed using points.']);
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

        // 5. Generate Voucher [FIXED]
        // Guna 'voucherCode' dan buang 'user_id' & 'code' 
        $uniqueCode = $reward->code_prefix . rand(1000,9999) . Str::upper(Str::random(3)); 

        Voucher::create([
            'customerID' => $userId, // Guna customerID sahaja
            // 'user_id' => $userId, // ERROR: Column ni tak wujud, buang!
            'voucherCode' => $uniqueCode, // Guna nama column yang betul
            // 'code' => $uniqueCode, // ERROR: Column ni tak wujud, buang!
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

    // --- CUSTOMER SELF-REDEEM (Guna Depan Kaunter) [FIXED] --- wildan
    public function useVoucherNow(Request $request)
    {
        $userId = Auth::id();
        $code = $request->code;

        // [FIXED] Cari guna 'voucherCode' dan 'customerID' sahaja
        $voucher = Voucher::where('voucherCode', $code) 
            ->where('customerID', $userId) // Buang check 'user_id'
            ->first();

        if (!$voucher) {
            return response()->json(['success' => false, 'message' => 'Voucher tidak dijumpai atau bukan milik anda.']);
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

        if ($request->filled('date')) {
            $activityQuery->whereDate('created_at', $request->date);
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
        //$manageRewards = Reward::all();
        $manageRewards = Reward::orderBy('category')->orderBy('milestone_step', 'asc')->get();
        
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
        //simple validation
        $request->validate([
            'name' => 'required|max:100',
            'offer' => 'required|max:150',
            'points' => $request->filled('milestone_step') ? 'nullable' : 'required|integer',
            'code_prefix' => 'required|max:10',
        ]);

        $isMilestone = $request->filled('milestone_step');
        $category = $isMilestone ? 'Milestone' : 'Food';
        $points = $isMilestone ? 0 : ($request->points ?? 0); // Kalau Milestone, Points = 0

        $rewardColors = [
                'bg-gradient-to-r from-purple-600/30 to-pink-600/30 border-purple-500/50',
                'bg-gradient-to-r from-blue-600/30 to-cyan-600/30 border-blue-500/50',
                'bg-gradient-to-r from-green-600/30 to-teal-600/30 border-green-500/50',
                'bg-gradient-to-r from-yellow-600/30 to-orange-600/30 border-yellow-500/50',
                'bg-gradient-to-r from-red-600/30 to-pink-600/30 border-red-500/50',
            ];

        Reward::create([
            'name' => $request->name,
            'offer_description' => $request->offer,
            'points_required' => $points,
            'code_prefix' => strtoupper($request->code_prefix),
            'validity_months' => 3, // Default 3 bulan
            'category' => $category,
            'milestone_step' => $request->milestone_step ?? null,
            'discount_percent' => $request->discount_percent ?? 0,
            'is_active' => true
        ]);

        return back()->with('success', 'Reward added to Customer Site successfully!');
    }

    // Update Reward
    public function staffUpdateReward(Request $request, $id)
    {
        $reward = Reward::findOrFail($id);

        
        // Kalau reward ni memang Milestone atau staff masukkan step baru, set jadi Milestone
        $isMilestone = ($reward->category == 'Milestone' && $request->filled('milestone_step')) || $request->filled('milestone_step');
        $category = $isMilestone ? 'Milestone' : 'Food';
        $points = $isMilestone ? 0 : ($request->points ?? 0); // Paksa 0 jika Milestone

        $reward->update([
            'name' => $request->name,
            'offer_description' => $request->offer,
            'points_required' => $request->points,
            'category' => $category,
            'milestone_step' => $request->milestone_step,
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
            'description' => 'nullable|string|max:150',
        ]);

        // 1. Tentukan masuk column mana (RM atau %)
        $voucherAmount = 0;
        $discountPercent = 0;

        if ($request->type == 'Rental Discount') {
            // Kalau Rental, kita anggap input tu adalah % (Contoh: 20 = 20%)
            $discountPercent = $request->amount;
        } else {
            // Kalau Merchant, kita anggap input tu adalah RM/Value dummy
            $voucherAmount = $request->amount;
        }

        Voucher::create([
            'voucherCode' => strtoupper($request->code),
            'code' => strtoupper($request->code),
            'voucherAmount' => $voucherAmount,      // Simpan RM (jika ada)
            'discount_percent' => $discountPercent, // Simpan % (jika rental)
            'voucherType' => $request->type,
            'validFrom' => Carbon::parse($request->valid_from),
            'validUntil' => Carbon::parse($request->valid_until),
            'conditions' => $request->description,
            'isUsed' => false,
            'status' => 'active'
        ]);

        return back()->with('success', ' Manual Voucher created successfully with exact time!');
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
            'description' => 'nullable|string|max:150',
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
        
        $codePrefix = $reward->code_prefix;
        $desc = $reward->offer_description;
        
        // DEFAULT VALUES
        $voucherType = 'Rental Discount';
        $percent = $reward->discount_percent; 
        
        // --- [FIX] LOGIC IMPROVED ---
        // Detect Free Half Day via Name OR if it is the 12th Step (Cycle Completion)
        if (stripos($reward->name, 'Half Day') !== false || $reward->milestone_step == 12) {
            $voucherType = 'Free Half Day'; 
            $percent = 0; // Set to 0 because logic calculates hours dynamically
            
            // Ensure description mentions it if missing
            if (stripos($desc, 'Half Day') === false) {
                $desc .= " (Free Half Day Reward)";
            }
        }

        Voucher::create([
            'customerID' => $userId,
            'voucherCode' => $codePrefix . '-' . strtoupper(Str::random(6)),
            'voucherAmount' => 0, 
            'discount_percent' => $percent,
            'voucherType' => $voucherType, // Now correctly sets 'Free Half Day'
            'redeem_place' => 'HASTA Platform',
            'validFrom' => now(),
            'validUntil' => now()->addMonths($reward->validity_months), 
            'conditions' => $desc,
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
        //CHECK MILESTONE (100% DYNAMIC)
        //Kira total booking yang valid (>9 jam)
        $allBookings = Booking::where('customerID', $userId)
            ->where('bookingStatus', 'Completed')
            ->get();

        $qualifiedCount = $allBookings->filter(function ($b) {
            $start = Carbon::parse($b->originalDate . ' ' . $b->bookingTime);
            $end = Carbon::parse($b->returnDate . ' ' . $b->returnTime);
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
