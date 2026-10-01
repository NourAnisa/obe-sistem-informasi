<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // cpl_target — configurable threshold/target per CPL
        if (!Schema::hasTable('cpl_target')) {
            Schema::create('cpl_target', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cpl_id')->constrained('cpl')->cascadeOnDelete();
                $table->decimal('target_pct', 5, 2)->default(60.00); // % mahasiswa harus lulus
                $table->decimal('threshold', 5, 2)->default(56.00);  // nilai minimum lulus
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->unique('cpl_id');
            });
        }

        // cpmk_achievement — nilai CPMK per mahasiswa per MK per semester
        if (!Schema::hasTable('cpmk_achievement')) {
            Schema::create('cpmk_achievement', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
                $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->cascadeOnDelete();
                $table->foreignId('cpmk_id')->constrained('cpmk')->cascadeOnDelete();
                $table->string('semester_aktif', 20);
                $table->year('angkatan')->nullable();
                // Komponen nilai (disimpan untuk audit trail)
                $table->decimal('nilai_tugas', 5, 2)->default(0);
                $table->decimal('nilai_uts', 5, 2)->default(0);
                $table->decimal('nilai_uas', 5, 2)->default(0);
                $table->decimal('nilai_partisipatif', 5, 2)->default(0);
                $table->decimal('nilai_proyek', 5, 2)->default(0);
                // Hasil kalkulasi
                $table->decimal('nilai_cpmk', 5, 2)->default(0); // weighted avg
                $table->decimal('threshold', 5, 2)->default(56);
                $table->tinyInteger('achieved')->default(0);       // 1=tercapai, 0=belum
                $table->timestamps();

                $table->unique(['mahasiswa_id', 'mata_kuliah_id', 'cpmk_id', 'semester_aktif'], 'cpmk_ach_unique');
            });
        }

        // cpl_achievement — rata-rata CPL per mahasiswa per semester
        if (!Schema::hasTable('cpl_achievement')) {
            Schema::create('cpl_achievement', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
                $table->foreignId('cpl_id')->constrained('cpl')->cascadeOnDelete();
                $table->string('semester_aktif', 20);
                $table->string('tahun_akademik', 20)->nullable();
                $table->year('angkatan')->nullable();
                $table->decimal('nilai_cpl', 5, 2)->default(0);    // avg of CPMK values
                $table->tinyInteger('achieved')->default(0);
                $table->decimal('threshold', 5, 2)->default(56);
                $table->unsignedSmallInteger('jumlah_cpmk')->default(0);
                $table->unsignedSmallInteger('jumlah_achieved')->default(0);
                $table->timestamps();

                $table->unique(['mahasiswa_id', 'cpl_id', 'semester_aktif'], 'cpl_ach_unique');
            });
        }

        // evaluasi_cohort — agregasi capaian CPL per angkatan
        if (!Schema::hasTable('evaluasi_cohort')) {
            Schema::create('evaluasi_cohort', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cpl_id')->constrained('cpl')->cascadeOnDelete();
                $table->year('angkatan');
                $table->string('tahun_akademik', 20)->nullable();
                $table->tinyInteger('semester_ke')->nullable();
                $table->unsignedSmallInteger('total_mahasiswa')->default(0);
                $table->unsignedSmallInteger('jumlah_tercapai')->default(0);
                $table->decimal('rata_nilai_cpl', 5, 2)->default(0);
                $table->decimal('pct_lulus', 5, 2)->default(0);
                $table->decimal('target_capaian', 5, 2)->default(60);
                $table->enum('status_target', ['tercapai', 'belum_tercapai', 'perlu_monitoring'])->default('belum_tercapai');
                $table->text('catatan')->nullable();
                $table->timestamps();

                $table->unique(['cpl_id', 'angkatan', 'tahun_akademik'], 'eval_cohort_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluasi_cohort');
        Schema::dropIfExists('cpl_achievement');
        Schema::dropIfExists('cpmk_achievement');
        Schema::dropIfExists('cpl_target');
    }
};
