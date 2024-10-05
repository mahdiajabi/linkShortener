<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use App\Jobs\SendOtpJob;
use Illuminate\Support\Str;

class OtpController extends Controller
{
    public function sendOtp(Request $request)
    {
        $phoneNumber = $request->input('phone_number');

        $user = User::where('phone_number', $phoneNumber)->first();

        if (!$user) {
            $user = User::create([
                'phone_number' => $phoneNumber,
                'name' => 'User_' . Str::random(6), 
                'password' => null,
            ]);
        }

        if ($user->expires_at && $user->expires_at > Carbon::now()) {
            return response()->json([
                'message' => 'OTP has already been sent. Please wait until it expires.',
                'expires_at' => $user->expires_at
            ], 429);
        }

        $newOtpCode = rand(1000, 9999);
        $expiresAt = Carbon::now()->addMinutes(5);

        $user->update([
            'otp_code' => $newOtpCode,
            'expires_at' => $expiresAt,
            'used_at' => null,  
        ]);

        dispatch(new SendOtpJob($phoneNumber, $newOtpCode));

        return response()->json(['message' => 'OTP will be sent shortly.', 'expires_at' => $expiresAt]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required',
            'otp_code' => 'required',
        ]);

        $user = User::where('phone_number', $request->phone_number)
                    ->where('otp_code', $request->otp_code)
                    ->whereNull('used_at')  
                    ->where('expires_at', '>', Carbon::now())  
                    ->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid or expired OTP'], 400);
        }

        $user->update(['used_at' => Carbon::now()]);

        return response()->json(['message' => 'OTP verified successfully']);
    }
}
