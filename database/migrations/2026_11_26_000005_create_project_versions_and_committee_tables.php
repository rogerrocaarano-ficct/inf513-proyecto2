<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('graduation_process_id')->constrained()->restrictOnDelete();
            $table->integer('number');
            $table->string('phase', 30);
            $table->string('title', 300);
            $table->string('file_path', 500)->unique();
            $table->string('original_file_name', 255);
            $table->integer('file_size_bytes');
            $table->string('status', 20);
            $table->foreignId('reviewer_id')->nullable()->constrained('teachers')->restrictOnDelete();
            $table->timestamp('submitted_at');
            $table->timestamp('evaluated_at')->nullable();
            $table->unique(['graduation_process_id', 'number']);
        });

        DB::statement('ALTER TABLE project_versions ADD CONSTRAINT project_versions_number_check CHECK (number BETWEEN 1 AND 999)');
        DB::statement("ALTER TABLE project_versions ADD CONSTRAINT project_versions_phase_check CHECK (phase IN ('Evaluación de perfil', 'Pre-defensa', 'Defensa formal'))");
        DB::statement("ALTER TABLE project_versions ADD CONSTRAINT project_versions_status_check CHECK (status IN ('En revisión', 'Con observaciones', 'Aprobada', 'Rechazada', 'Presentada'))");
        DB::statement('ALTER TABLE project_versions ADD CONSTRAINT project_versions_size_check CHECK (file_size_bytes BETWEEN 1 AND 20971520)');
        DB::statement("ALTER TABLE project_versions ADD CONSTRAINT project_versions_reviewer_only_for_profile CHECK (phase = 'Evaluación de perfil' OR reviewer_id IS NULL)");
        DB::statement("ALTER TABLE project_versions ADD CONSTRAINT project_versions_evaluated_has_reviewer CHECK (phase <> 'Evaluación de perfil' OR status NOT IN ('Aprobada', 'Rechazada') OR reviewer_id IS NOT NULL)");
        DB::statement("ALTER TABLE project_versions ADD CONSTRAINT project_versions_evaluated_at_check CHECK (status NOT IN ('Aprobada', 'Rechazada', 'Con observaciones') OR evaluated_at > submitted_at)");
        DB::statement("CREATE UNIQUE INDEX project_versions_single_in_review ON project_versions (graduation_process_id) WHERE status = 'En revisión'");
        DB::statement("CREATE UNIQUE INDEX project_versions_single_approved_per_phase ON project_versions (graduation_process_id, phase) WHERE status = 'Aprobada'");

        Schema::create('committee_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('graduation_process_id')->constrained()->restrictOnDelete();
            $table->foreignId('teacher_id')->constrained()->restrictOnDelete();
            $table->timestamp('assigned_at');
            $table->timestamp('replaced_at')->nullable();
            $table->unique(['graduation_process_id', 'teacher_id']);
        });

        DB::statement('ALTER TABLE committee_members ADD CONSTRAINT committee_members_replaced_after_assigned CHECK (replaced_at IS NULL OR replaced_at > assigned_at)');

        Schema::create('defenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('graduation_process_id')->constrained()->restrictOnDelete();
            $table->string('type', 20);
            $table->timestamp('scheduled_at');
            $table->string('location', 100)->nullable();
            $table->integer('grade')->nullable();
            $table->boolean('is_held')->default(false);
            $table->boolean('is_student_absent')->default(false);
            $table->foreignId('absence_recorded_by_id')->nullable()->constrained('committee_members')->restrictOnDelete();
        });

        DB::statement("ALTER TABLE defenses ADD CONSTRAINT defenses_type_check CHECK (type IN ('Pre-defensa', 'Defensa formal'))");
        DB::statement('ALTER TABLE defenses ADD CONSTRAINT defenses_grade_check CHECK (grade IS NULL OR grade BETWEEN 0 AND 100)');
        DB::statement('ALTER TABLE defenses ADD CONSTRAINT defenses_absent_only_when_held CHECK (NOT is_student_absent OR is_held)');
        DB::statement('ALTER TABLE defenses ADD CONSTRAINT defenses_absence_recorded_by CHECK ((absence_recorded_by_id IS NOT NULL) = is_student_absent)');
        DB::statement('CREATE UNIQUE INDEX defenses_single_pending_per_type ON defenses (graduation_process_id, type) WHERE NOT is_held');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('defenses');
        Schema::dropIfExists('committee_members');
        Schema::dropIfExists('project_versions');
    }
};
