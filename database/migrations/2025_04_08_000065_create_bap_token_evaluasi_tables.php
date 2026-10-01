<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── bap_tokens ─────────────────────────────────────
        if (!Schema::hasTable('bap_tokens')) {
            Schema::create('bap_tokens', function (Blueprint $table) {
                $table->id();
                $table->foreignId('bap_pertemuan_id')->constrained('bap_pertemuan')->cascadeOnDelete();
                $table->string('token', 8);
                $table->timestamp('expired_at');
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->index('bap_pertemuan_id');
                $table->index('token');
            });
        }

        // ── bap_evaluasi_mahasiswa ─────────────────────────
        if (!Schema::hasTable('bap_evaluasi_mahasiswa')) {
            Schema::create('bap_evaluasi_mahasiswa', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
                $table->foreignId('bap_pertemuan_id')->constrained('bap_pertemuan')->cascadeOnDelete();

                // 4 competency avg scores (1.0 – 3.0)
                $table->decimal('pedagogik',   4, 2)->default(0);
                $table->decimal('profesional', 4, 2)->default(0);
                $table->decimal('kepribadian', 4, 2)->default(0);
                $table->decimal('sosial',      4, 2)->default(0);

                $table->string('token_input', 8)->nullable(); // token entered by student
                $table->boolean('hadir')->default(false);     // true if token matched

                $table->timestamp('created_at')->useCurrent();

                $table->unique(['mahasiswa_id', 'bap_pertemuan_id'], 'bap_eval_unique');
                $table->index(['bap_pertemuan_id', 'hadir']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bap_evaluasi_mahasiswa');
        Schema::dropIfExists('bap_tokens');
    }
};
