<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licencies', function (Blueprint $table) {
            $table->unsignedSmallInteger('poids')->default(1)->after('sexe');
        });
    }

    public function down(): void
    {
        Schema::table('licencies', function (Blueprint $table) {
            $table->dropColumn('poids');
        });
    }
};
