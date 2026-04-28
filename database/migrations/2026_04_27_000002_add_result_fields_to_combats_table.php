<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('combats', function (Blueprint $table) {
            $table->string('resultat')->nullable()->after('statut');
            $table->string('score_texte')->nullable()->after('score_b');
            $table->text('commentaire')->nullable()->after('score_texte');
            $table->boolean('absence_forfait')->default(false)->after('commentaire');
        });
    }

    public function down(): void
    {
        Schema::table('combats', function (Blueprint $table) {
            $table->dropColumn(['resultat', 'score_texte', 'commentaire', 'absence_forfait']);
        });
    }
};
