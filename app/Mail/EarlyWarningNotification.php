<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EarlyWarningNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param string $dosenNama     Nama dosen PA yang menerima email
     * @param array  $warningList   List mahasiswa bimbingan yang masuk early warning
     *                              Each item: ['nim', 'nama', 'cpl_kode', 'nilai_cpl', 'threshold', 'gap', 'status']
     * @param string $ta            Tahun akademik (misal "2025/2026")
     */
    public function __construct(
        public readonly string $dosenNama,
        public readonly array  $warningList,
        public readonly string $ta,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[OBE Early Warning] Mahasiswa PA Perlu Perhatian — {$this->ta}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.obe.early-warning',
        );
    }
}
