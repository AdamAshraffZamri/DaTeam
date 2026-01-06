<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    use HasFactory;

    protected $table = 'rewards';

    protected $fillable = [
        'name', 
        'offer_description', 
        'points_required', 
        'code_prefix', 
        'validity_months', 
        'category',         // 'Milestone' atau 'Food'
        'icon_class', 
        'color_class', 
        'is_active',
        'milestone_step',   // PENTING: Target Booking (Contoh: 3, 6, 9, 10, 50...)
        'discount_percent'  // Nilai Diskaun %
    ];

    protected $casts = [
        'points_required' => 'integer',
        'validity_months' => 'integer',
        'is_active' => 'boolean',
        'milestone_step' => 'integer',
        'discount_percent' => 'integer',
    ];
}