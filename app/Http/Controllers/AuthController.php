<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|min:3',
                'email' => 'required|string|email|unique:users',
                'password' => 'required|string|min:6',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
            ]);

            $token = $user->createToken('token')->plainTextToken;

            return ResponseFormatter::success([
                'token' => $token,
                'user' => $user,
            ], 'User registration successfully.');
        } catch (\Illuminate\Validation\ValidationException $th) {
            return ResponseFormatter::error($th->errors(), $th->getMessage(), 422);
        } catch (\Throwable $th) {
            return ResponseFormatter::error(false, $th->getMessage());
        }
    }

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string|min:6',
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return ResponseFormatter::error(false, 'Incorrect email or password.', 422);
            }

            $user->tokens()->delete();

            $token = $user->createToken('token')->plainTextToken;

            return ResponseFormatter::success([
                'token' => $token,
                'user' => $user,
            ], 'User login successfully.');
        } catch (\Throwable $th) {
            return ResponseFormatter::error(false, $th->getMessage());
        }
    }

    public function user(Request $request)
    {
        try {
            return ResponseFormatter::success($request->user(), 'User data successfully retrieved.');
        } catch (\Throwable $th) {
            return ResponseFormatter::error(false, $th->getMessage());
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return ResponseFormatter::success(true, 'Successfully logged out of this account session.');
        } catch (\Throwable $th) {
            return ResponseFormatter::error(false, $th->getMessage());
        }
    }
}
