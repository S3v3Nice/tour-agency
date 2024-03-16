<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    use ApiJsonResponseTrait;

    public function getAnalytics(Request $request): JsonResponse
    {
        $results = [];

        $startDate = $request->get('start_date') ?? Carbon::createFromTimestamp(0)->format('Y-m-d H:i:s.u0');
        $endDate   = $request->get('end_date') ?? Carbon::now()->format('Y-m-d H:i:s.u0');

        switch ($request->get('type')) {
            // Количество записей на туры по городам туров
            case 'tour_bookings_count_by_city':
                $results = $this->getTourBookingsCountByCity($startDate, $endDate);
                break;
            // Количество записей на туры по странам туров
            case 'tour_bookings_count_by_country':
                $results = $this->getTourBookingsCountByCountry($startDate, $endDate);
                break;
            // Общая заполненность туров по городам
            case 'tour_occupancy_by_city':
                $results = $this->getTourOccupancyByCity($startDate, $endDate);
                break;
        }

        return $this->successJsonResponse([
            'results' => $results
        ]);
    }

    private function getTourBookingsCountByCity($startDate, $endDate): array
    {
        $results = DB::table('tour_bookings')
                     ->join('tours', 'tour_bookings.tour_id', '=', 'tours.id')
                     ->join('tour_hotels', 'tours.hotel_id', '=', 'tour_hotels.id')
                     ->join('tour_cities', 'tour_hotels.city_id', '=', 'tour_cities.id')
                     ->select('tour_cities.name', DB::raw('count(*) as bookings_count'))
                     ->whereBetween('tour_bookings.created_at', [$startDate, $endDate])
                     ->groupBy('tour_cities.name')
                     ->get();

        $formattedResults = [];
        foreach ($results as $result) {
            $formattedResults[$result->name] = $result->bookings_count;
        }

        return $formattedResults;
    }

    private function getTourBookingsCountByCountry($startDate, $endDate): array
    {
        $results = DB::table('tour_bookings')
                     ->join('tours', 'tour_bookings.tour_id', '=', 'tours.id')
                     ->join('tour_hotels', 'tours.hotel_id', '=', 'tour_hotels.id')
                     ->join('tour_cities', 'tour_hotels.city_id', '=', 'tour_cities.id')
                     ->join('tour_countries', 'tour_cities.country_id', '=', 'tour_countries.id')
                     ->select('tour_countries.name', DB::raw('count(*) as bookings_count'))
                     ->whereBetween('tour_bookings.created_at', [$startDate, $endDate])
                     ->groupBy('tour_countries.name')
                     ->get();

        $formattedResults = [];
        foreach ($results as $result) {
            $formattedResults[$result->name] = $result->bookings_count;
        }

        return $formattedResults;
    }

    private function getTourOccupancyByCity($startDate, $endDate): array
    {
        $results = DB::table('tour_bookings')
                     ->join('tours', 'tour_bookings.tour_id', '=', 'tours.id')
                     ->join('tour_hotels', 'tours.hotel_id', '=', 'tour_hotels.id')
                     ->join('tour_cities', 'tour_hotels.city_id', '=', 'tour_cities.id')
                     ->select(
                         'tour_cities.name',
                         DB::raw('SUM(tour_bookings.adults_count + tour_bookings.children_count) as participant_count'),
                         DB::raw('MAX(tours.max_participant_count) as max_participant_count')
                     )
                     ->whereBetween('tour_bookings.created_at', [$startDate, $endDate])
                     ->groupBy('tour_cities.name', 'tours.id')
                     ->get();

        $cityTourParticipantData = [];

        foreach ($results as $result) {
            $cityName     = $result->name;
            $prevCount    = ($cityTourParticipantData[$cityName]['participant_count'] ?? 0);
            $prevMaxCount = ($cityTourParticipantData[$cityName]['max_participant_count'] ?? 0);

            $cityTourParticipantData[$cityName]['participant_count']     = $prevCount + $result->participant_count;
            $cityTourParticipantData[$cityName]['max_participant_count'] = $prevMaxCount + $result->max_participant_count;
        }

        $formattedResults = [];
        foreach ($cityTourParticipantData as $cityName => $cityDatum) {
            $formattedResults[$cityName] = ($cityDatum['participant_count'] / $cityDatum['max_participant_count']) * 100;
        }

        return $formattedResults;
    }
}
