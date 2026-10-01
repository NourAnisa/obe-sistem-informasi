<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('laporan_evaluasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mata_kuliah_id');
            $table->foreign('mata_kuliah_id')->references('id')->on('mata_kuliah')->onDelete('cascade');
            $table->string('semester');
            $table->string('tahun_akademik');
            $table->string('kelas')->nullable();
            $table->integer('jumlah_mahasiswa')->default(0);
            $table->string('dosen_pjmk')->nullable();
            $table->enum('status', ['draft', 'final'])->default('draft');
            $table->text('catatan_umum')->nullable();
            $table->timestamps();
        });

        Schema::create('evaluasi_komponen_nilai', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('laporan_evaluasi_id');
            $table->foreign('laporan_evaluasi_id')->references('id')->on('laporan_evaluasi')->onDelete('cascade');
            $table->string('komponen');
            $table->decimal('bobot_persen', 5, 2)->default(0);
            $table->decimal('rata_rata', 5, 2)->default(0);
            $table->decimal('nilai_min', 5, 2)->default(0);
            $table->decimal('nilai_max', 5, 2)->default(0);
            $table->decimal('std_deviasi', 5, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('evaluasi_cpmk', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('laporan_evaluasi_id');
            $table->foreign('laporan_evaluasi_id')->references('id')->on('laporan_evaluasi')->onDelete('cascade');
            $table->unsignedBigInteger('cpmk_id')->nullable();
            $table->string('kode_cpmk');
            $table->text('deskripsi_cpmk')->nullable();
            $table->decimal('rata_rata_nilai', 5, 2)->default(0);
            $table->decimal('persen_lulus', 5, 2)->default(0);
            $table->decimal('target_capaian', 5, 2)->default(70);
            $table->boolean('tercapai')->default(false);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('evaluasi_cpl', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('laporan_evaluasi_id');
            $table->foreign('laporan_evaluasi_id')->references('id')->on('laporan_evaluasi')->onDelete('cascade');
            $table->unsignedBigInteger('cpl_id')->nullable();
            $table->string('kode_cpl');
            $table->decimal('nilai_cpl', 5, 2)->default(0);
            $table->decimal('target_cpl', 5, 2)->default(70);
            $table->boolean('tercapai')->default(false);
            $table->decimal('gap', 6, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('evaluasi_distribusi_nilai', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('laporan_evaluasi_id');
            $table->foreign('laporan_evaluasi_id')->references('id')->on('laporan_evaluasi')->onDelete('cascade');
            $table->integer('jumlah_lulus')->default(0);
            $table->integer('jumlah_tidak_lulus')->default(0);
            $table->decimal('persen_lulus', 5, 2)->default(0);
            $table->decimal('rata_rata_final', 5, 2)->default(0);
            $table->integer('jml_a')->default(0);
            $table->integer('jml_b')->default(0);
            $table->integer('jml_c')->default(0);
            $table->integer('jml_d')->default(0);
            $table->integer('jml_e')->default(0);
            $table->timestamps();
        });

        Schema::create('evaluasi_hambatan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('laporan_evaluasi_id');
            $table->foreign('laporan_evaluasi_id')->references('id')->on('laporan_evaluasi')->onDelete('cascade');
            $table->integer('no_urut')->default(1);
            $table->string('jenis_hambatan');
            $table->text('deskripsi');
            $table->text('solusi_usulan')->nullable();
            $table->timestamps();
        });

        Schema::create('evaluasi_tindak_lanjut', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('laporan_evaluasi_id');
            $table->foreign('laporan_evaluasi_id')->references('id')->on('laporan_evaluasi')->onDelete('cascade');
            $table->integer('no_urut')->default(1);
            $table->string('aspek');
            $table->text('permasalahan');
            $table->text('rekomendasi');
            $table->string('penanggung_jawab')->nullable();
            $table->string('target_semester')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluasi_tindak_lanjut');
        Schema::dropIfExists('evaluasi_hambatan');
        Schema::dropIfExists('evaluasi_distribusi_nilai');
        Schema::dropIfExists('evaluasi_cpl');
        Schema::dropIfExists('evaluasi_cpmk');
        Schema::dropIfExists('evaluasi_komponen_nilai');
        Schema::dropIfExists('laporan_evaluasi');
    }
};
