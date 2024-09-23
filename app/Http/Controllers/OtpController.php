<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Otp;
use Carbon\Carbon;
use App\Jobs\SendOtpJob;

class OtpController extends Controller
{
    public function sendOtp(Request $request)
    {
        $email = $request->input('email');

        $otp = Otp::where('email', $email)
                    ->where('expires_at', '>', Carbon::now())
                    ->first();
        if ($otp) {
            return response()->json([
                'message' => 'OTP has already been sent. Please wait until it expires.',
                'expires_at' => $otp->expires_at
            ], 429);
        }

        dispatch(new SendOtpJob($email));

        return response()->json(['message' => 'OTP will be sent shortly.']);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp_code' => 'required',
        ]);

        $otp = Otp::where('email', $request->email)
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
