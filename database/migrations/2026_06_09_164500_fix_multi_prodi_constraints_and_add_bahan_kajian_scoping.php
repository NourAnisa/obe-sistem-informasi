<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add program_id to bahan_kajian
        Schema::table('bahan_kajian', function (Blueprint $table) {
            $table->foreignId('program_id')->nullable()->after('id')->constrained('programs')->nullOnDelete();
        });

        // Set existing bahan_kajian to Sistem Informasi program study
        $siProgId = DB::table('programs')->where('nama', 'Sarjana Sistem Informasi')->value('id');
        if ($siProgId) {
            DB::table('bahan_kajian')->update(['program_id' => $siProgId]);
        }

        // 2. Adjust uniqueness constraints for multi-prodi
        if (DB::getDriverName() === 'mysql') {
            Schema::table('cpl', function (Blueprint $table) {
                $table->dropUnique('cpl_kode_unique');
                $table->unique(['program_id', 'kode']);
            });

            Schema::table('cpmk', function (Blueprint $table) {
                $table->dropUnique('cpmk_kode_unique');
                $table->unique(['cpl_id', 'kode']);
            });

            Schema::table('mata_kuliah', function (Blueprint $table) {
                $table->dropUnique('mata_kuliah_kode_unique');
                $table->unique(['program_id', 'kode']);
            });

            Schema::table('bahan_kajian', function (Blueprint $table) {
                $table->dropUnique('bahan_kajian_kode_unique');
                $table->unique(['program_id', 'kode']);
            });
        } else {
            // Safe SQLite handling (ignore drop if it fails, but add new constraint)
            try {
                Schema::table('cpl', function (Blueprint $table) {
                    $table->dropUnique(['kode']);
                });
            } catch (\Exception $e) {}
            try {
                Schema::table('cpl', function (Blueprint $table) {
                    $table->unique(['program_id', 'kode']);
                });
            } catch (\Exception $e) {}

            try {
                Schema::table('cpmk', function (Blueprint $table) {
                    $table->dropUnique(['kode']);
                });
            } catch (\Exception $e) {}
            try {
                Schema::table('cpmk', function (Blueprint $table) {
                    $table->unique(['cpl_id', 'kode']);
                });
            } catch (\Exception $e) {}

            try {
                Schema::table('mata_kuliah', function (Blueprint $table) {
                    $table->dropUnique(['kode']);
                });
            } catch (\Exception $e) {}
            try {
                Schema::table('mata_kuliah', function (Blueprint $table) {
                    $table->unique(['program_id', 'kode']);
                });
            } catch (\Exception $e) {}

            try {
                Schema::table('bahan_kajian', function (Blueprint $table) {
                    $table->dropUnique(['kode']);
                });
            } catch (\Exception $e) {}
            try {
                Schema::table('bahan_kajian', function (Blueprint $table) {
                    $table->unique(['program_id', 'kode']);
                });
            } catch (\Exception $e) {}
        }
    }

    public function down(): void
    {
        // Reverse constraints
        if (DB::getDriverName() === 'mysql') {
            Schema::table('bahan_kajian', function (Blueprint $table) {
                $table->dropUnique(['program_id', 'kode']);
                $table->unique('kode');
            });

            Schema::table('mata_kuliah', function (Blueprint $table) {
                $table->dropUnique(['program_id', 'kode']);
                $table->unique('kode');
            });

            Schema::table('cpmk', function (Blueprint $table) {
                $table->dropUnique(['cpl_id', 'kode']);
                $table->unique('kode');
            });

            Schema::table('cpl', function (Blueprint $table) {
                $table->dropUnique(['program_id', 'kode']);
                $table->unique('kode');
            });
        }

        Schema::table('bahan_kajian', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropColumn('program_id');
        });
    }
};
