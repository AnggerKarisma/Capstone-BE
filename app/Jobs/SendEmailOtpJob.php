<?php

namespace App\Jobs;

use App\Mail\OtpLoginEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;


class SendEmailOtpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $email;
    protected $otpCode;
    /**
     * Create a new job instance.
     */
    public function __construct($email, $otpCode)
    {
        $this->email = $email;
        $this->otpCode = $otpCode;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("Mengirim email OTP ke:" . $this->email);
            Mail::to($this->email)->send(new OtpLoginEmail($this->otpCode));
            Log::info("Email OTP berhasil dikirim ke {$this->email}");

        }catch (\Exception $e) {
            Log::error("Gagal mengirim email OTP ke {$this->email}: " . $e->getMessage());
        }
    }
}
