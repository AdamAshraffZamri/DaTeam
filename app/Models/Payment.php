<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';
    protected $primaryKey = 'paymentID';

    protected $fillable = [
        'bookingID', 
        'amount', 
        'depoAmount', 
        'transactionDate',
        'paymentMethod', 
        'paymentStatus', 
        'depoStatus',
        'depoRequestDate', 
        'depoRefundedDate',
        'installmentDetails', // This stores your proof image path
        'isInstallment'
    ];
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'bookingID', 'bookingID');
    }
}