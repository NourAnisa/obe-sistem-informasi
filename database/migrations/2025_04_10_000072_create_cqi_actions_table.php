<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cqi_actions')) {
            Schema::create('cqi_actions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cpl_id')->constrained('cpl')->onDelete('cascade');
                $table->year('angkatan')->nullable();
                $table->string('tahun_akademik', 20)->nullable();
                $table->decimal('nilai_cpl', 5, 2)->default(0);
                $table->decimal('threshold',  5, 2)->default(56);
                $table->text('masalah');
                $table->text('rencana_perbaikan');
                $table->string('pic', 100)->default('Kaprodi');
                $table->string('target_semester', 20)->nullable();
                $table->enum('status', ['open', 'in_progress', 'done'])->default('open');
                $table->timestamps();

                $table->index(['cpl_id', 'angkatan', 'tahun_akademik']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cqi_actions');
    }
};
