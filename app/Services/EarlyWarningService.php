<?php

namespace App\Services;

use App\Jobs\SendEarlyWarningEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * EarlyWarningService
 *
 * Mengirim email notifikasi ke dosen PA ketika mahasiswa bimbingannya
 * masuk kategori Early Warning (CPL achieved = 0).
 *
 * Panggil dari:
 *   - Console command (artisan obe:send-early-warning)
 *   - Setelah runAllPipeline() di ObeCalculationService
 *   - Manual trigger dari dashboard
 */
class EarlyWarningService
{
    /**
     * Kirim notifikasi ke semua dosen PA yang punya mahasiswa early warning.
     *
     * @param  string $ta           Tahun akademik filter
     * @param  string|null $angkatan Filter angkatan (opsional)
     * @return array ['sent' => int, 'skipped' => int, 'errors' => array]
     */
    public function sendNotifications(string $ta, ?string $angkatan = null): array
    {
        // Ambil mahasiswa yang tidak mencapai CPL (achieved = 0)
        // beserta dosen PA mereka
        $query = DB::table('cpl_achievement as ca')
            ->join('mahasiswas as mhs', 'mhs.id', '=', 'ca.mahasiswa_id')
            ->join('cpl', 'cpl.id', '=', 'ca.cpl_id')
            ->leftJoin('users as pa', 'pa.id', '=', 'mhs.dosen_pa_id')  // dosen PA
            ->where('ca.semester_aktif', $ta)
            ->where('ca.achieved', 0)
            ->select(
                'mhs.nim',
                'mhs.nama',
                'mhs.angkatan',
                'cpl.kode as cpl_kode',
                'ca.nilai_cpl',
                'ca.threshold',
                DB::raw('ROUND(ca.threshold - ca.nilai_cpl, 2) as gap'),
                'pa.id as pa_id',
                'pa.name as pa_nama',
                'pa.email as pa_email',
            )
            ->whereNotNull('pa.email')  // hanya jika dosen PA punya email
            ->orderBy('pa.id')->orderBy('mhs.nim')->orderBy('cpl.kode');

        if ($angkatan) {
            $query->where('ca.angkatan', $angkatan);
        }

        $rows = $query->get();

        if ($rows->isEmpty()) {
            return ['sent' => 0, 'skipped' => 0, 'errors' => [], 'message' => 'Tidak ada data early warning.'];
        }

        // Tandai status per baris
        $rows = $rows->map(function ($r) {
            $r->status = (float)$r->nilai_cpl < (float)$r->threshold * 0.5
                ? 'Kritis'
                : 'Perlu Intervensi';
            return $r;
        });

        // Group by dosen PA
        $grouped = $rows->groupBy('pa_id');

        $sent    = 0;
        $skipped = 0;
        $errors  = [];

        foreach ($grouped as $paId => $mahasiswaRows) {
            $paEmail = $mahasiswaRows->first()->pa_email;
            $paNama  = $mahasiswaRows->first()->pa_nama;

            if (empty($paEmail)) {
                $skipped++;
                continue;
            }

            // Susun list untuk email
            $warningList = $mahasiswaRows->map(fn($r) => [
                'nim'       => $r->nim,
                'nama'      => $r->nama,
                'angkatan'  => $r->angkatan,
                'cpl_kode'  => $r->cpl_kode,
                'nilai_cpl' => $r->nilai_cpl,
                'threshold' => $r->threshold,
                'gap'       => $r->gap,
                'status'    => $r->status,
            ])->toArray();

            try {
                SendEarlyWarningEmail::dispatch($paEmail, $paNama, $warningList, $ta);
                $sent++;
                Log::info("EarlyWarning job dispatched to {$paEmail} ({$paNama}), {$mahasiswaRows->count()} records.");
            } catch (\Throwable $e) {
                $errors[] = "Gagal dispatch ke {$paEmail}: " . $e->getMessage();
                Log::error('EarlyWarning dispatch error: ' . $e->getMessage());
            }
        }

        return compact('sent', 'skipped', 'errors');
    }

    /**
     * Hitung berapa dosen PA yang akan menerima notifikasi
     * (untuk preview sebelum kirim).
     */
    public function previewCount(string $ta, ?string $angkatan = null): array
    {
        $query = DB::table('cpl_achievement as ca')
            ->join('mahasiswas as mhs', 'mhs.id', '=', 'ca.mahasiswa_id')
            ->join('cpl', 'cpl.id', '=', 'ca.cpl_id')
            ->leftJoin('users as pa', 'pa.id', '=', 'mhs.dosen_pa_id')
            ->where('ca.semester_aktif', $ta)
            ->where('ca.achieved', 0)
            ->whereNotNull('pa.email');

        if ($angkatan) $query->where('ca.angkatan', $angkatan);

        return [
            'total_records'  => $query->count(),
            'total_mahasiswa' => $query->distinct()->count('mhs.id'),
            'total_dosen_pa' => $query->distinct()->count('pa.id'),
        ];
    }
}
