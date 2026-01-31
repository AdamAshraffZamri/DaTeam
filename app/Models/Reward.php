<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Reward Model
 * 
 * Defines available rewards in the loyalty program.
 * Manages reward catalog, eligibility, and distribution rules.
 * 
 * Key Features:
 * - Reward type management (Milestone, Food, Service, etc.)
 * - Points requirement tracking
 * - Milestone-based reward progression
 * - Validity period management
 * - Active/inactive status control
 * - Visual styling (icon, color) for UI display
 * - Discount percentage for discount-based rewards
 * 
 * Reward Categories:
 * 1. Milestone Rewards: Earned after every N qualified bookings
 *    * Triggered automatically when rental_bookings_count % 12 = milestone_step
 *    * Examples: 3 bookings = Free Half Day, 6 bookings = 20% Discount
 * 
 * 2. Food Rewards: Vouchers for food/beverage partners
 *    * Redeemed at partner restaurants
 *    * May have points requirement
 * 
 * 3. Service Rewards: Additional services or upgrades
 *    * Free upgrade, Priority support, Free insurance, etc.
 * 
 * 4. Cash Back: Percentage discount on future bookings
 *    * Applied directly to rental cost
 * 
 * Database Constraints:
 * - name: Reward name/title
 * - offer_description: Detailed reward description
 * - points_required: integer, points needed to redeem
 * - code_prefix: Prefix for voucher code generation
 * - validity_months: Duration of reward validity (in months)
 * - category: max 50 characters (Milestone, Food, Service, CashBack, etc.)
 * - icon_class: CSS class for icon display (Bootstrap/Font Awesome)
 * - color_class: CSS class for styling (e.g., 'bg-success', 'text-danger')
 * - is_active: Boolean to enable/disable reward
 * - milestone_step: integer, booking count target (e.g., 3, 6, 9, 12)
 * - discount_percent: integer, discount percentage for discount rewards
 * 
 * Milestone System:
 * - Cycle: 12 qualified bookings = one complete cycle
 * - Step Points: 3, 6, 9, 12 bookings within each cycle
 * - Example Progression:
 *   * 3 bookings: Reward 1
 *   * 6 bookings: Reward 2
 *   * 9 bookings: Reward 3
 *   * 12 bookings: Reward 4 (cycle resets)
 *   * 15 bookings: Reward 1 again (starts new cycle)
 * 
 * Workflow:
 * 1. Admin creates reward with all details
 * 2. Reward set to is_active = true
 * 3. System checks completed bookings
 * 4. When customer hits milestone_step booking count → Reward triggered
 * 5. Voucher automatically generated
 * 6. Customer notified
 * 7. Voucher appears in loyalty dashboard
 * 8. Customer can apply during booking
 * 9. Discount/reward applied to rental cost
 * 10. Loyalty history recorded
 * 
 * Visual Display:
 * - icon_class: Determines icon (e.g., 'fas fa-gift' for Font Awesome)
 * - color_class: Determines styling (e.g., 'badge-success', 'text-primary')
 * - Used in UI for reward cards, leaderboard, milestone displays
 * 
 * Configuration Example:
 * ```
 * Reward 1:
 *   - name: "Free Half Day"
 *   - category: "Milestone"
 *   - milestone_step: 3
 *   - offer_description: "12 hours free rental"
 *   - validity_months: 3
 *   - icon_class: "fas fa-gift"
 *   - color_class: "badge-success"
 * ```
 * 
 * @property int $id Primary key
 * @property string $name Reward name/title
 * @property string $offer_description Detailed description
 * @property int $points_required Points needed to redeem
 * @property string $code_prefix Prefix for generated voucher codes
 * @property int $validity_months Duration of validity
 * @property string $category Reward type (max 50)
 * @property string $icon_class CSS icon class for display
 * @property string $color_class CSS styling class
 * @property boolean $is_active Active/inactive status
 * @property int $milestone_step Booking count target for milestone
 * @property int $discount_percent Discount percentage if applicable
 * @property timestamp $created_at Record creation date
 * @property timestamp $updated_at Last update date
 */
class Reward extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'rewards';

    /**
     * The attributes that are mass assignable.
     * 
     * @var array
     */
    protected $fillable = [
        'name', 
        'offer_description', 
        'points_required', 
        'code_prefix', 
        'validity_months', 
        'category',         // 'Milestone' atau 'Food'
        'icon_class', 
        'color_class', 
        'is_active',
        'milestone_step',   // PENTING: Target Booking (Contoh: 3, 6, 9, 10, 50...)
        'discount_percent'  // Nilai Diskaun %
    ];

    protected $casts = [
        'points_required' => 'integer',
        'validity_months' => 'integer',
        'is_active' => 'boolean',
        'milestone_step' => 'integer',
        'discount_percent' => 'integer',
    ];
}