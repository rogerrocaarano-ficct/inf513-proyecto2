<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const string PHASES = "'Requisitos', 'Evaluación de perfil', 'Pre-defensa', 'Defensa formal', 'Archivo'";

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('graduation_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->foreignId('tutor_id')->nullable()->constrained('teachers')->restrictOnDelete();
            $table->string('proposed_tutor_name', 150)->nullable();
            $table->string('project_title', 300)->nullable();
            $table->string('phase', 30);
            $table->timestamp('started_at');
            $table->timestamp('concluded_at')->nullable();
            $table->timestamp('abandoned_at')->nullable();
            $table->timestamp('archived_at')->nullable();
        });

        DB::statement('ALTER TABLE graduation_processes ADD CONSTRAINT graduation_processes_phase_check CHECK (phase IN ('.self::PHASES.'))');
        DB::statement('ALTER TABLE graduation_processes ADD CONSTRAINT graduation_processes_single_ending CHECK (concluded_at IS NULL OR abandoned_at IS NULL)');
        DB::statement('ALTER TABLE graduation_processes ADD CONSTRAINT graduation_processes_concluded_after_start CHECK (concluded_at IS NULL OR concluded_at > started_at)');
        DB::statement('ALTER TABLE graduation_processes ADD CONSTRAINT graduation_processes_abandoned_after_start CHECK (abandoned_at IS NULL OR abandoned_at > started_at)');
        DB::statement("ALTER TABLE graduation_processes ADD CONSTRAINT graduation_processes_tutor_from_profile_evaluation CHECK (phase = 'Requisitos' OR tutor_id IS NOT NULL)");
        DB::statement("ALTER TABLE graduation_processes ADD CONSTRAINT graduation_processes_archived_in_archive_phase CHECK ((phase = 'Archivo') = (archived_at IS NOT NULL))");

        // UC-001 BR-003: A student has at most one graduation process in progress.
        DB::statement('CREATE UNIQUE INDEX graduation_processes_single_in_progress ON graduation_processes (student_id) WHERE concluded_at IS NULL AND abandoned_at IS NULL');

        Schema::create('phase_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('graduation_process_id')->constrained()->restrictOnDelete();
            $table->string('phase', 30);
            $table->timestamp('entered_at');
            $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();
            $table->unique(['graduation_process_id', 'phase']);
        });

        DB::statement('ALTER TABLE phase_changes ADD CONSTRAINT phase_changes_phase_check CHECK (phase IN ('.self::PHASES.'))');

        Schema::create('requirement_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('graduation_process_id')->constrained()->restrictOnDelete();
            $table->string('type', 30);
            $table->string('file_path', 500)->unique();
            $table->string('original_file_name', 255);
            $table->integer('file_size_bytes');
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('uploaded_at');
        });

        DB::statement("ALTER TABLE requirement_documents ADD CONSTRAINT requirement_documents_type_check CHECK (type IN ('Boleta de inscripción', 'Hoja de vida', 'Carta de solicitud'))");
        // UC-001 BR-002: Each requirement document is a PDF of up to 20 MB.
        DB::statement('ALTER TABLE requirement_documents ADD CONSTRAINT requirement_documents_size_check CHECK (file_size_bytes BETWEEN 1 AND 20971520)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requirement_documents');
        Schema::dropIfExists('phase_changes');
        Schema::dropIfExists('graduation_processes');
    }
};
