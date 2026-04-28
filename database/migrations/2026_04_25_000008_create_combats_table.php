<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('combats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inscription_a_id')->constrained('inscription_operationnelles')->cascadeOnDelete();
            $table->foreignId('inscription_b_id')->constrained('inscription_operationnelles')->cascadeOnDelete();
            $table->unsignedInteger('ordre_combat');
            $table->string('statut')->default('a_saisir');
            $table->timestamps();

            $table->unique(['poule_id', 'inscription_a_id', 'inscription_b_id']);
            $table->unique(['poule_id', 'ordre_combat']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('combats');
    }
};
