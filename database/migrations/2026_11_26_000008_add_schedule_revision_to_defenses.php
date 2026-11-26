<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('defenses', function (Blueprint $table): void {
            $table->unsignedInteger('schedule_revision')->default(1);
        });
    }

    public function down(): void
    {
        Schema::table('defenses', function (Blueprint $table): void {
            $table->dropColumn('schedule_revision');
        });
    }
};
