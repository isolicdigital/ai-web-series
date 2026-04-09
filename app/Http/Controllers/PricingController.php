<?php

namespace App\Http\Controllers;

use App\Models\VideoCreditPlan;

class PricingController extends Controller
{
    public function index()
    {
        $plans = VideoCreditPlan::active()->ordered()->get();
        
        return view('page.buycredits', compact('plans'));
    }
}