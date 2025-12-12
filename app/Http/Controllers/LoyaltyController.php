<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function index()
    {
        return view('loyalty.index');
    }
}