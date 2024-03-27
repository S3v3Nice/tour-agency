<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\TourBooking;
use App\Models\TourPayment;
use App\Models\User;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class TourBookingController extends Controller
{
    use ApiJsonResponseTrait;

    const PREPAYMENT_RATE = 0.5;
    const CHILD_PRICE_RATE = 0.5;

    public function getTourBookings(): JsonResponse
    {
        $sortField = 'created_at';
        $sortDirection = 'desc';

        $records = TourBooking::with(['user', 'tour.hotel.city.country'])->orderBy($sortField, $sortDirection)->get();
        return $this->successJsonResponse(
            [
                'records' => $records
            ]
        );
    }

    public function getByUser(Request $request, int $id): JsonResponse
    {
        /** @var User|null $user */
        $user = User::find($id)->first();
        $currentUser = Auth::user();

        if ($user?->id !== $currentUser->id && !$currentUser->is_admin) {
            throw new AccessDeniedHttpException();
        }

        if ($user == null) {
            return $this->errorJsonResponse('Не найден пользователь с id ' . $id . '.');
        }

        $sortField = 'created_at';
        $sortDirection = 'desc';

        $records = TourBooking::whereUserId($id)->with(['user', 'tour.hotel.city.country'])->orderBy($sortField, $sortDirection)->get();
        return $this->successJsonResponse(
            [
                'records' => $records
            ]
        );
    }

    public function makeTourBooking(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tour_id' => ['required', 'int', Rule::exists(Tour::class, 'id')],
            'adults_count' => ['required', 'int', 'min:1'],
            'children_count' => ['required', 'int', 'min:0'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $tour = Tour::find($request->get('tour_id'));
        $adultsCount = $request->get('adults_count');
        $childrenCount = $request->get('children_count');
        $placesLeft = $tour->max_participant_count - $tour->participant_count;

        if (($adultsCount + $childrenCount) > $placesLeft) {
            return $this->errorJsonResponse(
                '',
                [
                    'adults_count' => ['Количество участников превышает оставшееся количество мест: ' . $placesLeft]
                ],
            );
        }

        $totalCost = ($adultsCount * $tour->adult_price) + ($childrenCount * $tour->adult_price * self::CHILD_PRICE_RATE);

        $attributes = [
            'adults_count' => $adultsCount,
            'children_count' => $childrenCount,
            'total_amount' => $totalCost
        ];

        $booking = TourBooking::make($attributes);
        $booking->user()->associate(Auth::user());
        $booking->tour()->associate($tour);
        $booking->save();

        $payment = TourPayment::make(['amount' => $totalCost * self::PREPAYMENT_RATE]);
        $payment->booking()->associate($booking);
        $payment->save();

        return $this->successJsonResponse();
    }

    public function verifyTourBooking(TourBooking $booking): JsonResponse
    {
        $booking->is_verified = true;
        $booking->save();

        return $this->successJsonResponse();
    }

    public function deleteTourBooking(TourBooking $booking): JsonResponse
    {
        $booking->delete();
        return $this->successJsonResponse();
    }

    public function payRemainingAmount(int $bookingId): JsonResponse
    {
        /** @var TourBooking|null $booking */
        $booking = TourBooking::find($bookingId)->first();

        $currentUser = Auth::user();

        if ($booking?->user_id !== $currentUser->id && !$currentUser->is_admin) {
            throw new AccessDeniedHttpException();
        }

        if ($booking === null) {
            return $this->errorJsonResponse('Не найдена запись на тур с id ' . $bookingId . '.');
        }

        if (!$booking->is_verified) {
            return $this->errorJsonResponse('Нельзя доплатить неподтвержденную запись на тур.');
        }

        $payment = TourPayment::make(['amount' => $booking->total_amount - $booking->payed_amount]);
        $payment->booking()->associate($booking);
        $payment->save();

        return $this->successJsonResponse();
    }
}
