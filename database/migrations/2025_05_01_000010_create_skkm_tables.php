<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skkm_activity_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->unsignedInteger('max_points')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('skkm_point_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_type_id')->constrained('skkm_activity_types')->cascadeOnDelete();
            $table->string('role');
            $table->string('level');
            $table->unsignedInteger('points')->default(0);
            $table->timestamps();
        });

        Schema::create('skkm_mahasiswas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mahasiswa_id');
            $table->unsignedBigInteger('point_rule_id')->nullable();
            $table->string('nama_kegiatan');
            $table->string('kategori')->nullable();
            $table->string('tingkat')->nullable();
            $table->string('prestasi')->nullable();
            $table->string('jenis_anggota')->default('Personal');
            $table->string('semester')->nullable();
            $table->string('tahun_akademik')->nullable();
            $table->unsignedInteger('sks_ekuivalen')->default(0);
            $table->unsignedInteger('points_awarded')->default(0);
            $table->text('keterangan')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('nomor_sk')->nullable();
            $table->date('tanggal_sk')->nullable();
            $table->string('google_drive_link')->nullable();
            $table->string('file_bukti')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->cascadeOnDelete();
            $table->foreign('point_rule_id')->references('id')->on('skkm_point_rules')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skkm_mahasiswas');
        Schema::dropIfExists('skkm_point_rules');
        Schema::dropIfExists('skkm_activity_types');
    }
};
