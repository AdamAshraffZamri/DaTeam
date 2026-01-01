<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestRentalReward extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-rental-reward';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Rental Reward Service...');
        
        // Test with both bookings
        $bookingIds = [1, 2];
        
        foreach ($bookingIds as $bookingId) {
            $booking = \App\Models\Booking::find($bookingId);
            if (!$booking) {
                $this->error("Booking $bookingId not found!");
                continue;
            }
            
            $this->info("\n--- Processing Booking #{$bookingId} ---");
            $this->line("  Original Date: {$booking->originalDate}");
            $this->line("  Booking Time: {$booking->bookingTime}");
            $this->line("  Return Date: {$booking->returnDate}");
            $this->line("  Return Time: {$booking->returnTime}");
            
            // Calculate hours
            $service = new \App\Services\RentalRewardService();
            $hours = $service->calculateRentalHours($booking);
            $this->line("  Calculated Hours: {$hours}");
            
            // Process completion
            $this->info("  Processing completion...");
            $service->processBookingCompletion($booking);
        }
        
        // Check final loyalty points
        $loyalty = \App\Models\LoyaltyPoint::where('user_id', 1)->first();
        $this->info("\n=== Final Loyalty Points for customer 1 ===");
        $this->line("  Points: {$loyalty->points}");
        $this->line("  Tier: {$loyalty->tier}");
        $this->line("  Rental Bookings Count: {$loyalty->rental_bookings_count}");
        
        // Check vouchers
        $vouchers = \App\Models\Voucher::where('user_id', 1)->where('voucherType', 'Rental Discount')->get();
        $this->info("\nRental Discount Vouchers for customer 1:");
        $this->line("  Count: {$vouchers->count()}");
        foreach ($vouchers as $voucher) {
            $this->line("    - Code: {$voucher->code}, Amount: {$voucher->voucherAmount}%, Status: {$voucher->status}");
        }
    }
}
