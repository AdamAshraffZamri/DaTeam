<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $primaryKey = 'voucherID';
    
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
