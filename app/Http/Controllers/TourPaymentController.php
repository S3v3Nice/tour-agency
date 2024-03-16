<?php

namespace App\Http\Controllers;

use App\Models\TourPayment;
use Illuminate\Http\JsonResponse;

class TourPaymentController extends Controller
{
    use ApiJsonResponseTrait;

    public function getTourPayments(): JsonResponse
    {
        $records = TourPayment::with(['booking.user', 'booking.tour.hotel.city.country'])->get();
        return $this->successJsonResponse([
                'records' => $records
            ]
        );
    }

    public function deleteTourPayment(TourPayment $payment): JsonResponse
    {
        $payment->delete();
        return $this->successJsonResponse();
    }
}
