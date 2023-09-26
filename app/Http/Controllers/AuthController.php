<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * User register
     */
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required|confirmed'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'errors' => $validateUser->errors()
                ], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'message' => 'User Created Successfully',
                'token' => $user->createToken("API_TOKEN")->plainTextToken
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * User login
     */
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required',
                    'password' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'errors' => $validateUser->errors()
                ], 422);
            }

            if (!User::where('email', $request->email)->exists()) {
                return response()->json([
                    'errors' => [
                        'email' => [
                            'Email does not exist.'
                        ]
                    ]
                ], 404);
            }

            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json([
                    'errors' => [
                        'password' => [
                            'Password does not match.'
                        ]
                    ]
                ], 422);
            }

            $user = User::where('email', $request->email)->first();

            return response()->json([
                'message' => 'Logged In Successfully',
                'token' => $user->createToken("API_TOKEN")->plainTextToken
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * User logout
     */
    public function logout()
    {
        Auth::logout();

        return response()->json([], 204);
    }
}
