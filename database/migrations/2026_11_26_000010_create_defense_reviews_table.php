<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('defense_reviews', function (Blueprint $table): void {
            $table->foreignId('defense_id')->constrained()->restrictOnDelete();
            $table->foreignId('committee_member_id')->constrained()->restrictOnDelete();
            $table->boolean('has_observations');
            $table->integer('grade')->nullable();
            $table->timestamp('recorded_at');
            $table->primary(['defense_id', 'committee_member_id']);
        });

        DB::statement('ALTER TABLE defense_reviews ADD CONSTRAINT defense_reviews_grade_check CHECK (grade IS NULL OR grade BETWEEN 0 AND 100)');
    }

    public function down(): void
    {
        Schema::dropIfExists('defense_reviews');
    }
};
