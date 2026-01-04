<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    // Define the table name explicitly if it's not the plural of the model name
    protected $table = 'feedbacks'; // Assuming your table is named 'feedback'

    // Define the primary key if it's not 'id'
    // protected $primaryKey = 'feedbackID'; 

    protected $fillable = [
        'bookingID',
        'customerID',
        'rating',
        'comment', // Match this to your DB column name (comment vs comments)
        'created_at',
        'updated_at',
        'type',
    ];

    // Define relationship back to Booking
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'bookingID', 'bookingID');
    }

    // Define relationship to Customer
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customerID', 'customerID');
    }

    // 2. Define Customer "Through" the Booking (So your view code $review->customer works)
    public function getCustomerAttribute()
    {
        return $this->booking ? $this->booking->customer : null;
    }

    // 3. Define Vehicle "Through" the Booking
    public function getVehicleAttribute()
    {
        return $this->booking ? $this->booking->vehicle : null;
    }
}