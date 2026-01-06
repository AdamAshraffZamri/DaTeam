<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    use HasFactory;

    // Nama table (optional, tapi bagus untuk confirm)
    protected $table = 'rewards';

    // Column yang boleh diisi
    protected $fillable = [
        'name', 
        'offer_description', 
        'points_required', 
        'code_prefix', 
        'validity_months', 
        'category', 
        'icon_class', 
        'color_class', 
        'is_active',
        'milestone_step',   // Untuk simpan step ke-3, ke-6, dsb
        'discount_percent'  // Untuk simpan nilai % diskaun
        
    ];

    // Pastikan points sentiasa integer
    protected $casts = [
        'points_required' => 'integer',
        'validity_months' => 'integer',
        'is_active' => 'boolean',
    ];
}