<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;
use Carbon\Carbon;

class VoucherController extends Controller
{
    public function apply(Request $request)
    {
        $code = $request->input('code');
        $currentTotal = $request->input('total_amount');

        // 1. Find the Voucher
        $voucher = Voucher::where('voucherCode', $code)->first();

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

        // 5. Calculate Discount (Assuming Fixed Amount based on your Schema)
        $discountAmount = $voucher->voucherAmount;
        $newTotal = $currentTotal - $discountAmount;

        // Ensure total doesn't go below 0
        if ($newTotal < 0) { 
            $newTotal = 0; 
        }

        return response()->json([
            'success' => true,
            'message' => 'Voucher applied successfully!',
            'discount_amount' => number_format($discountAmount, 2),
            'new_total' => number_format($newTotal, 2),
            'voucher_id' => $voucher->voucherID // Send back ID to store in hidden input
        ]);
    }
}