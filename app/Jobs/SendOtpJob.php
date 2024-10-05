<?php

namespace App\Jobs;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

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

        User::where('phone_number', $this->phone_number)->update([
            'otp_code' => $otpCode,
            'expires_at' => $expiresAt,
        ]);

        Log::info("OTP {$otpCode} for phone number {$this->phone_number} has been generated and will expire at {$expiresAt}.");
    }
}
