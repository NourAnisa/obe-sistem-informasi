<?php

namespace App\Jobs;

use App\Mail\EarlyWarningNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * SendEarlyWarningEmail
 *
 * Job untuk mengirim email early warning ke satu dosen PA.
 * Di-dispatch dari EarlyWarningService::sendNotifications().
 *
 * Konfigurasi queue di .env:
 *   QUEUE_CONNECTION=database   (atau redis untuk production)
 */
class SendEarlyWarningEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Retry 3x jika mail server gagal */
    public int $tries = 3;

    /** Delay antar retry: 30 detik */
    public int $backoff = 30;

    public function __construct(
        public readonly string $paEmail,
        public readonly string $paNama,
        public readonly array  $warningList,
        public readonly string $ta,
    ) {}

    public function handle(): void
    {
        Mail::to($this->paEmail)
            ->send(new EarlyWarningNotification($this->paNama, $this->warningList, $this->ta));

        Log::info("EarlyWarning email dispatched to {$this->paEmail} ({$this->paNama}), " . count($this->warningList) . " records.");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("EarlyWarning email FAILED to {$this->paEmail}: " . $exception->getMessage());
    }
}
