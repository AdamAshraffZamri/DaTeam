<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function about() { return view('pages.about'); }
    public function faq() { return view('pages.faq'); }
    public function contact() { return view('pages.contact'); }

    public function singapore() { return view('pages.singapore'); }

    public function travel() { return view('pages.travel'); }

    public function flight() { return view('pages.flight'); }
}