<?php

namespace App\Http\Controllers;

use App\Models\TourCity;
use App\Models\TourHotel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TourHotelController extends Controller
{
    public function getTourHotels(): JsonResponse
    {
        return response()->json(TourHotel::with('city')->get());
    }

    public function addTourHotel(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'    => ['required', 'string'],
            'city_id' => ['required', 'int', Rule::exists(TourCity::class, 'id')],
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'errors'  => $validator->errors(),
                ]
            );
        }

        $hotel = TourHotel::make(($request->only(['name'])));
        $hotel->city()->associate(TourCity::find($request->get('city_id')));
        $hotel->save();

        return response()->json(['success' => true]);
    }

    public function updateTourHotel(Request $request, TourHotel $hotel): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'    => ['string'],
            'city_id' => ['int', Rule::exists(TourCity::class, 'id')],
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'errors'  => $validator->errors(),
                ]
            );
        }

        $hotel->city()->associate(TourCity::find($request->get('city_id')));
        $hotel->update($request->only(['name', 'description']));

        return response()->json(['success' => true]);
    }

    public function deleteTourHotel(TourHotel $hotel): JsonResponse
    {
        $hotel->delete();

        return response()->json(['success' => true]);
    }
}
