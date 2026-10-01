<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // bobot_penilaian — queried by mata_kuliah_id in every grading page
        if (Schema::hasTable('bobot_penilaian') && !$this->hasIndex('bobot_penilaian', 'bobot_penilaian_mata_kuliah_id_index')) {
            Schema::table('bobot_penilaian', function (Blueprint $t) {
                $t->index('mata_kuliah_id');
            });
        }

        // mata_kuliah_cpmk pivot — queried in both directions
        if (Schema::hasTable('mata_kuliah_cpmk')) {
            Schema::table('mata_kuliah_cpmk', function (Blueprint $t) {
                if (!$this->hasIndex('mata_kuliah_cpmk', 'mata_kuliah_cpmk_cpmk_id_index')) {
                    $t->index('cpmk_id');
                }
                if (!$this->hasIndex('mata_kuliah_cpmk', 'mata_kuliah_cpmk_mata_kuliah_id_index')) {
                    $t->index('mata_kuliah_id');
                }
            });
        }

        // mata_kuliah_cpl pivot — queried in both directions
        if (Schema::hasTable('mata_kuliah_cpl')) {
            Schema::table('mata_kuliah_cpl', function (Blueprint $t) {
                if (!$this->hasIndex('mata_kuliah_cpl', 'mata_kuliah_cpl_cpl_id_index')) {
                    $t->index('cpl_id');
                }
                if (!$this->hasIndex('mata_kuliah_cpl', 'mata_kuliah_cpl_mata_kuliah_id_index')) {
                    $t->index('mata_kuliah_id');
                }
            });
        }

        // nilai_sub_cpmk — compound index for grading lookups
        if (Schema::hasTable('nilai_sub_cpmk') && !$this->hasIndex('nilai_sub_cpmk', 'nilai_sub_cpmk_mhs_sub_index')) {
            Schema::table('nilai_sub_cpmk', function (Blueprint $t) {
                $t->index(['mahasiswa_id', 'sub_cpmk_id'], 'nilai_sub_cpmk_mhs_sub_index');
            });
        }

        // nilai_mahasiswa — compound index for TA-based queries
        if (Schema::hasTable('nilai_mahasiswa') && !$this->hasIndex('nilai_mahasiswa', 'nilai_mahasiswa_mk_ta_index')) {
            Schema::table('nilai_mahasiswa', function (Blueprint $t) {
                $t->index(['mata_kuliah_id', 'semester_aktif'], 'nilai_mahasiswa_mk_ta_index');
            });
        }

        // cpmk_achievement — compound index for OBE pipeline lookups
        if (Schema::hasTable('cpmk_achievement') && !$this->hasIndex('cpmk_achievement', 'cpmk_achievement_mk_ta_index')) {
            Schema::table('cpmk_achievement', function (Blueprint $t) {
                $t->index(['mata_kuliah_id', 'semester_aktif'], 'cpmk_achievement_mk_ta_index');
            });
        }

        // cpl_achievement — queried by mahasiswa_id in evaluasi
        if (Schema::hasTable('cpl_achievement') && !$this->hasIndex('cpl_achievement', 'cpl_achievement_mahasiswa_id_index')) {
            Schema::table('cpl_achievement', function (Blueprint $t) {
                $t->index('mahasiswa_id');
            });
        }

        // dosen_mata_kuliah — queried by tahun_akademik + dosen_id
        if (Schema::hasTable('dosen_mata_kuliah') && !$this->hasIndex('dosen_mata_kuliah', 'dosen_mk_dosen_ta_index')) {
            Schema::table('dosen_mata_kuliah', function (Blueprint $t) {
                $t->index(['dosen_id', 'tahun_akademik'], 'dosen_mk_dosen_ta_index');
            });
        }

        // krs_mahasiswa — compound index for TA + status queries
        if (Schema::hasTable('krs_mahasiswa') && !$this->hasIndex('krs_mahasiswa', 'krs_mahasiswa_ta_status_index')) {
            Schema::table('krs_mahasiswa', function (Blueprint $t) {
                $t->index(['tahun_akademik', 'status'], 'krs_mahasiswa_ta_status_index');
            });
        }

        // mahasiswa_mk — compound index for common TA + MK queries
        if (Schema::hasTable('mahasiswa_mk') && !$this->hasIndex('mahasiswa_mk', 'mahasiswa_mk_mk_ta_status_index')) {
            Schema::table('mahasiswa_mk', function (Blueprint $t) {
                $t->index(['mata_kuliah_id', 'semester_aktif', 'status'], 'mahasiswa_mk_mk_ta_status_index');
            });
        }
    }

    public function down(): void
    {
        $drops = [
            'bobot_penilaian' => ['bobot_penilaian_mata_kuliah_id_index'],
            'mata_kuliah_cpmk' => ['mata_kuliah_cpmk_cpmk_id_index', 'mata_kuliah_cpmk_mata_kuliah_id_index'],
            'mata_kuliah_cpl' => ['mata_kuliah_cpl_cpl_id_index', 'mata_kuliah_cpl_mata_kuliah_id_index'],
            'nilai_sub_cpmk' => ['nilai_sub_cpmk_mhs_sub_index'],
            'nilai_mahasiswa' => ['nilai_mahasiswa_mk_ta_index'],
            'cpmk_achievement' => ['cpmk_achievement_mk_ta_index'],
            'cpl_achievement' => ['cpl_achievement_mahasiswa_id_index'],
            'dosen_mata_kuliah' => ['dosen_mk_dosen_ta_index'],
            'krs_mahasiswa' => ['krs_mahasiswa_ta_status_index'],
            'mahasiswa_mk' => ['mahasiswa_mk_mk_ta_status_index'],
        ];

        foreach ($drops as $table => $indexes) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) use ($indexes) {
                    foreach ($indexes as $idx) {
                        try {
                            $t->dropIndex($idx);
                        } catch (\Exception $e) {
                        }
                    }
                });
            }
        }
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        try {
            $indexes = Schema::getIndexes($table);
            foreach ($indexes as $index) {
                if ($index['name'] === $indexName) return true;
            }
        } catch (\Exception $e) {
        }
        return false;
    }
};
