<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyHistory extends Model
{
    protected $fillable = ['user_id', 'points_change', 'reason'];
    
    // Relationship to Customer
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

    // App/Models/LoyaltyHistory.php
    // public function customer() {
    //     return $this->belongsTo(User::class, 'user_id'); 
    // }
}

