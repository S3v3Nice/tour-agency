<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\TourCity;
use App\Models\TourCountry;
use App\Models\TourHotel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TourController extends Controller
{
    use ApiJsonResponseTrait;

    public function getTours(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'country_id' => ['integer', Rule::exists(TourCountry::class, 'id')],
            'city_id' => ['integer', Rule::exists(TourCity::class, 'id')],
            'start_date_range' => ['array', 'min:1', 'max:2'],
            'start_date_range.*' => ['date'],
            'min_days' => ['integer', 'min:1'],
            'adults_count' => ['integer', 'min:1'],
            'children_count' => ['integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        /** @var int|null $countryId */
        $countryId = $request->input('country_id');

        /** @var int|null $cityId */
        $cityId = $request->input('city_id');

        /** @var string[]|null $startDateRange */
        $startDateRange = $request->input('start_date_range');

        /** @var int|null $minDays */
        $minDays = $request->input('min_days');

        $adultsCount = $request->integer('adults_count', 1);
        $childrenCount = $request->integer('children_count');
        $sortOrder = $request->integer('sort_order', -1);

        if ($sortOrder === 0) {
            $sortField = 'created_at';
            $sortDirection = 'desc';
        } else {
            $sortField = $request->string('sort_field', 'id');
            $sortDirection = $sortOrder === -1 ? 'desc' : 'asc';
        }

        $participantCount = $adultsCount + $childrenCount;

        $records = Tour::with('hotel.city.country')
            ->orderBy($sortField, $sortDirection)
            ->whereRaw('max_participant_count - (SELECT COALESCE(SUM(adults_count + children_count), 0) FROM tour_bookings WHERE tour_bookings.tour_id = tours.id) >= ?', [$participantCount]);

        if ($cityId !== null) {
            $records = $records->whereHas('hotel', function ($query) use ($cityId) {
                $query->where('city_id', $cityId);
            });
        } elseif ($countryId !== null) {
            $records = $records->whereHas('hotel.city', function ($query) use ($countryId) {
                $query->where('country_id', $countryId);
            });
        }

        if ($startDateRange !== null) {
            $startDateFrom = $startDateRange[0];
            $records = $records->whereDate('start_date', '>=', $startDateFrom);

            if (count($startDateRange) > 1) {
                $startDateTo = $startDateRange[1];
                $records = $records->whereDate('start_date', '<=', $startDateTo);
            }
        }

        if ($minDays !== null) {
            $records = $records->whereRaw('(UNIX_TIMESTAMP(end_date) - UNIX_TIMESTAMP(start_date)) / 86400 >= ?', [$minDays]);
        }

        return $this->successJsonResponse([
                'records' => $records->get()
            ]
        );
    }

    public function addTour(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'hotel_id' => ['required', 'int', Rule::exists(TourHotel::class, 'id')],
            'start_date' => ['required', 'date_format:Y-m-d\TH:i'],
            'end_date' => ['required', 'date_format:Y-m-d\TH:i'],
            'max_participant_count' => ['required', 'int'],
            'adult_price' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse(
                '',
                $validator->errors()
            );
        }

        $hotel = Tour::make($request->only(['start_date', 'end_date', 'max_participant_count', 'adult_price']));
        $hotel->hotel()->associate(TourHotel::find($request->get('hotel_id')));
        $hotel->save();

        return $this->successJsonResponse();
    }

    public function updateTour(Request $request, Tour $tour): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'hotel_id' => ['int', Rule::exists(TourHotel::class, 'id')],
            'start_date' => ['date_format:Y-m-d\TH:i'],
            'end_date' => ['date_format:Y-m-d\TH:i'],
            'max_participant_count' => ['int'],
            'adult_price' => ['numeric'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse(
                '',
                $validator->errors()
            );
        }

        if ($request->has('hotel_id')) {
            $tour->hotel()->associate(TourHotel::find($request->get('hotel_id')));
        }
        $tour->update($request->only(['start_date', 'end_date', 'max_participant_count', 'adult_price']));

        return $this->successJsonResponse();
    }

    public function deleteTour(Tour $tour): JsonResponse
    {
        $tour->delete();
        return $this->successJsonResponse();
    }
}
