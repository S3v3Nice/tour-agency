<?php

namespace App\Http\Controllers;

use App\Models\TourCity;
use App\Models\TourCountry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class TourCityController extends Controller
{
    use ApiJsonResponseTrait;

    const MAX_IMAGE_SIZE_MB = 5;

    public function getTourCities(Request $request): JsonResponse
    {
        $records = TourCity::with('country')->get();
        return $this->successJsonResponse([
                'records' => $records
            ]
        );
    }

    public function addTourCity(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'country_id' => ['required', 'int', Rule::exists(TourCountry::class, 'id')],
            'description' => ['required', 'string'],
            'image' => [
                'required',
                File::types(['jpeg', 'jpg', 'png']),
                File::image()
                    ->max(self::MAX_IMAGE_SIZE_MB * 1024)
                    ->dimensions(
                        Rule::dimensions()
                            ->minWidth(100)
                            ->minHeight(100)
                    ),
            ],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse(
                '',
                $validator->errors()
            );
        }

        $attributes = $request->only(['name', 'description']);

        $image = $request->file('image');
        $imagePath = $image->storeAs('public/tour_cities/', $image->hashName());
        $publicImagePath = substr_replace($imagePath, 'storage/', 0, strlen('public/'));

        $attributes = array_merge($attributes, ['image_path' => $publicImagePath]);

        $city = TourCity::make($attributes);
        $city->country()->associate(TourCountry::find($request->get('country_id')));
        $city->save();

        return $this->successJsonResponse();
    }

    public function updateTourCity(Request $request, TourCity $city): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['string'],
            'country_id' => ['int', Rule::exists(TourCountry::class, 'id')],
            'description' => ['string'],
            'image' => [
                File::types(['jpeg', 'jpg', 'png']),
                File::image()
                    ->max(self::MAX_IMAGE_SIZE_MB * 1024)
                    ->dimensions(
                        Rule::dimensions()
                            ->minWidth(100)
                            ->minHeight(100)
                    ),
            ],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse(
                '',
                $validator->errors()
            );
        }

        $attributes = $request->only(['name', 'description']);

        $image = $request->file('image');
        if ($image !== null) {
            $oldImagePath = substr_replace($city->image_path, 'public/', 0, strlen('storage/'));
            Storage::disk()->delete($oldImagePath);

            $imagePath = $image->storeAs('public/tour_cities/', $image->hashName());
            $publicImagePath = substr_replace($imagePath, 'storage/', 0, strlen('public/'));

            $attributes = array_merge($attributes, ['image_path' => $publicImagePath]);
        }

        $city->update($attributes);
        $city->country()->associate(TourCountry::find($request->get('country_id')));
        $city->save();

        return $this->successJsonResponse();
    }

    public function deleteTourCity(TourCity $city): JsonResponse
    {
        $imagePath = substr_replace($city->image_path, 'public/', 0, strlen('storage/'));
        Storage::disk()->delete($imagePath);

        $city->delete();
        return $this->successJsonResponse();
    }
}
