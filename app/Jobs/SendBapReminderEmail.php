<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\BapReminderNotification;

/**
 * SendBapReminderEmail
 *
 * Kirim reminder token evaluasi BAP ke mahasiswa yang belum mengisi.
 * Di-dispatch dari artisan command: obe:send-bap-reminder
 *
 * Logic: cek bap_pertemuan yang sudah melewati waktu pertemuan tapi
 * mahasiswanya belum mengisi evaluasi (bap_evaluasi_mahasiswa).
 */
class SendBapReminderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $backoff = 60;

    public function __construct(
        public readonly string $mahasiswaEmail,
        public readonly string $mahasiswaNama,
        public readonly string $mataKuliahNama,
        public readonly string $token,
        public readonly int    $minggu,
        public readonly string $ta,
    ) {}

    public function handle(): void
    {
        Mail::to($this->mahasiswaEmail)
            ->send(new BapReminderNotification(
                $this->mahasiswaNama,
                $this->mataKuliahNama,
                $this->token,
                $this->minggu,
                $this->ta,
            ));

        Log::info("BAP reminder sent to {$this->mahasiswaEmail}, MK: {$this->mataKuliahNama}, Minggu: {$this->minggu}");
    }

    public function failed(\Throwable $e): void
    {
        Log::error("BAP reminder FAILED to {$this->mahasiswaEmail}: " . $e->getMessage());
    }
}
