<?php

namespace App\Jobs;

use App\Models\Otp;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendOtpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $phone_number;

    public function __construct($phone_number)
    {
        $this->phone_number = $phone_number;
    }

    public function handle()
    {
        $otpCode = rand(1000, 9999);
        $expiresAt = Carbon::now()->addMinutes(1);

        Otp::create([
            'phone_number' => $this->phone_number,
            'otp_code' => $otpCode,
            'expires_at' => $expiresAt,
        ]);

        return response()->json([
            'message' => 'OTP has been sent successfully.',
            'otp_code' => $otpCode,
            'expires_at' => $expiresAt
        ]);
    }
}
