<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // mahasiswas — created by PHP setup script, now added as proper migration
        if (!Schema::hasTable('mahasiswas')) {
            Schema::create('mahasiswas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('nim', 20)->unique();
                $table->string('nama');
                $table->year('angkatan')->nullable();
                $table->string('prodi')->default('Sistem Informasi');
                $table->boolean('aktif')->default(true);
                $table->foreignId('dosen_pa_id')->nullable()->constrained('users')->nullOnDelete();
                $table->unsignedTinyInteger('semester')->default(1);
                $table->string('tahun_akademik', 20)->nullable();
                $table->decimal('ipk', 4, 2)->default(0);
                $table->decimal('ips', 4, 2)->default(0);
                $table->timestamps();
            });
        }

        // mahasiswa_mk — enrollment pivot
        if (!Schema::hasTable('mahasiswa_mk')) {
            Schema::create('mahasiswa_mk', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
                $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->cascadeOnDelete();
                $table->string('semester_aktif', 20);
                $table->boolean('is_pjmk')->default(false);
                $table->enum('status', ['draft', 'diajukan', 'disetujui', 'ditolak'])->default('draft');
                $table->text('catatan_pa')->nullable();
                $table->timestamps();

                $table->unique(['mahasiswa_id', 'mata_kuliah_id', 'semester_aktif']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa_mk');
        Schema::dropIfExists('mahasiswas');
    }
};
