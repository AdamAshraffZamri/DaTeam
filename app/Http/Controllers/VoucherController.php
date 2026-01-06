<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Vehicle;

class VoucherController extends Controller
{
    public function apply(Request $request)
    {
        $code = $request->input('code');
        $currentTotal = $request->input('total_amount');
        
        // Terima pickup_date dari request untuk semakan hari
        $pickupDate = $request->input('pickup_date'); 


        // Ambil ID dari request dulu
        $vehicleId = $request->input('vehicle_id');

        // Pastikan vehicle wujud
        // Gunakan try-catch atau check kalau ID wujud untuk elak error
        if ($vehicleId) {
            $vehicle = Vehicle::findOrFail($vehicleId);
        } else {
            return response()->json(['success' => false, 'message' => 'Vehicle data missing.']);
        }

        // 1. Find the Voucher
        $voucher = Voucher::where('voucherCode', $code)->orWhere('code', $code)->first();



        // 2. Validate Voucher Existence
        if (!$voucher) {
            return response()->json(['success' => false, 'message' => 'Invalid voucher code.']);
        }

        // 3. Validate Date (Start & End)
        $now = Carbon::now();
        if ($now->lt($voucher->validFrom) || $now->gt($voucher->validUntil)) {
            return response()->json(['success' => false, 'message' => 'This voucher has expired.']);
        }

        // 4. Validate Usage
        if ($voucher->isUsed) {
            return response()->json(['success' => false, 'message' => 'This voucher has already been used.']);
        }

        // 5. [BARU] Semak Syarat Hari (Isnin - Khamis Sahaja) untuk Loyalty Voucher
        // Kita anggap voucher loyalty jenis 'Rental Discount'. 
        // Anda boleh tambah check specific type jika perlu.
        if ($voucher->voucherType == 'Rental Discount') {
            if (!$pickupDate) {
                return response()->json(['success' => false, 'message' => 'Pickup date is required to validate this voucher.']);
            }

            $pickupDay = Carbon::parse($pickupDate);
            
            // Carbon: Monday=1, Thursday=4, Friday=5, Sunday=7
            // Jika hari adalah Jumaat (5), Sabtu (6), atau Ahad (7) -> BLOCK
            if ($pickupDay->dayOfWeekIso > 4) {
                return response()->json(['success' => false, 'message' => 'Loyalty vouchers are only valid for bookings on Monday - Thursday.']);
            }
        }

        // 6. Calculate Discount - EXCLUDE DEPOSIT FROM DISCOUNT CALCULATION
        $baseDepo = $vehicle->baseDepo; // Ambil harga deposit dari database
        $rentalOnlyCost = $currentTotal - $baseDepo; // Asingkan harga sewa

        $discountAmount = 0;

        // --- [MULA TAMBAH SINI] ---
        // Kita check column 'conditions' ada tak tulis FREE HALF DAY
        $conditionText = strtoupper($voucher->conditions ?? '');

        if (str_contains($conditionText, 'FREE HALF DAY')) {
            // LOGIC: TOLAK HARGA PAKEJ 12 JAM (VERSION ANTI-CRASH)
            
            $discountAmount = 0; // Default

            try {
                // 1. Ambil raw data dari database
                $rates = $vehicle->hourly_rates;

                // 2. CHECK JENIS DATA (Ini yg buat error tadi)
                // Kalau dia string (teks JSON), baru kita decode.
                if (is_string($rates)) {
                    $rates = json_decode($rates, true);
                }
                // Kalau dia dah memang array (Laravel dah cast), kita guna terus.

                // 3. Ambil rate 12 Jam
                if (is_array($rates) && isset($rates['12'])) {
                    // Tukar value jadi float/number siap2
                    $discountAmount = (float)$rates['12']; 
                } else {
                    // Fallback: Kalau tak jumpa rate 12, kira manual
                    $discountAmount = ($vehicle->priceHour ?? 0) * 12;
                }

            } catch (\Exception $e) {
                // 4. JIKA ADA ERROR PELIK, GUNA FALLBACK (JANGAN CRASH)
                // Kalau JSON rosak ke apa, dia akan masuk sini
                $discountAmount = ($vehicle->priceHour ?? 0) * 12;
            }

        } elseif ($voucher->discount_percent > 0) {
             // ... Sambung coding asal ...

        } elseif ($voucher->discount_percent > 0) { 
            // ... (sambung coding asal bawah ni) ...

        // --- [HABIS TAMBAH SINI, BAWAH NI SAMBUNG CODING ASAL (Tukar if jadi elseif)] ---
        } elseif ($voucher->discount_percent > 0) {
            // Kira % berdasarkan harga sewa sahaja
            $discountAmount = ($rentalOnlyCost * $voucher->discount_percent) / 100;
        } else {
            $discountAmount = $voucher->voucherAmount;
        }

        // Safety: Jangan bagi diskaun lebih dari harga sewa
        if ($discountAmount > $rentalOnlyCost) {
            $discountAmount = $rentalOnlyCost;
        }

        $newTotal = $currentTotal - $discountAmount;


        return response()->json([
            'success' => true,
            'message' => 'Voucher applied successfully!',
            'discount_amount' => number_format($discountAmount, 2),
            'new_total' => number_format($newTotal, 2),
            'voucher_id' => $voucher->voucherID 
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