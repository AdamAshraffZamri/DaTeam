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

        if ($reward->points_required <= 0 || $reward->category == 'Milestone') {
            return response()->json(['success' => false, 'message' => 'This is a Milestone Reward and cannot be redeemed using points. It is awarded automatically upon booking completion.']);
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
            'name' => 'required',
            'offer' => 'required',
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
            'description' => 'nullable|string|max:255',
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
        $voucherType = 'Rental Discount';

        // LOGIC KHAS: Jika reward ini adalah untuk Milestone ke-12
        // Kita paksa jadi type 'Free Half Day' supaya BookingController boleh baca
        if ($reward->milestone_step == 12) {
            $voucherType = 'Free Half Day'; 
            $percent = 0; // Percent 0 sebab kiraan dia manual (tolak 12 jam)
        }

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
        //CHECK MILESTONE (100% DYNAMIC)
        //Kira total booking yang valid (>9 jam)
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
                ->where('milestone_step', $qualifiedCount)
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