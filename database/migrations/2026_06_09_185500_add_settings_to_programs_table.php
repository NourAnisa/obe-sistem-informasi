<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->string('logo_prodi_path')->nullable();
            $table->string('kaprodi')->nullable();
            $table->string('nik_kaprodi')->nullable();
            $table->string('akreditasi')->nullable();
            $table->integer('sks_total')->default(144);
            $table->integer('total_semester')->default(8);
            $table->text('visi')->nullable();
        });

        // Copy existing global settings to the default program (Sarjana Sistem Informasi)
        try {
            $siProgId = DB::table('programs')
                ->where('nama', 'Sarjana Sistem Informasi')
                ->value('id');

            if ($siProgId) {
                $settings = DB::table('system_settings')->pluck('value', 'key');
                
                DB::table('programs')->where('id', $siProgId)->update([
                    'logo_prodi_path' => $settings['logo_prodi_path'] ?? null,
                    'kaprodi'         => $settings['kaprodi']         ?? null,
                    'nik_kaprodi'     => $settings['nik_kaprodi']     ?? null,
                    'akreditasi'      => $settings['akreditasi']      ?? null,
                    'sks_total'       => (int)($settings['sks_total'] ?? 144),
                    'total_semester'  => (int)($settings['total_semester'] ?? 8),
                    'visi'            => $settings['visi']            ?? null,
                ]);
            }
        } catch (\Throwable $e) {
            // Ignore if settings table doesn't exist yet
        }
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn([
                'logo_prodi_path',
                'kaprodi',
                'nik_kaprodi',
                'akreditasi',
                'sks_total',
                'total_semester',
                'visi'
            ]);
        });
    }
};
