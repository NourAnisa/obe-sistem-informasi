<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','dekan','wakildekan','kaprodi','dosen','akademik','kemahasiswaan','mahasiswa','viewer') NULL DEFAULT 'viewer'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','dosen','viewer','kaprodi','kemahasiswaan','akademik','mahasiswa') NULL DEFAULT 'viewer'");
    }
};
