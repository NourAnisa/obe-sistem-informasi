<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BapReminderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $mahasiswaNama,
        public readonly string $mataKuliahNama,
        public readonly string $token,
        public readonly int    $minggu,
        public readonly string $ta,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[OBE] Reminder: Isi Evaluasi Perkuliahan Minggu {$this->minggu}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.obe.bap-reminder');
    }
}
