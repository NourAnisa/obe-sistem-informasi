<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BapEvaluasiMahasiswaNilai extends Model
{
    protected $table = 'bap_evaluasi_mahasiswa';

    public $timestamps = false;   // only created_at (set via useCurrent)

    protected $fillable = [
        'mahasiswa_id',
        'bap_pertemuan_id',
        'pedagogik',
        'profesional',
        'kepribadian',
        'sosial',
        'token_input',
        'hadir',
        'kritik',
        'saran',
        'status_kehadiran',
        'bukti_file',
    ];

    protected $casts = [
        'pedagogik'   => 'float',
        'profesional' => 'float',
        'kepribadian' => 'float',
        'sosial'      => 'float',
        'hadir'       => 'boolean',
        'created_at'  => 'datetime',
    ];

    // ── Relations ─────────────────────────────────────────

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function bapPertemuan(): BelongsTo
    {
        return $this->belongsTo(BapPertemuan::class);
    }

    // ── Helpers ───────────────────────────────────────────

    /** Average of all 4 competency scores. */
    public function rataRata(): float
    {
        return round(($this->pedagogik + $this->profesional + $this->kepribadian + $this->sosial) / 4, 2);
    }

    /** Aggregate summary for a bap_pertemuan (for dosen view). */
    public static function summaryForPertemuan(int $bapPertemuanId): array
    {
        $rows = static::where('bap_pertemuan_id', $bapPertemuanId)->get();

        if ($rows->isEmpty()) {
            return [
                'count'       => 0,
                'hadir'       => 0,
                'sakit'       => 0,
                'izin'        => 0,
                'tk'          => 0,
                'pedagogik'   => 0,
                'profesional' => 0,
                'kepribadian' => 0,
                'sosial'      => 0,
                'rata_rata'   => 0,
            ];
        }

        return [
            'count'       => $rows->count(),
            'hadir'       => $rows->where('status_kehadiran', 'hadir')->count(),
            'sakit'       => $rows->where('status_kehadiran', 'sakit')->count(),
            'izin'        => $rows->where('status_kehadiran', 'izin')->count(),
            'tk'          => $rows->where('status_kehadiran', 'tk')->count(),
            'pedagogik'   => round($rows->whereIn('status_kehadiran', ['hadir', null])->avg('pedagogik') ?? 0, 2),
            'profesional' => round($rows->whereIn('status_kehadiran', ['hadir', null])->avg('profesional') ?? 0, 2),
            'kepribadian' => round($rows->whereIn('status_kehadiran', ['hadir', null])->avg('kepribadian') ?? 0, 2),
            'sosial'      => round($rows->whereIn('status_kehadiran', ['hadir', null])->avg('sosial') ?? 0, 2),
            'rata_rata'   => round($rows->whereIn('status_kehadiran', ['hadir', null])->avg(fn($r) => ($r->pedagogik + $r->profesional + $r->kepribadian + $r->sosial) / 4) ?? 0, 2),
        ];
    }

    /**
     * Auto-mark TK for all meetings of a mahasiswa where:
     * - tanggal is set and has passed 23:59
     * - no submission exists yet
     */
    public static function autoMarkTk(int $mahasiswaId, array $bapPertemuanIds): int
    {
        $marked = 0;
        foreach ($bapPertemuanIds as $ptId) {
            $pt = \App\Models\BapPertemuan::find($ptId);
            if (!$pt || !$pt->tanggal) continue;

            // Deadline: end of that day
            $deadline = \Carbon\Carbon::parse($pt->tanggal)->endOfDay();
            if (now()->lte($deadline)) continue;

            // Already submitted?
            $exists = static::where('mahasiswa_id', $mahasiswaId)
                ->where('bap_pertemuan_id', $ptId)
                ->exists();
            if ($exists) continue;

            static::create([
                'mahasiswa_id'     => $mahasiswaId,
                'bap_pertemuan_id' => $ptId,
                'pedagogik'        => 0,
                'profesional'      => 0,
                'kepribadian'      => 0,
                'sosial'           => 0,
                'hadir'            => false,
                'status_kehadiran' => 'tk',
            ]);
            $pt->increment('jumlah_tk');
            $marked++;
        }
        return $marked;
    }
}
