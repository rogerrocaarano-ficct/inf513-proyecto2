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
        Schema::create('observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('project_version_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('defense_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('body', 2000);
            $table->timestamp('recorded_at');
        });

        DB::statement('ALTER TABLE observations ADD CONSTRAINT observations_single_subject CHECK ((project_version_id IS NULL) <> (defense_id IS NULL))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('observations');
    }
};
