<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Vehicle;

/**
 * VoucherController
 * 
 * Manages voucher application and retrieval for booking discounts.
 * Handles voucher validation, discount calculation, and availability checks.
 * 
 * Key Features:
 * - Voucher code validation and application
 * - Discount calculation (percentage, fixed amount, or free time)
 * - Day-based restrictions (Monday-Thursday for loyalty vouchers)
 * - Expiry date validation
 * - Usage status tracking (single-use or multi-use)
 * - Multiple discount types (Rental Discount, Free Half Day, etc.)
 * - Vehicle-specific pricing integration
 * 
 * Voucher Types:
 * 1. Rental Discount: Percentage-based or fixed amount discount
 * 2. Free Half Day: 12-hour rental free (loyalty reward)
 * 3. Seasonal Promotion: Time-limited special offers
 * 4. Referral Voucher: Special promotional codes
 * 
 * Database Constraints:
 * - voucherCode, code: max 50 characters (unique identifier)
 * - voucherType: max 50 characters
 * - voucherAmount: decimal(10,2)
 * - discount_percent: decimal(5,2) percentage discount
 * - conditions: text field for detailed terms
 * 
 * Discount Rules:
 * - Loyalty vouchers (Free Half Day): Monday-Thursday only
 * - Discount cannot exceed rental cost (after deposit deduction)
 * - 12-hour free rental calculated from vehicle hourly rates
 * - Percentage and fixed amount automatically calculated
 * 
 * Authentication:
 * - Customer authenticated (via web guard)
 * - Returns JSON for AJAX requests
 */
class VoucherController extends Controller
{

    /**
     * apply()
     * 
     * Validate and apply voucher code to current booking, calculating discount amount.
     * Performs comprehensive validation including expiry, usage, day restrictions, and discount calculation.
     * 
     * Process:
     * 1. Validate vehicle exists and load pricing data
     * 2. Find voucher by code in database
     * 3. Verify validity period (validFrom to validUntil)
     * 4. Check usage status (not already used if single-use)
     * 5. Validate day restrictions for loyalty vouchers (Mon-Thu only)
     * 6. Calculate discount based on voucher type:
     *    - Free Half Day: 12 hours from hourly_rates or base price
     *    - Percentage: Discount on rental cost (excluding deposit)
     *    - Fixed Amount: Direct deduction from rental cost
     * 7. Ensure discount doesn't exceed rental cost
     * 8. Calculate and return new total
     * 
     * Request Parameters:
     * - code: Voucher code to validate
     * - total_amount: Current booking total (RM)
     * - pickup_date: Date of vehicle pickup (format: YYYY-MM-DD)
     * - vehicle_id: Vehicle ID for pricing lookup
     * 
     * Response JSON:
     * - success: Boolean indicating validation result
     * - message: Success or error message
     * - discount_amount: Amount discounted (if successful)
     * - new_total: New total after discount (if successful)
     * - voucherID: Voucher database ID (if successful)
     * - display_title: Display name for voucher ("FREE HALF DAY", "25% OFF", "RM 100 OFF")
     * 
     * Error Cases:
     * - Invalid voucher code: "Invalid voucher code."
     * - Expired voucher: "This voucher has expired."
     * - Already used: "This voucher has already been used."
     * - Wrong day (loyalty): "Loyalty vouchers are valid Mon-Thu only."
     * - Missing vehicle: "Vehicle data missing."
     * - Missing pickup date: "Pickup date is required."
     * 
     * @param Request $request The HTTP request with voucher and booking details
     * @return \Illuminate\Http\JsonResponse JSON response with validation result and discount info
     */
    public function apply(Request $request)
{
    $code = $request->input('code');
    $currentTotal = $request->input('total_amount');
    $pickupDate = $request->input('pickup_date'); 
    $vehicleId = $request->input('vehicle_id');

    // 1. Validate Vehicle
    if ($vehicleId) {
        $vehicle = Vehicle::findOrFail($vehicleId);
    } else {
        return response()->json(['success' => false, 'message' => 'Vehicle data missing.']);
    }

    // 2. Find Voucher
    $voucher = Voucher::where('voucherCode', $code)
        ->orWhere('code', $code)
        ->first();

    if (!$voucher) {
        return response()->json(['success' => false, 'message' => 'Invalid voucher code.']);
    }

    // 3. Validate Status & Date
    $now = Carbon::now();
    if ($now->lt($voucher->validFrom) || $now->gt($voucher->validUntil)) {
        return response()->json(['success' => false, 'message' => 'This voucher has expired.']);
    }

    if ($voucher->isUsed) {
        return response()->json(['success' => false, 'message' => 'This voucher has already been used.']);
    }

    // 4. Validate Day (Isnin - Khamis sahaja untuk Loyalty)
    // Check condition FREE HALF DAY
    $conditionText = strtoupper($voucher->conditions ?? '');
    $isFreeHalfDay = str_contains($conditionText, 'FREE HALF DAY') || $voucher->voucherType == 'Free Half Day';

    if ($voucher->voucherType == 'Rental Discount' || $isFreeHalfDay) {
        if (!$pickupDate) {
            return response()->json(['success' => false, 'message' => 'Pickup date is required.']);
        }
        $pickupDay = Carbon::parse($pickupDate);
        // Block Jumaat(5), Sabtu(6), Ahad(7)
        if ($pickupDay->dayOfWeekIso > 4) {
            return response()->json(['success' => false, 'message' => 'Loyalty vouchers are valid Mon-Thu only.']);
        }
    }

    // 5. Calculate Discount
    $baseDepo = $vehicle->baseDepo ?? 0;
    $rentalOnlyCost = max(0, $currentTotal - $baseDepo); 
    $discountAmount = 0;
    $displayTitle = ""; // Variable untuk tajuk voucher

    if ($isFreeHalfDay) {
        // --- LOGIC: FREE HALF DAY ---
        $displayTitle = "FREE HALF DAY"; // Set nama kat sini
        
        try {
            $rates = $vehicle->hourly_rates;
            if (is_string($rates)) $rates = json_decode($rates, true);

            if (is_array($rates) && isset($rates['12'])) {
                $discountAmount = (float)$rates['12']; 
            } else {
                $discountAmount = ($vehicle->priceHour ?? 0) * 12;
            }
        } catch (\Exception $e) {
            $discountAmount = ($vehicle->priceHour ?? 0) * 12;
        }

    } elseif ($voucher->discount_percent > 0) {
        // --- LOGIC: PERCENTAGE ---
        $displayTitle = intval($voucher->discount_percent) . "% OFF";
        $discountAmount = ($rentalOnlyCost * $voucher->discount_percent) / 100;

    } else {
        // --- LOGIC: FIXED AMOUNT ---
        $displayTitle = "RM " . number_format($voucher->voucherAmount, 0) . " OFF";
        $discountAmount = $voucher->voucherAmount;
    }

    // Safety Limit
    if ($discountAmount > $rentalOnlyCost) {
        $discountAmount = $rentalOnlyCost;
    }

    $newTotal = $currentTotal - $discountAmount;

    // RETURN JSON (INI YANG PENTING)
    return response()->json([
        'success' => true,
        'message' => 'Voucher applied successfully!',
        'discount_amount' => number_format($discountAmount, 2),
        'new_total' => number_format($newTotal, 2),
        'voucherID' => $voucher->voucherID ?? $voucher->id,
        'display_title' => $displayTitle // <--- Frontend perlukan ini!
    ]);
}

    // Get available vouchers for logged-in customer
    public function getAvailableVouchers()
    {
        // ... (Kekalkan kod sedia ada, tiada perubahan besar diperlukan di sini) ...
        // ... Pastikan query mengambil voucher yang belum used ...
        
        $userId = Auth::id();
        
        if (!$userId) {
            return response()->json([]);
        }

        $vouchers = Voucher::where(function($query) use ($userId) {
                $query->where('user_id', $userId)->orWhere('customerID', $userId);
            })
            ->where('isUsed', false) // Pastikan belum digunakan
            ->whereIn('voucherType', ['Rental Discount', 'Free Half Day'])
            ->whereDate('validUntil', '>=', now())
            ->whereDate('validFrom', '<=', now())
            ->get()
            ->map(function($voucher) {
                
                $code = $voucher->code ?? $voucher->voucherCode ?? 'N/A';
                $amount = $voucher->voucherAmount ?? 0;
                $discountPercent = $voucher->discount_percent ?? 0;
                $type = $voucher->voucherType ?? 'Rental Discount';
                $expires = $voucher->validUntil ?? $voucher->expires_at;
                
                // --- [LOGIC BARU: TENTUKAN NAMA DISPLAY] ---
                $conditionText = strtoupper($voucher->conditions ?? '');
                $displayTitle = "";

                if ($type == 'Free Half Day' || str_contains($conditionText, 'FREE HALF DAY')) {
                    $displayTitle = "FREE HALF DAY";
                } 
                elseif ($discountPercent > 0) {
                    $displayTitle = $discountPercent . "% OFF";
                } 
                else {
                    $displayTitle = "RM" . number_format($amount, 0) . " OFF";
                }

                return [
                    'id' => $voucher->voucherID ?? $voucher->id ?? 0,
                    'code' => $code,
                    'amount' => $amount,
                    'discount_percent' => $discountPercent,
                    'type' => $type,
                    // Hantar ayat ni ke frontend
                    'display_title' => $displayTitle, 
                    'expires' => $expires ? $expires->format('d M Y') : 'No expiry',
                ];
            })->values();

        return response()->json($vouchers);
    }
}