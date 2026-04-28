<?php

use App\Models\Invitation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('club_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->enum('status', [
                Invitation::STATUS_PRE_INVITE,
                Invitation::STATUS_INVITE,
                Invitation::STATUS_PARTICIPATION_CONFIRMED,
                Invitation::STATUS_PARTICIPATION_DECLINED,
            ])->default(Invitation::STATUS_PRE_INVITE);
            $table->timestamps();

            $table->unique(['competition_id', 'club_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
