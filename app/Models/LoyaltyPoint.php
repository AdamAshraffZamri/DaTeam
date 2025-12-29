<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyPoint extends Model
{
    protected $fillable = ['user_id', 'points', 'tier'];

    public function customer() {
        return $this->belongsTo(Customer::class, 'user_id', 'customerID');
    }
}

