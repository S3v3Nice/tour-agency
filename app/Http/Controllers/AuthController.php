<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'errors'  => $validator->errors(),
                ]
            );
        }

        $attributes = [
            'email'    => $request->get('email'),
            'password' => $request->get('password'),
        ];

        if (!Auth::attempt($attributes, $request->get('remember', false))) {
            return response()->json(
                [
                    'success' => false,
                    'errors'  => ['email' => ['Неверный адрес эл. почты или пароль.']],
                ]
            );
        }

        $request->session()->regenerate();
        return response()->json(['success' => true]);
    }

    public function register(Request $request): JsonResponse
    {
        // TODO Implement registration

        return response()->json(['success' => true]);
    }

    public function logout(): JsonResponse
    {
        Auth::logout();
        return response()->json(['success' => true]);
    }
}
