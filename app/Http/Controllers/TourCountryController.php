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

    public function getTourCountry(Request $request, string $countrySlug): JsonResponse
    {
        $searchResults = TourCountry::whereSlug($countrySlug)->with('tours')->get();
        if ($searchResults->count() === 0) {
            return response()->json(null);
        }

        return response()->json($searchResults[0]);
    }
}
