<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('building')->nullable();
            $table->string('floor', 10)->nullable();
            $table->unsignedSmallInteger('capacity')->default(30);
            $table->enum('type', ['class', 'lab', 'aula', 'online', 'field'])->default('class');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('room_block_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->enum('day_of_week', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('course_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->cascadeOnDelete();
            $table->string('class_name', 10)->nullable();         // e.g. A, B, reguler
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->enum('day_of_week', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']);
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedTinyInteger('semester');
            $table->string('academic_year', 20);                  // e.g. 2025/2026
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_schedules');
        Schema::dropIfExists('room_block_rules');
        Schema::dropIfExists('rooms');
    }
};
