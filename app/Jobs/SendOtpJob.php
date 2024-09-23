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

    public $email;

    public function __construct($email)
    {
        $this->email = $email;
    }

    public function handle()
    {
        $otpCode = rand(1000, 9999);
        $expiresAt = Carbon::now()->addMinutes(1);

        Otp::create([
            'email' => $this->email,
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
