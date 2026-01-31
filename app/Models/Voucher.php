<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Voucher Model
 * 
 * Represents discount vouchers issued to customers.
 * Tracks voucher distribution, validity, usage, and redemption.
 * 
 * Key Features:
 * - Voucher code generation and management
 * - Multiple discount types (percentage, fixed amount, free time)
 * - Validity period tracking (validFrom to validUntil)
 * - Usage status (single-use or multi-use)
 * - Redemption conditions and restrictions
 * - Day-based validity (some vouchers Mon-Thu only)
 * 
 * Voucher Types:
 * 1. Rental Discount: Percentage-based discount (e.g., 20% OFF)
 * 2. Free Half Day: 12-hour free rental as loyalty reward
 * 3. Fixed Discount: Fixed amount reduction (e.g., RM 50 OFF)
 * 4. Seasonal Promotion: Time-limited special offers
 * 5. Referral Voucher: Special promotional codes for new customers
 * 
 * Database Constraints:
 * - voucherCode, code: max 50 characters (unique identifier)
 * - voucherType: max 50 characters
 * - voucherAmount: decimal(10,2) - Fixed discount amount
 * - discount_percent: decimal(5,2) - Percentage discount value
 * - conditions: text field with detailed terms
 * - validFrom, validUntil: date fields
 * - redeem_place: Location or conditions for redemption
 * 
 * Discount Logic:
 * - Percentage: rentalCost × (discount_percent / 100)
 * - Fixed Amount: Direct RM deduction from rental
 * - Free Half Day: 12-hour cost from vehicle hourly_rates
 * - Discount cannot exceed rental cost (after deposit)
 * 
 * Relationships:
 * - belongsTo: Customer (customerID)
 * - belongsTo: Reward (reward_id, if loyalty-based)
 * 
 * Usage Workflow:
 * 1. Voucher created by admin with validity period
 * 2. Issued to customer (customerID set)
 * 3. Customer applies during booking
 * 4. Validation checks: expiry, usage, day restrictions
 * 5. Discount calculated and applied
 * 6. Voucher marked as used (isUsed = true)
 * 7. Booking total reduced by discount amount
 * 
 * @property int $voucherID Primary key
 * @property int $customerID Customer ID (owner of voucher)
 * @property int $user_id Alternative customer ID field
 * @property int $reward_id Foreign key to rewards
 * @property string $voucherCode Code identifier (max 50)
 * @property string $code Alternative code field (max 50)
 * @property decimal $voucherAmount Fixed discount amount
 * @property decimal $discount_percent Percentage discount value
 * @property string $voucherType Type of voucher (max 50)
 * @property string $redeem_place Where voucher can be redeemed
 * @property date $validFrom Start date of validity
 * @property date $validUntil End date of validity
 * @property date $expires_at Expiry date (alternative field)
 * @property string $conditions Detailed terms and conditions
 * @property string $terms_conditions Alternative terms field
 * @property boolean $isUsed Usage status (true if used)
 * @property string $status Status field (active, expired, used)
 */
class Voucher extends Model
{
    /**
     * The primary key for the model.
     * 
     * @var string
     */
    protected $primaryKey = 'voucherID';
    
    /**
     * The attributes that are mass assignable.
     * 
     * @var array
     */
    protected $fillable = [
        'customerID',
        'user_id',
        'reward_id',
        'voucherCode',
        'code',
        'voucherAmount',
        'discount_percent',
        'voucherType',
        'redeem_place',
        'validFrom',
        'validUntil',
        'expires_at',
        'conditions',
        'terms_conditions',
        'isUsed',
        'status'
    ];

    protected $casts = [
        'validFrom' => 'date',
        'validUntil' => 'date',
        'expires_at' => 'date',
        'isUsed' => 'boolean',
    ];

    public function customer() {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }

    // Accessors to handle both old and new column names
    public function getCodeAttribute()
    {
        $value = $this->attributes['code'] ?? null;
        return $value ?? $this->attributes['voucherCode'] ?? null;
    }

    public function getDiscountPercentAttribute()
    {
        $value = $this->attributes['discount_percent'] ?? null;
        return $value;
    }

    public function getStatusAttribute()
    {
        $value = $this->attributes['status'] ?? null;
        if ($value !== null) {
            return $value;
        }
        // Map isUsed to status
        return ($this->attributes['isUsed'] ?? false) ? 'used' : 'unused';
    }

    public function getUserIdAttribute()
    {
        $value = $this->attributes['user_id'] ?? null;
        return $value ?? $this->attributes['customerID'] ?? null;
    }

    
}
