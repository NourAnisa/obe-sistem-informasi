<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bap_mahasiswa_evaluasi')) {
            Schema::create('bap_mahasiswa_evaluasi', function (Blueprint $table) {
                $table->id();
                $table->foreignId('bap_pertemuan_id')->constrained('bap_pertemuan')->onDelete('cascade');
                $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->onDelete('cascade');
                $table->enum('kehadiran', ['hadir', 'ijin', 'sakit', 'tk'])->default('hadir');
                $table->text('materi_dirasakan')->nullable()->comment('Materi yang dirasakan mahasiswa');
                $table->unsignedTinyInteger('kesesuaian_materi')->default(3)->comment('1-5: relevansi materi');
                $table->unsignedTinyInteger('kesesuaian_metode')->default(3)->comment('1-5: relevansi metode');
                $table->text('catatan')->nullable();
                $table->timestamps();

                $table->unique(['bap_pertemuan_id', 'mahasiswa_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bap_mahasiswa_evaluasi');
    }
};
