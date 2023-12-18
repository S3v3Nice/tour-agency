<?php

namespace App\Http\Controllers;

use App\Models\TourPayment;
use Illuminate\Http\JsonResponse;

class TourPaymentController extends Controller
{
    public function getTourPayments(): JsonResponse
    {
        return response()->json(TourPayment::with(['booking.user', 'booking.tour.hotel.city.country'])->get());
    }

    public function deleteTourPayment(TourPayment $payment): JsonResponse
    {
        $payment->delete();

        return response()->json(['success' => true]);
    }
}
