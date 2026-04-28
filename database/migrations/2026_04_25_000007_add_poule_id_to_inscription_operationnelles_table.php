<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscription_operationnelles', function (Blueprint $table) {
            $table->foreignId('poule_id')
                ->nullable()
                ->after('participant_source_id')
                ->constrained('poules')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inscription_operationnelles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('poule_id');
        });
    }
};
