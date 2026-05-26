<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffSetting extends Model
{
    protected $table = 'staff_settings';
    protected $fillable = [
        'staff_id',
        'deals_image_path',
        'deals_description',
        'johor_highlights_image_path',
    ];

    /**
     * Relationship: Settings belong to a Staff member
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staffID');
    }
}
