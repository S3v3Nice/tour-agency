<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    use ApiJsonResponseTrait;
    use PasswordValidationRulesTrait;

    public function changeProfileSettings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => ['string', 'nullable'],
            'last_name' => ['string', 'nullable'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $user = Auth::user();
        $user->update($validator->getData());

        return $this->successJsonResponse();
    }

    public function changeEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'confirmed'],
            'email_confirmation' => ['required'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $user = Auth::user();
        $user->email = $request->get('email');
        $user->save();

        return $this->successJsonResponse();
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => $this->getPasswordRules(),
            'password_confirmation' => ['required'],
        ]);

        if ($validator->fails()) {
            return $this->errorJsonResponse('', $validator->errors());
        }

        $user = Auth::user();
        $user->password = $request->get('password');
        $user->save();

        return $this->successJsonResponse();
    }
}
