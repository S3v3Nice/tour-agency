<?php

namespace App\Http\Controllers;

use App\Models\TourCountry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TourCountryController extends Controller
{
    public function getTourCountries(Request $request): JsonResponse
    {
        return response()->json(TourCountry::all());
    }
}
