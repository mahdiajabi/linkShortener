<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Otp; 
use Carbon\Carbon;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        try {
            $request->validate([
                'phone_number' => 'required|phone_number|unique:users',
                'password' => 'required|min:6',
            ]);

            $user = User::create([
                'phone_number' => $request->phone_number,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('Personal Access Token')->plainTextToken;

            return response()->json([
                'message' => 'User registered successfully',
                'token' => $token,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|phone_number',
            'password' => 'required_without:otp',  
            'otp' => 'required_without:password', 
        ]);

        if ($request->has('password')) {
            $user = User::where('phone_number', $request->phone_number)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            $token = $user->createToken('Personal Access Token')->plainTextToken;

            return response()->json(['token' => $token], 200);
        }

        if ($request->has('otp')) {
            $otp = Otp::where('phone_number', $request->phone_number)
                       ->where('otp_code', $request->otp)
                       ->whereNull('used_at')
                       ->where('expires_at', '>', Carbon::now())
                       ->first();

            if (!$otp) {
                return response()->json(['error' => 'Invalid or expired OTP'], 401);
            }

            $otp->update(['used_at' => Carbon::now()]);

            $user = User::where('phone_number', $request->phone_number)->first();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $token = $user->createToken('Personal Access Token')->plainTextToken;

            return response()->json(['token' => $token], 200);
        }
    }
}
