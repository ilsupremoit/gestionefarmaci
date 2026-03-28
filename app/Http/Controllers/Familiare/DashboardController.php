<?php

namespace App\Http\Controllers\Familiare;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $familiare = Auth::user();
        return view('familiare.dashboard', compact('familiare'));
    }
}
