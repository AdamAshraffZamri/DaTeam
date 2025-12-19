<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Staff extends Authenticatable
{
    use Notifiable;

    protected $table = 'staff'; // Connects to 'staff' table
    protected $primaryKey = 'staffID'; // Matches ERD

    protected $fillable = [
        'name', 'role', 'email', 'phoneNo', 'password', 'active'
    ];

    protected $hidden = [
        'password',
    ];
}