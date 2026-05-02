<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    protected $table = 'deals';
    protected $fillable = [
        'staff_id',
        'image_path',
        'title',
        'description',
        'order',
        'active',
    ];

    /**
     * Relationship: Deals belong to a Staff member
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staffID');
    }
}
