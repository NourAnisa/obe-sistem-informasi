<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Main portfolio table
        Schema::create('student_portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->unique()->constrained('mahasiswas')->onDelete('cascade');
            $table->string('semester_aktif')->default('2025/2026');
            $table->decimal('completion_percentage', 5, 2)->default(0);
            $table->enum('status', ['draft', 'active', 'completed'])->default('active');
            $table->text('description')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('mahasiswa_id');
            $table->index('semester_aktif');
            $table->index('status');
        });

        // Evidence/Artifact table
        Schema::create('portfolio_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_portfolio_id')->constrained('student_portfolios')->onDelete('cascade');
            $table->foreignId('sub_cpmk_id')->nullable()->constrained('sub_cpmk')->onDelete('set null');
            $table->enum('evidence_type', ['assignment', 'project', 'certification', 'reflection', 'peer_feedback'])->default('assignment');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_mime_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->text('reflection_notes')->nullable();
            $table->tinyInteger('quality_rating')->nullable()->comment('1-5 scale');
            $table->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'rejected'])->default('draft');
            $table->text('dosen_feedback')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->string('external_link')->nullable()->comment('Link to Google Drive, GitHub repo, etc');
            $table->timestamps();

            $table->index('student_portfolio_id');
            $table->index('sub_cpmk_id');
            $table->index('evidence_type');
            $table->index('status');
            $table->index('reviewed_by');
        });

        // Evidence-to-CPMK mapping (many-to-many)
        Schema::create('evidence_cpmk_mapping', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evidence_id')->constrained('portfolio_evidences')->onDelete('cascade');
            $table->foreignId('cpmk_id')->constrained('cpmk')->onDelete('cascade');
            $table->tinyInteger('demonstration_level')->nullable()->comment('1-5 scale: how well does this evidence demonstrate CPMK');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['evidence_id', 'cpmk_id']);
            $table->index('cpmk_id');
        });

        // Portfolio progress tracking history
        Schema::create('portfolio_progress_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_portfolio_id')->constrained('student_portfolios')->onDelete('cascade');
            $table->decimal('completion_percentage', 5, 2);
            $table->integer('total_evidences');
            $table->integer('approved_evidences');
            $table->text('notes')->nullable();
            $table->timestamp('logged_at')->useCurrent();

            $table->index('student_portfolio_id');
            $table->index('logged_at');
        });

        // Portfolio views tracking (for analytics)
        Schema::create('portfolio_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_portfolio_id')->constrained('student_portfolios')->onDelete('cascade');
            $table->foreignId('viewed_by')->constrained('users')->onDelete('cascade');
            $table->enum('viewer_type', ['student', 'dosen', 'admin', 'employer'])->default('dosen');
            $table->timestamp('viewed_at')->useCurrent();

            $table->index('student_portfolio_id');
            $table->index('viewed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_views');
        Schema::dropIfExists('portfolio_progress_logs');
        Schema::dropIfExists('evidence_cpmk_mapping');
        Schema::dropIfExists('portfolio_evidences');
        Schema::dropIfExists('student_portfolios');
    }
};
