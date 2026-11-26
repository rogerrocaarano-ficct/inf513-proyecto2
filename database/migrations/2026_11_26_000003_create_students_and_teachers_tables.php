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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->restrictOnDelete();
            $table->string('registration_number', 20)->unique();
            $table->string('id_card_number', 20);
            $table->string('id_card_complement', 5)->nullable();
            $table->string('phone', 20)->nullable();
        });

        // UC-001 BR-004: The identity card, with its complement, identifies one student.
        DB::statement('ALTER TABLE students ADD CONSTRAINT students_id_card_unique UNIQUE NULLS NOT DISTINCT (id_card_number, id_card_complement)');

        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->restrictOnDelete();
            $table->boolean('is_workshop_teacher')->default(false);
            $table->boolean('is_active')->default(true);
        });

        // UC-003 BR-001: At most one teacher is the workshop teacher.
        DB::statement('CREATE UNIQUE INDEX teachers_single_workshop_teacher ON teachers (is_workshop_teacher) WHERE is_workshop_teacher');
        DB::statement('ALTER TABLE teachers ADD CONSTRAINT teachers_workshop_teacher_is_active CHECK (NOT is_workshop_teacher OR is_active)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('students');
    }
};
