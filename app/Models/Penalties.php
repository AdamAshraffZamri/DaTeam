<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class penalties extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'amount', 'reason', 'date_imposed'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
