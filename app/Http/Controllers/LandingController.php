<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        $planes = Plan::where('activo', true)->orderBy('orden')->get();
        return view('landing', compact('planes'));
    }
}
