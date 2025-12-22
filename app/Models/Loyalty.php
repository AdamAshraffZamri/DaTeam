<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Loyalty extends Model
{
    use HasFactory;

    protected $table = 'loyalties';
    protected $primaryKey = 'loyaltyID';

    protected $fillable = ['customerID', 'tier', 'pointsEarned', 'pointsRedeemed', 'totalPoints'];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }
}
