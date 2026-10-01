<?php

namespace App\Console\Commands;

use App\Jobs\SendBapReminderEmail;
use App\Models\BapToken;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendBapReminder extends Command
{
    protected $signature = 'obe:send-bap-reminder
                            {--ta= : Tahun akademik (default: config obe.tahun_akademik)}
                            {--minggu= : Minggu pertemuan spesifik (default: semua yang belum diisi)}
                            {--dry-run : Tampilkan jumlah mahasiswa yang akan dikirimi tanpa kirim}';

    protected $description = 'Kirim reminder email ke mahasiswa yang belum mengisi evaluasi BAP';

    public function handle(): int
    {
        $ta     = $this->option('ta') ?? config('obe.tahun_akademik', '2025/2026');
        $minggu = $this->option('minggu') ? (int) $this->option('minggu') : null;
        $dryRun = $this->option('dry-run');

        $this->info("📋 BAP Reminder — TA: {$ta}" . ($minggu ? ", Minggu: {$minggu}" : ''));

        // Cari mahasiswa yang punya token tapi belum mengisi evaluasi
        $query = DB::table('bap_tokens as bt')
            ->join('bap_pertemuan as bp', 'bp.id', '=', 'bt.bap_pertemuan_id')
            ->join('bap as b', 'b.id', '=', 'bp.bap_id')
            ->join('mata_kuliah as mk', 'mk.id', '=', 'b.mata_kuliah_id')
            ->join('mahasiswa_mk as mmk', function ($join) use ($ta) {
                $join->on('mmk.mata_kuliah_id', '=', 'b.mata_kuliah_id')
                     ->where('mmk.semester_aktif', '=', $ta)
                     ->where('mmk.status', '=', 'disetujui');
            })
            ->join('mahasiswas as mhs', 'mhs.id', '=', 'mmk.mahasiswa_id')
            ->join('users as u', 'u.id', '=', 'mhs.user_id')
            ->where('b.semester_aktif', $ta)
            ->where('bt.expired_at', '>', now())
            ->whereNotNull('u.email')
            ->whereNotExists(function ($q) {
                $q->from('bap_evaluasi_mahasiswa as bem')
                    ->whereColumn('bem.mahasiswa_id', 'mhs.id')
                    ->whereColumn('bem.bap_pertemuan_id', 'bp.id');
            })
            ->select('mhs.id as mahasiswa_id', 'mhs.nama', 'u.email',
                     'mk.nama as mk_nama', 'bt.token', 'bp.minggu');

        if ($minggu) {
            $query->where('bp.minggu', $minggu);
        }

        $rows = $query->get();

        if ($rows->isEmpty()) {
            $this->info('✅ Tidak ada mahasiswa yang perlu diingatkan.');
            return self::SUCCESS;
        }

        $this->info("📨 Ditemukan {$rows->count()} mahasiswa belum mengisi evaluasi.");

        if ($dryRun) {
            $this->table(['Nama', 'Email', 'MK', 'Minggu'], $rows->map(fn($r) => [
                $r->nama, $r->email, $r->mk_nama, $r->minggu,
            ])->toArray());
            $this->warn('Dry-run mode: tidak ada email yang dikirim.');
            return self::SUCCESS;
        }

        $dispatched = 0;
        foreach ($rows as $r) {
            SendBapReminderEmail::dispatch(
                $r->email, $r->nama, $r->mk_nama, $r->token, $r->minggu, $ta
            );
            $dispatched++;
        }

        $this->info("✅ {$dispatched} job email berhasil di-dispatch ke queue.");
        return self::SUCCESS;
    }
}
