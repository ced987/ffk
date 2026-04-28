<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Competition;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompetitionVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_sprint_1_competition_visibility_depends_on_organizer_or_sent_invitation(): void
    {
        $clubA = Club::create(['name' => 'Club A']);
        $clubB = Club::create(['name' => 'Club B']);
        $clubC = Club::create(['name' => 'Club C']);

        $competition = Competition::create([
            'organizer_club_id' => $clubA->id,
            'name' => 'Competition Sprint 1',
        ]);

        $invitation = Invitation::create([
            'competition_id' => $competition->id,
            'club_id' => $clubB->id,
            'status' => Invitation::STATUS_PRE_INVITE,
        ]);

        $this->assertTrue(Competition::visibleForClub($clubA)->whereKey($competition)->exists());
        $this->assertFalse(Competition::visibleForClub($clubB)->whereKey($competition)->exists());
        $this->assertFalse(Competition::visibleForClub($clubC)->whereKey($competition)->exists());

        $invitation->markAsSent();

        $this->assertTrue(Competition::visibleForClub($clubB)->whereKey($competition)->exists());
        $this->assertFalse(Competition::visibleForClub($clubC)->whereKey($competition)->exists());
    }
}
