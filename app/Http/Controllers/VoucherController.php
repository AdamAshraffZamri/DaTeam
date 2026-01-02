<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class VoucherController extends Controller
{
    public function apply(Request $request)
    {
        $code = $request->input('code');
        $currentTotal = $request->input('total_amount');

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

    // Get available vouchers for logged-in customer
    public function getAvailableVouchers()
    {
        $userId = Auth::id();
        
        if (!$userId) {
            return response()->json([]);
        }

        $vouchers = Voucher::where(function($query) use ($userId) {
                $query->where('user_id', $userId)->orWhere('customerID', $userId);
            })
            ->where('isUsed', false)
            ->where('voucherType', 'Rental Discount') // Only rental discount vouchers
            ->whereDate('validUntil', '>=', now())
            ->whereDate('validFrom', '<=', now())
            ->get()
            ->map(function($voucher) {
                $code = $voucher->code ?? $voucher->voucherCode ?? 'N/A';
                $amount = $voucher->voucherAmount ?? 0;
                $discountPercent = $voucher->discount_percent ?? 0;
                $type = $voucher->voucherType ?? 'Rental Discount';
                $expires = $voucher->validUntil ?? $voucher->expires_at;
                
                return [
                    'id' => $voucher->voucherID ?? $voucher->id ?? 0,
                    'code' => $code,
                    'amount' => $amount,
                    'discount_percent' => $discountPercent,
                    'type' => $type,
                    'expires' => $expires ? $expires->format('d M Y') : 'No expiry',
                ];
            })->values();

        return response()->json($vouchers);
    }
}