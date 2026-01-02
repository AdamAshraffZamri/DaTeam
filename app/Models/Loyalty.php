<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Backwards-compatible alias model.
 *
 * The application now uses `LoyaltyPoint` (table: `loyalty_points`).
 * Keep this class so any legacy references to `Loyalty` still work.
 */
class Loyalty extends LoyaltyPoint
{
    use HasFactory;
}
