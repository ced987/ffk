<?php

use App\Models\Invitation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->rebuildInvitationsTable([
            Invitation::STATUS_PRE_INVITE,
            Invitation::STATUS_INVITE,
            Invitation::STATUS_PARTICIPATION_CONFIRMED,
            Invitation::STATUS_PARTICIPATION_DECLINED,
        ]);
    }

    public function down(): void
    {
        $this->rebuildInvitationsTable([
            Invitation::STATUS_PRE_INVITE,
            Invitation::STATUS_INVITE,
        ]);
    }

    /**
     * SQLite cannot alter an enum/check constraint in place.
     */
    private function rebuildInvitationsTable(array $statuses): void
    {
        $quotedStatuses = collect($statuses)
            ->map(fn (string $status) => "'".$status."'")
            ->implode(', ');

        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement(<<<SQL
            CREATE TABLE invitations_new (
                id integer primary key autoincrement not null,
                competition_id integer not null,
                club_id integer not null,
                status varchar check ("status" in ({$quotedStatuses})) not null default 'pre_invite',
                created_at datetime,
                updated_at datetime,
                foreign key("competition_id") references "competitions"("id") on delete cascade,
                foreign key("club_id") references "clubs"("id") on delete cascade
            )
        SQL);

        DB::statement(<<<SQL
            INSERT INTO invitations_new (id, competition_id, club_id, status, created_at, updated_at)
            SELECT id, competition_id, club_id, status, created_at, updated_at
            FROM invitations
        SQL);

        DB::statement('DROP TABLE invitations');
        DB::statement('ALTER TABLE invitations_new RENAME TO invitations');

        DB::statement(<<<SQL
            CREATE UNIQUE INDEX invitations_competition_id_club_id_unique
            on invitations (competition_id, club_id)
        SQL);

        DB::statement('PRAGMA foreign_keys = ON');
    }
};
