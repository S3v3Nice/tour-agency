<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\TourHotel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TourController extends Controller
{
    public function getTours(): JsonResponse
    {
        return response()->json(Tour::with('hotel.city.country')->get());
    }

    public function addTour(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'hotel_id'          => ['required', 'int', Rule::exists(TourHotel::class, 'id')],
            'start_date'        => ['required', 'date_format:Y-m-d\TH:i'],
            'end_date'          => ['required', 'date_format:Y-m-d\TH:i'],
            'max_participant_count' => ['required', 'int'],
            'adult_price'       => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'errors'  => $validator->errors(),
                ]
            );
        }

        $hotel = Tour::make($request->only(['start_date', 'end_date', 'max_participant_count', 'adult_price']));
        $hotel->hotel()->associate(TourHotel::find($request->get('hotel_id')));
        $hotel->save();

        return response()->json(['success' => true]);
    }

    public function updateTour(Request $request, Tour $tour): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'hotel_id'          => ['int', Rule::exists(TourHotel::class, 'id')],
            'start_date'        => ['date_format:Y-m-d\TH:i'],
            'end_date'          => ['date_format:Y-m-d\TH:i'],
            'max_participant_count' => ['int'],
            'adult_price'       => ['numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'errors'  => $validator->errors(),
                ]
            );
        }

        if ($request->has('hotel_id')) {
            $tour->hotel()->associate(TourHotel::find($request->get('hotel_id')));
        }
        $tour->update($request->only(['start_date', 'end_date', 'max_participant_count', 'adult_price']));

        return response()->json(['success' => true]);
    }

    public function deleteTour(Tour $tour): JsonResponse
    {
        $tour->delete();

        return response()->json(['success' => true]);
    }
}
