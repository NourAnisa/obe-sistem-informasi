<?php

namespace App\Http\Controllers;

use App\Models\EvaluasiCpl;
use App\Models\EvaluasiCpmk;
use App\Models\EvaluasiDistribusiNilai;
use App\Models\EvaluasiHambatan;
use App\Models\EvaluasiKomponenNilai;
use App\Models\EvaluasiTindakLanjut;
use App\Models\LaporanEvaluasi;
use App\Models\MataKuliah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LaporanEvaluasiController extends Controller
{
    /** Tahun akademik yang sedang aktif (bisa dikonfigurasi). */
    private function getTaAktif(): string
    {
        return config('obe.tahun_akademik', '2025/2026');
    }

    /** MK yang aktif = MK yang ada enrollment mahasiswa pada TA aktif. */
    private function getMataKuliahAktif(string $taAktif)
    {
        $mkAktifIdsQuery = DB::table('mahasiswa_mk')
            ->join('mata_kuliah', 'mahasiswa_mk.mata_kuliah_id', '=', 'mata_kuliah.id')
            ->where('mahasiswa_mk.semester_aktif', $taAktif);

        if (Auth::check() && Auth::user()->role !== 'admin' && Auth::user()->program_id) {
            $mkAktifIdsQuery->where('mata_kuliah.program_id', Auth::user()->program_id);
        }

        $mkAktifIds = $mkAktifIdsQuery->distinct()
            ->pluck('mahasiswa_mk.mata_kuliah_id');

        if ($mkAktifIds->isEmpty()) {
            // Fallback: tampilkan semua MK jika belum ada enrollment
            return MataKuliah::orderBy('semester')->orderBy('kode')->get();
        }

        return MataKuliah::whereIn('id', $mkAktifIds)
            ->orderBy('semester')->orderBy('kode')->get();
    }

    public function index(Request $request)
    {
        $taAktif = $this->getTaAktif();

        // Default ke TA aktif jika tidak ada filter yang diberikan
        $filterTa = $request->filled('tahun_akademik') ? $request->tahun_akademik : $taAktif;

        $query = LaporanEvaluasi::with('mataKuliah')
            ->whereHas('mataKuliah')
            ->orderByDesc('created_at');

        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        // Terapkan filter TA (default aktif atau pilihan user)
        $query->where('tahun_akademik', $filterTa);

        if ($request->filled('mata_kuliah_id')) {
            $query->where('mata_kuliah_id', $request->mata_kuliah_id);
        }

        $laporans       = $query->paginate(15)->withQueryString();
        $mataKuliahs    = $this->getMataKuliahAktif($filterTa);
        $tahunAkademiks = LaporanEvaluasi::whereHas('mataKuliah')->distinct()->pluck('tahun_akademik')
            ->push($taAktif)->unique()->sort()->values();
        $filterTaActive = $filterTa;

        return view(
            'evaluasi.laporan.index',
            compact('laporans', 'mataKuliahs', 'tahunAkademiks', 'taAktif', 'filterTaActive')
        );
    }

    public function create()
    {
        $taAktif     = $this->getTaAktif();
        $mataKuliahs = $this->getMataKuliahAktif($taAktif);

        // Bobot per komponen per MK (SUM across all CPMK rows for that MK)
        $bobotQuery = DB::table('bobot_penilaian')
            ->join('mata_kuliah', 'bobot_penilaian.mata_kuliah_id', '=', 'mata_kuliah.id');

        if (Auth::check() && Auth::user()->role !== 'admin' && Auth::user()->program_id) {
            $bobotQuery->where('mata_kuliah.program_id', Auth::user()->program_id);
        }

        $bobotRows = $bobotQuery->select(
                'bobot_penilaian.mata_kuliah_id',
                DB::raw('SUM(bobot_tugas) as tugas'),
                DB::raw('SUM(bobot_uts) as uts'),
                DB::raw('SUM(bobot_uas) as uas'),
                DB::raw('SUM(bobot_partisipatif) as partisipatif'),
                DB::raw('SUM(bobot_proyek) as proyek')
            )
            ->groupBy('bobot_penilaian.mata_kuliah_id')
            ->get()
            ->keyBy('mata_kuliah_id');

        // CPMK list per MK (from pivot mata_kuliah_cpmk)
        $cpmkQuery = DB::table('cpmk')
            ->join('mata_kuliah_cpmk', 'cpmk.id', '=', 'mata_kuliah_cpmk.cpmk_id')
            ->join('mata_kuliah', 'mata_kuliah_cpmk.mata_kuliah_id', '=', 'mata_kuliah.id');

        if (Auth::check() && Auth::user()->role !== 'admin' && Auth::user()->program_id) {
            $cpmkQuery->where('mata_kuliah.program_id', Auth::user()->program_id);
        }

        $cpmkRows = $cpmkQuery->select('mata_kuliah_cpmk.mata_kuliah_id', 'cpmk.kode', 'cpmk.deskripsi')
            ->orderBy('cpmk.kode')
            ->get()
            ->groupBy('mata_kuliah_id');

        // CPL list per MK (from pivot mata_kuliah_cpl)
        $cplQuery = DB::table('cpl')
            ->join('mata_kuliah_cpl', 'cpl.id', '=', 'mata_kuliah_cpl.cpl_id')
            ->join('mata_kuliah', 'mata_kuliah_cpl.mata_kuliah_id', '=', 'mata_kuliah.id');

        if (Auth::check() && Auth::user()->role !== 'admin' && Auth::user()->program_id) {
            $cplQuery->where('mata_kuliah.program_id', Auth::user()->program_id);
        }

        $cplRows = $cplQuery->select('mata_kuliah_cpl.mata_kuliah_id', 'cpl.kode', 'cpl.deskripsi')
            ->orderBy('cpl.kode')
            ->get()
            ->groupBy('mata_kuliah_id');

        // Build JS-ready maps keyed by mata_kuliah_id
        $bobotPerMk  = $bobotRows->map(fn($r) => [
            'tugas'        => (int) $r->tugas,
            'uts'          => (int) $r->uts,
            'uas'          => (int) $r->uas,
            'partisipatif' => (int) $r->partisipatif,
            'proyek'       => (int) $r->proyek,
        ]);

        $cpmkPerMk = $cpmkRows->map(fn($rows) => $rows->map(fn($r) => [
            'kode_cpmk'     => $r->kode,
            'deskripsi_cpmk' => $r->deskripsi,
            'rata_rata_nilai' => 0,
            'persen_lulus'  => 0,
            'target_capaian' => 70,
            'keterangan'    => '',
        ])->values());

        $cplPerMk = $cplRows->map(fn($rows) => $rows->map(fn($r) => [
            'kode_cpl'  => $r->kode,
            'nilai_cpl' => 0,
            'target_cpl' => 70,
            'keterangan' => '',
        ])->values());

        return view(
            'evaluasi.laporan.create',
            compact('mataKuliahs', 'taAktif', 'bobotPerMk', 'cpmkPerMk', 'cplPerMk')
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'mata_kuliah_id'  => 'required|exists:mata_kuliah,id',
            'semester'        => 'required|string',
            'tahun_akademik'  => 'required|string',
        ]);

        DB::transaction(function () use ($request) {
            $laporan = LaporanEvaluasi::create([
                'mata_kuliah_id'  => $request->mata_kuliah_id,
                'semester'        => $request->semester,
                'tahun_akademik'  => $request->tahun_akademik,
                'kelas'           => $request->kelas,
                'jumlah_mahasiswa' => $request->jumlah_mahasiswa ?? 0,
                'dosen_pjmk'     => $request->dosen_pjmk,
                'status'          => $request->status ?? 'draft',
                'catatan_umum'    => $request->catatan_umum,
            ]);

            $this->syncChildren($laporan, $request);
        });

        return redirect()->route('laporan-evaluasi.index')
            ->with('success', 'Laporan evaluasi berhasil disimpan.');
    }

    public function show($id)
    {
        $laporan = LaporanEvaluasi::with([
            'mataKuliah',
            'komponenNilai',
            'cpmks',
            'cpls',
            'distribusiNilai',
            'hambatans',
            'tindakLanjuts',
        ])->findOrFail($id);

        if (Auth::user()->role !== 'admin' && !$laporan->mataKuliah) {
            abort(403, 'Anda tidak memiliki akses ke laporan evaluasi ini.');
        }

        return view('evaluasi.laporan.show', compact('laporan'));
    }

    public function edit($id)
    {
        // Hanya admin/kaprodi/akademik yang boleh edit laporan
        $role = Auth::user()->role ?? '';
        if (!in_array($role, ['admin', 'kaprodi', 'akademik'])) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit laporan evaluasi.');
        }

        $laporan = LaporanEvaluasi::with([
            'mataKuliah',
            'komponenNilai',
            'cpmks',
            'cpls',
            'distribusiNilai',
            'hambatans',
            'tindakLanjuts',
        ])->findOrFail($id);

        if (Auth::user()->role !== 'admin' && !$laporan->mataKuliah) {
            abort(403, 'Anda tidak memiliki akses ke laporan evaluasi ini.');
        }

        $mataKuliahs = MataKuliah::orderBy('semester')->orderBy('kode')->get();

        return view('evaluasi.laporan.create', compact('laporan', 'mataKuliahs'));
    }

    public function update(Request $request, $id)
    {
        // Hanya admin/kaprodi/akademik yang boleh update laporan
        $role = Auth::user()->role ?? '';
        if (!in_array($role, ['admin', 'kaprodi', 'akademik'])) {
            abort(403, 'Anda tidak memiliki akses untuk memperbarui laporan evaluasi.');
        }

        $laporan = LaporanEvaluasi::findOrFail($id);

        if (Auth::user()->role !== 'admin' && !$laporan->mataKuliah) {
            abort(403, 'Anda tidak memiliki akses ke laporan evaluasi ini.');
        }

        $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
            'semester'       => 'required|string',
            'tahun_akademik' => 'required|string',
        ]);

        DB::transaction(function () use ($request, $laporan) {
            $laporan->update([
                'mata_kuliah_id'  => $request->mata_kuliah_id,
                'semester'        => $request->semester,
                'tahun_akademik'  => $request->tahun_akademik,
                'kelas'           => $request->kelas,
                'jumlah_mahasiswa' => $request->jumlah_mahasiswa ?? 0,
                'dosen_pjmk'     => $request->dosen_pjmk,
                'status'          => $request->status ?? 'draft',
                'catatan_umum'    => $request->catatan_umum,
            ]);

            // delete old children
            $laporan->komponenNilai()->delete();
            $laporan->cpmks()->delete();
            $laporan->cpls()->delete();
            $laporan->distribusiNilai()->delete();
            $laporan->hambatans()->delete();
            $laporan->tindakLanjuts()->delete();

            $this->syncChildren($laporan, $request);
        });

        return redirect()->route('laporan-evaluasi.show', $laporan->id)
            ->with('success', 'Laporan evaluasi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        // Hanya admin/kaprodi yang boleh hapus laporan
        $role = Auth::user()->role ?? '';
        if (!in_array($role, ['admin', 'kaprodi'])) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus laporan evaluasi.');
        }

        $laporan = LaporanEvaluasi::findOrFail($id);
        if (Auth::user()->role !== 'admin' && !$laporan->mataKuliah) {
            abort(403, 'Anda tidak memiliki akses ke laporan evaluasi ini.');
        }
        $laporan->delete();
        return redirect()->route('laporan-evaluasi.index')
            ->with('success', 'Laporan evaluasi berhasil dihapus.');
    }

    // ── Helper: sync child records ────────────────────────────────
    private function syncChildren(LaporanEvaluasi $laporan, Request $request): void
    {
        // Tab 2: Komponen Nilai
        foreach ((array) $request->komponen_nilai as $row) {
            if (empty($row['komponen'])) continue;
            EvaluasiKomponenNilai::create([
                'laporan_evaluasi_id' => $laporan->id,
                'komponen'    => $row['komponen'],
                'bobot_persen' => $row['bobot_persen'] ?? 0,
                'rata_rata'   => $row['rata_rata'] ?? 0,
                'nilai_min'   => $row['nilai_min'] ?? 0,
                'nilai_max'   => $row['nilai_max'] ?? 0,
                'std_deviasi' => $row['std_deviasi'] ?? 0,
            ]);
        }

        // Tab 3: CPMK
        foreach ((array) $request->eval_cpmk as $i => $row) {
            if (empty($row['kode_cpmk'])) continue;
            $rata  = (float)($row['rata_rata_nilai'] ?? 0);
            $target = (float)($row['target_capaian'] ?? 70);
            EvaluasiCpmk::create([
                'laporan_evaluasi_id' => $laporan->id,
                'kode_cpmk'     => $row['kode_cpmk'],
                'deskripsi_cpmk' => $row['deskripsi_cpmk'] ?? null,
                'rata_rata_nilai' => $rata,
                'persen_lulus'  => $row['persen_lulus'] ?? 0,
                'target_capaian' => $target,
                'tercapai'      => $rata >= $target,
                'keterangan'    => $row['keterangan'] ?? null,
            ]);
        }

        // Tab 4: CPL
        foreach ((array) $request->eval_cpl as $row) {
            if (empty($row['kode_cpl'])) continue;
            $nilai  = (float)($row['nilai_cpl'] ?? 0);
            $target = (float)($row['target_cpl'] ?? 70);
            EvaluasiCpl::create([
                'laporan_evaluasi_id' => $laporan->id,
                'kode_cpl'   => $row['kode_cpl'],
                'nilai_cpl'  => $nilai,
                'target_cpl' => $target,
                'tercapai'   => $nilai >= $target,
                'gap'        => round($nilai - $target, 2),
                'keterangan' => $row['keterangan'] ?? null,
            ]);
        }

        // Tab 5: Distribusi
        if ($request->has('distribusi')) {
            $d = $request->distribusi;
            $lulus    = (int)($d['jumlah_lulus'] ?? 0);
            $tdk      = (int)($d['jumlah_tidak_lulus'] ?? 0);
            $total    = $lulus + $tdk;
            $persen   = $total > 0 ? round($lulus / $total * 100, 2) : 0;
            EvaluasiDistribusiNilai::create([
                'laporan_evaluasi_id' => $laporan->id,
                'jumlah_lulus'       => $lulus,
                'jumlah_tidak_lulus' => $tdk,
                'persen_lulus'       => $persen,
                'rata_rata_final'    => $d['rata_rata_final'] ?? 0,
                'jml_a' => $d['jml_a'] ?? 0,
                'jml_b' => $d['jml_b'] ?? 0,
                'jml_c' => $d['jml_c'] ?? 0,
                'jml_d' => $d['jml_d'] ?? 0,
                'jml_e' => $d['jml_e'] ?? 0,
            ]);
        }

        // Tab 6: Hambatan
        foreach ((array) $request->hambatan as $i => $row) {
            if (empty($row['deskripsi'])) continue;
            EvaluasiHambatan::create([
                'laporan_evaluasi_id' => $laporan->id,
                'no_urut'        => $i + 1,
                'jenis_hambatan' => $row['jenis_hambatan'] ?? 'lainnya',
                'deskripsi'      => $row['deskripsi'],
                'solusi_usulan'  => $row['solusi_usulan'] ?? null,
            ]);
        }

        // Tab 7: Tindak Lanjut
        foreach ((array) $request->tindak_lanjut as $i => $row) {
            if (empty($row['permasalahan'])) continue;
            EvaluasiTindakLanjut::create([
                'laporan_evaluasi_id' => $laporan->id,
                'no_urut'           => $i + 1,
                'aspek'             => $row['aspek'] ?? 'lainnya',
                'permasalahan'      => $row['permasalahan'],
                'rekomendasi'       => $row['rekomendasi'] ?? '',
                'penanggung_jawab'  => $row['penanggung_jawab'] ?? null,
                'target_semester'   => $row['target_semester'] ?? null,
            ]);
        }
    }

    // ── Export Word (.docx via ZipArchive) ────────────────────────
    public function exportWord($id)
    {
        $laporan = LaporanEvaluasi::with([
            'mataKuliah',
            'komponenNilai',
            'cpmks',
            'cpls',
            'distribusiNilai',
            'hambatans',
            'tindakLanjuts',
        ])->findOrFail($id);

        if (Auth::user()->role !== 'admin' && !$laporan->mataKuliah) {
            abort(403, 'Anda tidak memiliki akses ke laporan evaluasi ini.');
        }

        $mk = $laporan->mataKuliah;

        // Build word/document.xml content
        $body = '';

        // Helper closures
        $esc  = fn($s) => htmlspecialchars((string)$s, ENT_XML1, 'UTF-8');
        $bold = fn($s) => "<w:r><w:rPr><w:b/></w:rPr><w:t xml:space=\"preserve\">{$esc($s)}</w:t></w:r>";
        $cell = function ($text, $bold = false, $width = null, $shade = null) use ($esc) {
            $shadeXml = $shade ? "<w:shd w:val=\"clear\" w:color=\"auto\" w:fill=\"{$shade}\"/>" : '';
            $widthXml = $width ? "<w:tcW w:w=\"{$width}\" w:type=\"dxa\"/>" : '';
            $boldOpen = $bold ? '<w:b/>' : '';
            return "<w:tc><w:tcPr>{$widthXml}{$shadeXml}</w:tcPr><w:p><w:r><w:rPr>{$boldOpen}</w:rPr><w:t xml:space=\"preserve\">{$esc($text)}</w:t></w:r></w:p></w:tc>";
        };

        $heading = function ($text) use ($esc) {
            return "<w:p><w:pPr><w:pStyle w:val=\"Heading2\"/></w:pPr><w:r><w:t>{$esc($text)}</w:t></w:r></w:p>";
        };

        $tblStart = '<w:tbl><w:tblPr><w:tblW w:w="9072" w:type="dxa"/><w:tblBorders>'
            . '<w:top w:val="single" w:sz="4" w:space="0" w:color="4472C4"/>'
            . '<w:left w:val="single" w:sz="4" w:space="0" w:color="4472C4"/>'
            . '<w:bottom w:val="single" w:sz="4" w:space="0" w:color="4472C4"/>'
            . '<w:right w:val="single" w:sz="4" w:space="0" w:color="4472C4"/>'
            . '<w:insideH w:val="single" w:sz="4" w:space="0" w:color="BBBBBB"/>'
            . '<w:insideV w:val="single" w:sz="4" w:space="0" w:color="BBBBBB"/>'
            . '</w:tblBorders></w:tblPr>';
        $tblEnd = '</w:tbl>';

        $tr = fn($cells) => '<w:tr>' . implode('', $cells) . '</w:tr>';

        // ── TITLE ────────────────────────────────────────────────
        $body .= '<w:p><w:pPr><w:jc w:val="center"/></w:pPr>'
            . '<w:r><w:rPr><w:b/><w:sz w:val="28"/><w:color w:val="1D4ED8"/></w:rPr>'
            . '<w:t>LAPORAN EVALUASI KETERCAPAIAN PEMBELAJARAN</w:t></w:r></w:p>';
        $body .= '<w:p><w:pPr><w:jc w:val="center"/></w:pPr>'
            . '<w:r><w:rPr><w:sz w:val="22"/></w:rPr>'
            . "<w:t xml:space=\"preserve\">{$esc($mk->nama ?? '')} — {$esc($laporan->semester)} {$esc($laporan->tahun_akademik)}</w:t>"
            . '</w:r></w:p>';
        $body .= '<w:p/>';

        // ── TAB 1: Identitas ─────────────────────────────────────
        $body .= $heading('1. Identitas Mata Kuliah');
        $body .= $tblStart;
        $rows1 = [
            ['Kode MK', $mk->kode ?? '-'],
            ['Nama MK', $mk->nama ?? '-'],
            ['SKS', $mk->sks ?? '-'],
            ['Semester', $laporan->semester . ' ' . $laporan->tahun_akademik],
            ['Kelas', $laporan->kelas ?? '-'],
            ['Jumlah Mahasiswa', $laporan->jumlah_mahasiswa],
            ['Dosen PJMK', $laporan->dosen_pjmk ?? '-'],
            ['Status', strtoupper($laporan->status)],
        ];
        foreach ($rows1 as [$label, $val]) {
            $body .= $tr([$cell($label, true, 3000, 'E8F0FE'), $cell($val, false, 6072)]);
        }
        $body .= $tblEnd . '<w:p/>';

        // ── TAB 2: Komponen Nilai ────────────────────────────────
        $body .= $heading('2. Rekapitulasi Komponen Nilai');
        $body .= $tblStart;
        $body .= $tr([
            $cell('Komponen', true, 2000, '1D4ED8'),
            $cell('Bobot (%)', true, 1200, '1D4ED8'),
            $cell('Rata-rata', true, 1200, '1D4ED8'),
            $cell('Nilai Min', true, 1200, '1D4ED8'),
            $cell('Nilai Maks', true, 1200, '1D4ED8'),
            $cell('Std. Deviasi', true, 1400, '1D4ED8'),
        ]);
        foreach ($laporan->komponenNilai as $k) {
            $body .= $tr([
                $cell(ucfirst($k->komponen)),
                $cell($k->bobot_persen),
                $cell($k->rata_rata),
                $cell($k->nilai_min),
                $cell($k->nilai_max),
                $cell($k->std_deviasi),
            ]);
        }
        $body .= $tblEnd . '<w:p/>';

        // ── TAB 3: CPMK ─────────────────────────────────────────
        $body .= $heading('3. Rekapitulasi Ketercapaian CPMK');
        $body .= $tblStart;
        $body .= $tr([
            $cell('Kode CPMK', true, 1200, '1D4ED8'),
            $cell('Deskripsi', true, 2800, '1D4ED8'),
            $cell('Rata-rata', true, 1000, '1D4ED8'),
            $cell('% Lulus', true, 1000, '1D4ED8'),
            $cell('Target (%)', true, 1000, '1D4ED8'),
            $cell('Tercapai', true, 1000, '1D4ED8'),
            $cell('Keterangan', true, 1072, '1D4ED8'),
        ]);
        foreach ($laporan->cpmks as $c) {
            $body .= $tr([
                $cell($c->kode_cpmk),
                $cell($c->deskripsi_cpmk ?? '-'),
                $cell($c->rata_rata_nilai),
                $cell($c->persen_lulus . '%'),
                $cell($c->target_capaian . '%'),
                $cell($c->tercapai ? '✓ Ya' : '✗ Tidak'),
                $cell($c->keterangan ?? '-'),
            ]);
        }
        $body .= $tblEnd . '<w:p/>';

        // ── TAB 4: CPL ───────────────────────────────────────────
        $body .= $heading('4. Ketercapaian CPL');
        $body .= $tblStart;
        $body .= $tr([
            $cell('Kode CPL', true, 1200, '1D4ED8'),
            $cell('Nilai CPL', true, 1400, '1D4ED8'),
            $cell('Target (%)', true, 1400, '1D4ED8'),
            $cell('Gap', true, 1000, '1D4ED8'),
            $cell('Tercapai', true, 1200, '1D4ED8'),
            $cell('Keterangan', true, 2872, '1D4ED8'),
        ]);
        foreach ($laporan->cpls as $c) {
            $body .= $tr([
                $cell($c->kode_cpl),
                $cell($c->nilai_cpl),
                $cell($c->target_cpl . '%'),
                $cell($c->gap),
                $cell($c->tercapai ? '✓ Ya' : '✗ Tidak'),
                $cell($c->keterangan ?? '-'),
            ]);
        }
        $body .= $tblEnd . '<w:p/>';

        // ── TAB 5: Distribusi ────────────────────────────────────
        $body .= $heading('5. Distribusi Nilai & Kelulusan');
        $dist = $laporan->distribusiNilai;
        if ($dist) {
            $body .= $tblStart;
            $rows5 = [
                ['Jumlah Lulus',       $dist->jumlah_lulus],
                ['Jumlah Tidak Lulus', $dist->jumlah_tidak_lulus],
                ['% Lulus',            $dist->persen_lulus . '%'],
                ['Rata-rata Final',    $dist->rata_rata_final],
                ['Nilai A (≥80)',      $dist->jml_a . ' mahasiswa'],
                ['Nilai B (70-79)',    $dist->jml_b . ' mahasiswa'],
                ['Nilai C (60-69)',    $dist->jml_c . ' mahasiswa'],
                ['Nilai D (50-59)',    $dist->jml_d . ' mahasiswa'],
                ['Nilai E (<50)',      $dist->jml_e . ' mahasiswa'],
            ];
            foreach ($rows5 as [$label, $val]) {
                $body .= $tr([$cell($label, true, 3000, 'E8F0FE'), $cell($val, false, 6072)]);
            }
            $body .= $tblEnd;
        }
        $body .= '<w:p/>';

        // ── TAB 6: Hambatan ──────────────────────────────────────
        $body .= $heading('6. Hambatan & Permasalahan');
        $body .= $tblStart;
        $body .= $tr([
            $cell('No', true, 500, '1D4ED8'),
            $cell('Jenis Hambatan', true, 2000, '1D4ED8'),
            $cell('Deskripsi', true, 3500, '1D4ED8'),
            $cell('Solusi/Usulan', true, 3072, '1D4ED8'),
        ]);
        foreach ($laporan->hambatans as $h) {
            $body .= $tr([
                $cell($h->no_urut),
                $cell(ucfirst($h->jenis_hambatan)),
                $cell($h->deskripsi),
                $cell($h->solusi_usulan ?? '-'),
            ]);
        }
        $body .= $tblEnd . '<w:p/>';

        // ── TAB 7: Tindak Lanjut ─────────────────────────────────
        $body .= $heading('7. Rekomendasi & Tindak Lanjut');
        $body .= $tblStart;
        $body .= $tr([
            $cell('No', true, 400, '1D4ED8'),
            $cell('Aspek', true, 1200, '1D4ED8'),
            $cell('Permasalahan', true, 2000, '1D4ED8'),
            $cell('Rekomendasi', true, 2000, '1D4ED8'),
            $cell('Penanggung Jawab', true, 1700, '1D4ED8'),
            $cell('Target Semester', true, 1772, '1D4ED8'),
        ]);
        foreach ($laporan->tindakLanjuts as $t) {
            $body .= $tr([
                $cell($t->no_urut),
                $cell(ucfirst($t->aspek)),
                $cell($t->permasalahan),
                $cell($t->rekomendasi),
                $cell($t->penanggung_jawab ?? '-'),
                $cell($t->target_semester ?? '-'),
            ]);
        }
        $body .= $tblEnd . '<w:p/>';

        // ── Catatan Umum ─────────────────────────────────────────
        if ($laporan->catatan_umum) {
            $body .= $heading('Catatan Umum');
            $body .= '<w:p><w:r><w:t xml:space="preserve">' . $esc($laporan->catatan_umum) . '</w:t></w:r></w:p>';
        }

        // ── Build .docx ──────────────────────────────────────────
        $docXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:document xmlns:wpc="http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas" '
            . 'xmlns:cx="http://schemas.microsoft.com/office/drawing/2014/chartex" '
            . 'xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" '
            . 'xmlns:aink="http://schemas.microsoft.com/office/drawing/2016/ink" '
            . 'xmlns:am3d="http://schemas.microsoft.com/office/drawing/2017/model3d" '
            . 'xmlns:o="urn:schemas-microsoft-com:office:office" '
            . 'xmlns:oel="http://schemas.microsoft.com/office/2019/extlst" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" '
            . 'xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math" '
            . 'xmlns:v="urn:schemas-microsoft-com:vml" '
            . 'xmlns:wp14="http://schemas.microsoft.com/office/word/2010/wordprocessingDrawing" '
            . 'xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" '
            . 'xmlns:w10="urn:schemas-microsoft-com:office:word" '
            . 'xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" '
            . 'xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml" '
            . 'xmlns:w15="http://schemas.microsoft.com/office/word/2012/wordml" '
            . 'xmlns:w16cex="http://schemas.microsoft.com/office/word/2018/wordml/cex" '
            . 'xmlns:w16cid="http://schemas.microsoft.com/office/word/2016/wordml/cid" '
            . 'xmlns:w16="http://schemas.microsoft.com/office/word/2018/wordml" '
            . 'xmlns:w16sdtdh="http://schemas.microsoft.com/office/word/2020/wordml/sdtdatahash" '
            . 'xmlns:w16se="http://schemas.microsoft.com/office/word/2015/wordml/symex" '
            . 'xmlns:wpg="http://schemas.microsoft.com/office/word/2010/wordprocessingGroup" '
            . 'xmlns:wpi="http://schemas.microsoft.com/office/word/2010/wordprocessingInk" '
            . 'xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml" '
            . 'xmlns:wps="http://schemas.microsoft.com/office/word/2010/wordprocessingShape" '
            . 'mc:Ignorable="w14 w15 w16se w16cid w16 w16cex w16sdtdh wp14">'
            . '<w:body>'
            . $body
            . '<w:sectPr>'
            . '<w:pgSz w:w="12240" w:h="15840"/>'
            . '<w:pgMar w:top="1440" w:right="1080" w:bottom="1440" w:left="1080" w:header="708" w:footer="708" w:gutter="0"/>'
            . '</w:sectPr>'
            . '</w:body></w:document>';

        $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" '
            . 'xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml" '
            . 'xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" '
            . 'mc:Ignorable="w14">'
            . '<w:docDefaults><w:rPrDefault><w:rPr>'
            . '<w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/>'
            . '<w:sz w:val="22"/>'
            . '</w:rPr></w:rPrDefault></w:docDefaults>'
            . '<w:style w:type="paragraph" w:styleId="Normal"><w:name w:val="Normal"/></w:style>'
            . '<w:style w:type="paragraph" w:styleId="Heading2">'
            . '<w:name w:val="heading 2"/>'
            . '<w:pPr><w:spacing w:before="200" w:after="60"/></w:pPr>'
            . '<w:rPr><w:b/><w:color w:val="1D4ED8"/><w:sz w:val="24"/></w:rPr>'
            . '</w:style>'
            . '</w:styles>';

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            . '<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
            . '</Types>';

        $relsMain = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            . '</Relationships>';

        $relsDoc = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';

        $tmpPath = storage_path('app/laporan_evaluasi_' . $laporan->id . '_' . time() . '.docx');

        $zip = new \ZipArchive();
        if ($zip->open($tmpPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Gagal membuat file docx.');
        }
        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', $relsMain);
        $zip->addFromString('word/_rels/document.xml.rels', $relsDoc);
        $zip->addFromString('word/document.xml', $docXml);
        $zip->addFromString('word/styles.xml', $stylesXml);
        $zip->close();

        $filename = 'Laporan_Evaluasi_' . ($mk->kode ?? 'MK') . '_' . $laporan->tahun_akademik . '.docx';
        $filename = str_replace(['/', '\\', ' '], '_', $filename);

        return response()->streamDownload(function () use ($tmpPath) {
            readfile($tmpPath);
            @unlink($tmpPath);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }
}

