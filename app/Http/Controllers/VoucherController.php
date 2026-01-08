<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Vehicle;

class VoucherController extends Controller
{

    // File: app/Http/Controllers/VoucherController.php

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
            ->where('voucherType', 'Rental Discount')
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

                if (str_contains($conditionText, 'FREE HALF DAY')) {
                    // Kalau voucher ni jenis Free Half Day
                    $displayTitle = "FREE HALF DAY";
                } 
                elseif ($discountPercent > 0) {
                    // Kalau voucher percent biasa (20%, 50%)
                    $displayTitle = $discountPercent . "% OFF";
                } 
                else {
                    // Kalau voucher tolak duit (RM5 OFF)
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