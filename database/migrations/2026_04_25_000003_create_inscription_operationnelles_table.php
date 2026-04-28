<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscription_operationnelles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_source_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['competition_id', 'participant_source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscription_operationnelles');
    }
};
