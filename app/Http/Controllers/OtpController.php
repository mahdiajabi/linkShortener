<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Otp;
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
            ]);
        }

        $otp = Otp::where('phone_number', $phoneNumber)
                    ->where('expires_at', '>', Carbon::now())
                    ->first();
        if ($otp) {
            return response()->json([
                'message' => 'OTP has already been sent. Please wait until it expires.',
                'expires_at' => $otp->expires_at
            ], 429);
        }

        dispatch(new SendOtpJob($phoneNumber));

        return response()->json(['message' => 'OTP will be sent shortly.']);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required',
            'otp_code' => 'required',
        ]);

        $otp = Otp::where('phone_number', $request->phone_number)
                   ->where('otp_code', $request->otp_code)
                   ->whereNull('used_at')
                   ->where('expires_at', '>', Carbon::now())
                   ->first();

        if (!$otp) {
            return response()->json(['message' => 'Invalid or expired OTP'], 400);
        }

        $otp->update(['used_at' => Carbon::now()]);

        return response()->json(['message' => 'OTP verified successfully']);
    }
}
