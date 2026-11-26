<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // UC-008 BR-002: a committee member approves a pre-defense version at most once.
        Schema::create('version_approvals', function (Blueprint $table) {
            $table->foreignId('project_version_id')->constrained()->restrictOnDelete();
            $table->foreignId('committee_member_id')->constrained()->restrictOnDelete();
            $table->timestamp('approved_at');
            $table->primary(['project_version_id', 'committee_member_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('version_approvals');
    }
};
