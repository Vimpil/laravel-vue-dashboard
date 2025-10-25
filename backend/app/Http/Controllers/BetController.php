<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BetController extends Controller
{
    public function store(Request $request)
    {
        // Placeholder logic for storing a bet
        return response()->json(['message' => 'Bet stored successfully']);
    }
}
