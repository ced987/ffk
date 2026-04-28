<?php

use App\Models\Club;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licencies', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Club::class)->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->string('prenom');
            $table->date('date_naissance');
            $table->string('sexe');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licencies');
    }
};
