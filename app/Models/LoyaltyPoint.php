<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * LoyaltyPoint Model
 * 
 * Tracks customer loyalty program membership and progression.
 * Records accumulated points, tier level, and booking history for rewards.
 * 
 * Key Features:
 * - Points balance tracking
 * - Tier progression (Bronze → Silver → Gold → Platinum)
 * - Rental booking count (for milestone rewards)
 * - Leaderboard ranking
 * - Tier benefits and rewards eligibility
 * 
 * Tier System:
 * - Bronze: Initial tier, 0-99 points
 *   * Benefit: 1 point per booking
 *   * Access: Basic loyalty features
 * 
 * - Silver: 100-499 points
 *   * Benefit: 1.25x points multiplier
 *   * Access: Exclusive vouchers
 * 
 * - Gold: 500-999 points
 *   * Benefit: 1.5x points multiplier
 *   * Access: Premium vouchers, priority support
 * 
 * - Platinum: 1000+ points
 *   * Benefit: 2x points multiplier
 *   * Access: VIP benefits, free upgrades
 * 
 * Points Mechanics:
 * - Earned: 1 point per qualified booking (9+ hours)
 * - Redeemed: Via vouchers and rewards
 * - Milestone Bonus: Extra reward every 12 qualified bookings
 * - No point decay or expiration
 * - Points not refundable (non-transferable)
 * 
 * Database Constraints:
 * - points: integer, non-negative, no upper limit
 * - tier: max 50 characters (Bronze, Silver, Gold, Platinum)
 * - rental_bookings_count: integer, count of qualifying bookings
 * - user_id: Foreign key to customers
 * 
 * Relationships:
 * - belongsTo: Customer (user_id → customerID)
 * - hasMany: LoyaltyHistory (points transaction log)
 * - hasMany: Vouchers (redeemed vouchers)
 * - hasMany: Rewards (earned rewards)
 * 
 * Loyalty Workflow:
 * 1. Customer completes booking (9+ hours)
 * 2. Booking marked as completed
 * 3. System calculates points earned
 * 4. Points added to LoyaltyPoint record
 * 5. Tier automatically updated based on total points
 * 6. Milestone check: Every 12th booking triggers reward
 * 7. Reward notification sent to customer
 * 8. Voucher generated for redemption
 * 9. Customer can apply voucher to next booking
 * 
 * Ranking Calculation:
 * - Ranked by points descending
 * - Top 10 displayed in leaderboard
 * - User's rank calculated at query time
 * 
 * @property int $id Primary key
 * @property int $user_id Foreign key to customers
 * @property int $points Total accumulated points
 * @property string $tier Current tier level (max 50)
 * @property int $rental_bookings_count Count of qualified bookings
 * @property timestamp $created_at Record creation date
 * @property timestamp $updated_at Last update date
 */
class LoyaltyPoint extends Model
{
    /**
     * The attributes that are mass assignable.
     * 
     * @var array
     */
    protected $fillable = ['user_id', 'points', 'tier', 'rental_bookings_count'];

    /**
     * Get the customer that owns this loyalty record.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer() {
        return $this->belongsTo(Customer::class, 'user_id', 'customerID');
    }
}

