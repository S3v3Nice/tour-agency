<?php

namespace App\Http\Controllers;

use App\Models\TourCountry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Rules\Unique;

class TourCountryController extends Controller
{
    use ApiJsonResponseTrait;

    const MAX_IMAGE_SIZE_MB = 5;

    public function getTourCountries(Request $request): JsonResponse
    {
        $records = TourCountry::all();
        return $this->successJsonResponse([
                'records' => $records
            ]
        );
    }

    public function getTourCountry(Request $request, string $countrySlug): JsonResponse
    {
        $searchResults = TourCountry::whereSlug($countrySlug)->with('tours')->get();
        if ($searchResults->count() === 0) {
            return $this->errorJsonResponse();
        }

        return $this->successJsonResponse([
            'record' => $searchResults[0]
        ]);
    }

    public function addTourCountry(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'slug' => ['required', 'string', new Unique(TourCountry::class)],
            'name' => ['required', 'string'],
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

        $attributes = $request->only(['slug', 'name', 'description']);

        $image = $request->file('image');
        $imagePath = $image->storeAs('public/tour_countries/', $image->hashName());
        $publicImagePath = substr_replace($imagePath, 'storage/', 0, strlen('public/'));

        $attributes = array_merge($attributes, ['image_path' => $publicImagePath]);

        TourCountry::create($attributes);

        return $this->successJsonResponse();
    }

    public function updateTourCountry(Request $request, TourCountry $country): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'slug' => ['string', Rule::unique(TourCountry::class)->ignore($country)],
            'name' => ['string'],
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

        $attributes = $request->only(['slug', 'name', 'description']);

        $image = $request->file('image');
        if ($image !== null) {
            $oldImagePath = substr_replace($country->image_path, 'public/', 0, strlen('storage/'));
            Storage::disk()->delete($oldImagePath);

            $imagePath = $image->storeAs('public/tour_countries/', $image->hashName());
            $publicImagePath = substr_replace($imagePath, 'storage/', 0, strlen('public/'));

            $attributes = array_merge($attributes, ['image_path' => $publicImagePath]);
        }

        $country->update($attributes);

        return $this->successJsonResponse();
    }

    public function deleteTourCountry(TourCountry $country): JsonResponse
    {
        $imagePath = substr_replace($country->image_path, 'public/', 0, strlen('storage/'));
        Storage::disk()->delete($imagePath);

        $country->delete();
        return $this->successJsonResponse();
    }
}
