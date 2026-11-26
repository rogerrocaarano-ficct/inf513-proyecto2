<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('version_approvals', function (Blueprint $table): void {
            $table->foreignId('project_version_id')->constrained()->restrictOnDelete();
            $table->foreignId('committee_member_id')->constrained()->restrictOnDelete();
            $table->timestamp('approved_at');
            $table->primary(['project_version_id', 'committee_member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('version_approvals');
    }
};
