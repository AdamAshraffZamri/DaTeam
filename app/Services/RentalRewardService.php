<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Voucher;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyHistory;
use Carbon\Carbon;

class RentalRewardService
{
    /**
     * Calculate rental hours from booking
     */
    public function calculateRentalHours(Booking $booking): int
    {
        try {
            if (!$booking->originalDate || !$booking->bookingTime || !$booking->returnDate || !$booking->returnTime) {
                \Log::warning("Missing booking dates for booking {$booking->bookingID}");
                return 0;
            }
            
            // Parse dates - handle both date and datetime formats
            $startDate = $booking->originalDate instanceof \DateTime 
                ? $booking->originalDate 
                : Carbon::parse($booking->originalDate);
            
            $startTime = is_string($booking->bookingTime) 
                ? Carbon::createFromFormat('H:i:s', $booking->bookingTime)
                : $booking->bookingTime;
            
            $endDate = $booking->returnDate instanceof \DateTime 
                ? $booking->returnDate 
                : Carbon::parse($booking->returnDate);
            
            $endTime = is_string($booking->returnTime) 
                ? Carbon::createFromFormat('H:i:s', $booking->returnTime)
                : $booking->returnTime;
            
            // Combine date + time - IMPORTANT: use UTC to avoid timezone issues
            $start = Carbon::create(
                $startDate->year, $startDate->month, $startDate->day,
                $startTime->hour, $startTime->minute, $startTime->second,
                'UTC'
            );
            
            $end = Carbon::create(
                $endDate->year, $endDate->month, $endDate->day,
                $endTime->hour, $endTime->minute, $endTime->second,
                'UTC'
            );
            
            // Use absolute difference to handle any comparison issues
            $hours = (int) abs($end->diffInHours($start));
            
            \Log::info("Rental hours calculated for booking {$booking->bookingID}: {$hours}h (from {$start} to {$end})");
            
            return $hours;
        } catch (\Exception $e) {
            \Log::warning("Error calculating rental hours for booking {$booking->bookingID}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Process booking completion & reward tracking
     */
    public function processBookingCompletion(Booking $booking): bool
    {
        $userId = $booking->customerID;
        
        // Get or create loyalty point record
        $loyalty = LoyaltyPoint::firstOrCreate(
            ['user_id' => $userId],
            [
                'points' => 0,
                'tier' => 'Bronze',
                'rental_bookings_count' => 0
            ]
        );

        // Calculate rental hours
        $rentalHours = $this->calculateRentalHours($booking);
        \Log::info("Processing booking {$booking->bookingID} for customer {$userId}: {$rentalHours} hours, current count: {$loyalty->rental_bookings_count}");

        // If booking >= 9 hours, increment counter & check for reward
        if ($rentalHours >= 9) {
            $loyalty->rental_bookings_count = ($loyalty->rental_bookings_count ?? 0) + 1;
            \Log::info("Booking {$booking->bookingID} qualified (>= 9h). New count: {$loyalty->rental_bookings_count}");

            // Every 3 bookings = 1 voucher
            if ($loyalty->rental_bookings_count >= 3) {
                \Log::info("Issuing voucher for customer {$userId} - count reached 3");
                $this->issueRentalRewardVoucher($userId);
                // Reset counter to 1 for next cycle
                $loyalty->rental_bookings_count = 1;
            }
        } else {
            \Log::info("Booking {$booking->bookingID} did NOT qualify (only {$rentalHours}h)");
        }

        $loyalty->save();

        return true;
    }

    /**
     * Issue rental reward voucher
     */
    public function issueRentalRewardVoucher(int $userId): Voucher
    {
        $code = $this->generateVoucherCode();

        $voucher = Voucher::create([
            'user_id' => $userId,
            'voucherCode' => $code,
            'code' => $code,
            'voucherAmount' => 30,
            'discount_percent' => 30,
            'voucherType' => 'Rental Discount',
            'validFrom' => Carbon::now(),
            'validUntil' => Carbon::now()->addDays(30),
            'conditions' => 'Reward for completing 3 bookings of 9+ hours',
            'isUsed' => false,
            'status' => 'active'
        ]);

        // Log the reward
        LoyaltyHistory::create([
            'user_id' => $userId,
            'points_change' => 0, // Voucher issuance doesn't add/subtract points directly
            'action' => 'earned',
            'reason' => 'Rental Discount Voucher Earned',
            'description' => "30% OFF voucher (Code: {$code}) earned from completing 3 rental bookings of 9+ hours"
        ]);

        return $voucher;
    }

    /**
     * Generate unique voucher code
     */
    private function generateVoucherCode(): string
    {
        do {
            $code = 'RENTAL' . strtoupper(substr(uniqid(), -6));
        } while (Voucher::where('code', $code)->exists());

        return $code;
    }
}
