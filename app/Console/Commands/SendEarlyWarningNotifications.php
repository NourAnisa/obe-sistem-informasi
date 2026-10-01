<?php

namespace App\Console\Commands;

use App\Services\EarlyWarningService;
use Illuminate\Console\Command;

class SendEarlyWarningNotifications extends Command
{
    protected $signature = 'obe:send-early-warning
                            {--ta= : Tahun akademik (default: config obe.tahun_akademik)}
                            {--angkatan= : Filter angkatan tertentu}
                            {--dry-run : Preview tanpa kirim}';

    protected $description = 'Kirim email early warning CPL ke dosen PA';

    public function handle(EarlyWarningService $svc): int
    {
        $ta       = $this->option('ta') ?? config('obe.tahun_akademik', '2025/2026');
        $angkatan = $this->option('angkatan');
        $dryRun   = $this->option('dry-run');

        $this->info("⚠️  Early Warning — TA: {$ta}" . ($angkatan ? ", Angkatan: {$angkatan}" : ''));

        if ($dryRun) {
            $preview = $svc->previewCount($ta, $angkatan);
            $this->table(['Metric', 'Jumlah'], [
                ['Total records early warning', $preview['total_records']],
                ['Total mahasiswa',             $preview['total_mahasiswa']],
                ['Total dosen PA',              $preview['total_dosen_pa']],
            ]);
            $this->warn('Dry-run: tidak ada email yang dikirim.');
            return self::SUCCESS;
        }

        $result = $svc->sendNotifications($ta, $angkatan);

        $this->info("✅ Email job di-dispatch: {$result['sent']} dosen PA");
        if (!empty($result['skipped'])) {
            $this->warn("⏭  Dilewati (email kosong): {$result['skipped']}");
        }
        foreach ($result['errors'] as $err) {
            $this->error("✗ {$err}");
        }

        return empty($result['errors']) ? self::SUCCESS : self::FAILURE;
    }
}
