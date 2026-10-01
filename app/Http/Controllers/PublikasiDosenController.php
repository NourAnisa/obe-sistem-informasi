<?php

namespace App\Http\Controllers;

use App\Exports\PublikasiExport;
use App\Models\PublikasiDosen;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;

class PublikasiDosenController extends Controller
{
    /**
     * Sync publikasi dari Google Scholar via SerpAPI.
     */
    public function syncScholar(int $dosenId)
    {
        // Dosen hanya boleh sync publikasi sendiri
        if ((Auth::user()->role ?? '') === 'dosen' && Auth::id() !== $dosenId) {
            abort(403, 'Anda hanya dapat mensinkronisasi publikasi sendiri.');
        }

        $dosen = User::findOrFail($dosenId);

        if (empty($dosen->google_scholar_id)) {
            return back()->with('error', 'Google Scholar ID belum diisi untuk dosen ini.');
        }

        $apiKey = env('SERPAPI_KEY');
        if (empty($apiKey)) {
            return back()->with('error', 'SERPAPI_KEY belum dikonfigurasi di .env');
        }

        try {
            $response = Http::timeout(30)->get('https://serpapi.com/search.json', [
                'engine'    => 'google_scholar_author',
                'author_id' => $dosen->google_scholar_id,
                'api_key'   => $apiKey,
            ]);

            if (!$response->successful()) {
                return back()->with('error', 'SerpAPI error: ' . $response->status() . ' — ' . ($response->json('error') ?? 'Unknown'));
            }

            $data     = $response->json();
            $articles = $data['articles'] ?? [];
            $synced   = 0;

            foreach ($articles as $article) {
                $serpId = $article['citation_id'] ?? ($article['link'] ?? null);
                if (!$serpId) continue;

                $judul   = $article['title']   ?? 'Tanpa Judul';
                $tahun   = $article['year']     ?? null;
                $authorsRaw = $article['authors'] ?? null;
                $authors = is_array($authorsRaw)
                    ? implode(', ', array_column($authorsRaw, 'name'))
                    : ($authorsRaw ?: null);
                $link    = $article['link']     ?? null;
                $snippet = $article['snippet']  ?? null;
                $sumber  = $article['publication'] ?? null;

                PublikasiDosen::updateOrCreate(
                    ['serpapi_id' => $serpId],
                    [
                        'dosen_id' => $dosen->id,
                        'judul'    => $judul,
                        'tahun'    => $tahun ? (int)$tahun : null,
                        'authors'  => $authors,
                        'sumber'   => $sumber,
                        'link'     => $link,
                        'snippet'  => $snippet,
                        'tipe'     => PublikasiDosen::klasifikasi($judul),
                    ]
                );
                $synced++;
            }

            $dosen->last_sync_at = now();
            $dosen->save();

            return back()->with('success', "✅ Berhasil sync {$synced} publikasi dari Google Scholar.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal sync: ' . $e->getMessage());
        }
    }

    /**
     * Halaman profil dosen + daftar publikasi.
     */
    public function index(int $dosenId)
    {
        $dosen = User::where('role', 'dosen')->findOrFail($dosenId);
        $publikasi = PublikasiDosen::where('dosen_id', $dosenId)
            ->orderByDesc('tahun')->orderBy('judul')
            ->get();

        return view('dosen.publikasi', compact('dosen', 'publikasi'));
    }

    /**
     * Simpan / update Google Scholar ID.
     */
    public function updateScholarId(Request $request, int $dosenId)
    {
        // Dosen hanya boleh update Scholar ID milik sendiri
        if ((Auth::user()->role ?? '') === 'dosen' && Auth::id() !== $dosenId) {
            abort(403, 'Anda hanya dapat mengubah data profil sendiri.');
        }

        $dosen = User::findOrFail($dosenId);
        $request->validate(['google_scholar_id' => 'nullable|string|max:255']);
        $dosen->google_scholar_id = $request->google_scholar_id;
        $dosen->save();
        return back()->with('success', 'Google Scholar ID disimpan.');
    }

    /**
     * Hapus publikasi.
     */
    public function destroy(int $id)
    {
        $pub = PublikasiDosen::findOrFail($id);

        // Dosen hanya boleh hapus publikasi milik sendiri
        if ((Auth::user()->role ?? '') === 'dosen' && Auth::id() !== $pub->dosen_id) {
            abort(403, 'Anda hanya dapat menghapus publikasi sendiri.');
        }

        $dosenId = $pub->dosen_id;
        $pub->delete();
        return back()->with('success', 'Publikasi dihapus.');
    }

    /**
     * Halaman laporan publikasi dengan filter.
     */
    public function laporanPublikasi(Request $request)
    {
        $dosenList = User::where('role', 'dosen')->orderBy('name')->get();
        $tahunList = PublikasiDosen::select('tahun')->distinct()->orderByDesc('tahun')->pluck('tahun');

        $query = PublikasiDosen::with('dosen');

        if ($request->filled('tahun'))    $query->where('tahun', $request->tahun);
        if ($request->filled('dosen_id')) $query->where('dosen_id', $request->dosen_id);
        if ($request->filled('tipe'))     $query->where('tipe', $request->tipe);

        $publikasiList = $query->orderByDesc('tahun')->orderBy('judul')->get();

        return view('laporan.publikasi', compact('dosenList', 'tahunList', 'publikasiList'));
    }

    /**
     * Export Excel laporan publikasi.
     */
    public function exportExcel(Request $request)
    {
        $filters = $request->only('tahun', 'dosen_id', 'tipe');
        return Excel::download(new PublikasiExport($filters), 'laporan_publikasi_dosen.xlsx');
    }

    /**
     * Export PDF laporan publikasi.
     */
    public function exportPdf(Request $request)
    {
        $filters = $request->only('tahun', 'dosen_id', 'tipe');

        $query = PublikasiDosen::with('dosen');
        if (!empty($filters['tahun']))    $query->where('tahun', $filters['tahun']);
        if (!empty($filters['dosen_id'])) $query->where('dosen_id', $filters['dosen_id']);
        if (!empty($filters['tipe']))     $query->where('tipe', $filters['tipe']);

        $publikasiList = $query->orderByDesc('tahun')->orderBy('judul')->get();
        $grouped = $publikasiList->groupBy(fn($p) => $p->dosen?->name ?? 'Tanpa Nama');

        $pdf = Pdf::loadView('laporan.publikasi-pdf', compact('grouped', 'filters'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('laporan_publikasi_dosen.pdf');
    }

    /**
     * Simpan/update referensi publikasi untuk RPS (mata kuliah).
     */
    public function saveRpsPublikasi(Request $request, string $kode)
    {
        $mk = \App\Models\MataKuliah::where('kode', $kode)->firstOrFail();
        $ids = $request->input('publikasi_ids', []);

        DB::table('rps_publikasi')->where('mata_kuliah_id', $mk->id)->delete();
        foreach ($ids as $pubId) {
            DB::table('rps_publikasi')->insert([
                'mata_kuliah_id'     => $mk->id,
                'publikasi_dosen_id' => $pubId,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }

        return $request->ajax() || $request->wantsJson()
            ? response()->json(['ok' => true, 'count' => count($ids)])
            : back()->with('success', 'Referensi publikasi disimpan.');
    }

    /**
     * Update tipe, jenis, dan dokumen bukti publikasi.
     */
    public function update(Request $request, int $id)
    {
        $pub = PublikasiDosen::findOrFail($id);

        $request->validate([
            'tipe'          => 'required|in:penelitian,pengabdian',
            'jenis'         => 'nullable|in:jurnal,prosiding,buku',
            'dokumen_bukti' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        $pub->tipe  = $request->tipe;
        $pub->jenis = $request->jenis ?: null;

        if ($request->hasFile('dokumen_bukti') && $request->file('dokumen_bukti')->isValid()) {
            // Remove old file
            if ($pub->dokumen_bukti && \Illuminate\Support\Facades\Storage::disk('public')->exists($pub->dokumen_bukti)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($pub->dokumen_bukti);
            }
            $path = $request->file('dokumen_bukti')->store('publikasi-bukti', 'public');
            $pub->dokumen_bukti = $path;
        }

        $pub->save();

        return back()->with('success', "Publikasi «{$pub->judul}» berhasil diperbarui.");
    }
}
