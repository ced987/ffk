<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participant_sources', function (Blueprint $table) {
            $table->foreignId('licencie_id')
                ->nullable()
                ->after('club_id')
                ->constrained('licencies')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('participant_sources', function (Blueprint $table) {
            $table->dropConstrainedForeignId('licencie_id');
        });
    }
};
