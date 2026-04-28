<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('combats', function (Blueprint $table) {
            $table->unsignedInteger('score_a')->nullable()->after('statut');
            $table->unsignedInteger('score_b')->nullable()->after('score_a');
        });
    }

    public function down(): void
    {
        Schema::table('combats', function (Blueprint $table) {
            $table->dropColumn(['score_a', 'score_b']);
        });
    }
};
