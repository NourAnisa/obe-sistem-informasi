<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\User;
use App\Models\PublikasiDosen;
use Illuminate\Support\Facades\Http;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Daily auto-sync Google Scholar publikasi ──────────────────────────────
Schedule::call(function () {
    $apiKey = env('SERPAPI_KEY');
    if (!$apiKey) return;

    $dosens = User::where('role', 'dosen')
        ->whereNotNull('google_scholar_id')
        ->where('google_scholar_id', '!=', '')
        ->get();

    foreach ($dosens as $dosen) {
        try {
            $response = Http::timeout(30)->get('https://serpapi.com/search.json', [
                'engine'    => 'google_scholar_author',
                'author_id' => $dosen->google_scholar_id,
                'api_key'   => $apiKey,
            ]);
            if (!$response->successful()) continue;

            $articles = $response->json('articles', []);
            foreach ($articles as $article) {
                $serpId = $article['citation_id'] ?? ($article['link'] ?? null);
                if (!$serpId) continue;
                $judul = $article['title'] ?? 'Tanpa Judul';
                $authorsRaw = $article['authors'] ?? null;
                PublikasiDosen::updateOrCreate(
                    ['serpapi_id' => $serpId],
                    [
                        'dosen_id' => $dosen->id,
                        'judul'    => $judul,
                        'tahun'    => isset($article['year']) ? (int)$article['year'] : null,
                        'authors'  => is_array($authorsRaw) ? implode(', ', array_column($authorsRaw, 'name')) : ($authorsRaw ?: null),
                        'sumber'   => $article['publication'] ?? null,
                        'link'     => $article['link'] ?? null,
                        'snippet'  => $article['snippet'] ?? null,
                        'tipe'     => PublikasiDosen::klasifikasi($judul),
                    ]
                );
            }
            $dosen->last_sync_at = now();
            $dosen->save();
        } catch (\Exception $e) {
            // Silent fail per dosen
        }
    }
})->daily()->name('sync-scholar-publikasi')->withoutOverlapping();

// ── OBE Early Warning — setiap hari Senin pukul 07:00 ────────────────────────
Schedule::command('obe:send-early-warning')
    ->weeklyOn(1, '07:00')
    ->name('obe-early-warning-weekly')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/obe-early-warning.log'));

// ── BAP Reminder — setiap hari pukul 08:00 (hanya jika ada token aktif) ──────
Schedule::command('obe:send-bap-reminder')
    ->dailyAt('08:00')
    ->name('obe-bap-reminder-daily')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/obe-bap-reminder.log'));
