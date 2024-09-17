<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Otp;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

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
        
        $newOtpCode = rand(1000, 9999);
        $expiresAt = Carbon::now()->addMinutes(1);

        Otp::create([
            'email' => $email,
            'otp_code' => $newOtpCode,
            'expires_at' => $expiresAt,
        ]);

        return response()->json(['message' => 'OTP has been sent successfully.', 'otp_code' => $newOtpCode, 'expires_at' => $expiresAt]);
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
