<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cpmk_rubrics')) {
            Schema::create('cpmk_rubrics', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cpmk_id')->nullable()->constrained('cpmk')->onDelete('cascade')
                    ->comment('NULL = default global rubric for all CPMK');
                $table->enum('level', ['novice', 'developing', 'proficient']);
                $table->unsignedTinyInteger('min_score');
                $table->unsignedTinyInteger('max_score');
                $table->text('deskripsi');
                $table->timestamps();

                $table->index(['cpmk_id', 'level']);
            });
        }

        // Add rubric_level to cpmk_achievement
        if (Schema::hasTable('cpmk_achievement') && !Schema::hasColumn('cpmk_achievement', 'rubric_level')) {
            Schema::table('cpmk_achievement', function (Blueprint $table) {
                $table->string('rubric_level', 20)->nullable()->after('achieved');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cpmk_rubrics');

        if (Schema::hasTable('cpmk_achievement') && Schema::hasColumn('cpmk_achievement', 'rubric_level')) {
            Schema::table('cpmk_achievement', function (Blueprint $table) {
                $table->dropColumn('rubric_level');
            });
        }
    }
};
