<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class GuidePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guide_page_displays_visual_help_content(): void
    {
        $this->get(route('guide'))
            ->assertOk()
            ->assertSee('<div class="guide">', false)
            ->assertSee('<h1>🥋 FFK Interclubs</h1>', false)
            ->assertSee('Guide utilisateur')
            ->assertSeeText('FFK Interclubs permet d’organiser une compétition interclubs')
            ->assertSee('Club participant')
            ->assertSee('Organisateur');
    }

    public function test_guide_page_links_to_demo_test_dataset_documentation(): void
    {
        $this->get(route('guide'))
            ->assertOk()
            ->assertSee('Jeu de test démo')
            ->assertSee('href="'.route('guide.jeu-test-demo').'"', false)
            ->assertSee('Réinitialiser cette démo')
            ->assertSee('href="'.route('demo.reset').'"', false);

        $this->get(route('guide.jeu-test-demo'))
            ->assertOk()
            ->assertSee('<h1>Jeu de test demo</h1>', false)
            ->assertSeeText('Open Interclubs Méditerranée 2026')
            ->assertSee('href="'.route('guide').'"', false);
    }

    public function test_demo_reset_page_explains_reset_and_blocks_wrong_password(): void
    {
        config(['demo.reset_password' => 'secret-demo']);

        $this->get(route('demo.reset'))
            ->assertOk()
            ->assertSee('Réinitialiser cette démo')
            ->assertSee('Remettre à zéro la démo')
            ->assertSee('Cette action remet les données de démonstration dans leur état initial.')
            ->assertSee('Vous allez effacer toutes les données et revenir au jeu de démonstration initial. Voulez-vous continuer ?')
            ->assertSee('Oui, réinitialiser')
            ->assertSee('Non, annuler')
            ->assertSee('type="button" data-reset-open-confirmation', false);

        $this->post(route('demo.reset.run'), [
            'password' => 'wrong-password',
        ])
            ->assertSessionHasErrors('password');
    }

    public function test_demo_reset_page_runs_seed_reset_with_valid_password(): void
    {
        config(['demo.reset_password' => 'secret-demo']);

        Artisan::shouldReceive('call')
            ->once()
            ->with('migrate:fresh', [
                '--seed' => true,
                '--force' => true,
            ])
            ->andReturn(0);

        $this->post(route('demo.reset.run'), [
            'password' => 'secret-demo',
        ])
            ->assertRedirect(route('demo.reset'))
            ->assertSessionHas('status', 'Démo réinitialisée.');
    }

    public function test_help_video_can_be_changed_from_demo_user_page(): void
    {
        $iframe = '<iframe width="100%" height="400" src="https://www.youtube.com/embed/demo-video" frameborder="0" allowfullscreen></iframe>';

        $this->get(route('demo.users.index'))
            ->assertOk()
            ->assertSee('action="'.route('demo.video.update').'"', false)
            ->assertSeeText('Code iframe YouTube');

        $this->post(route('demo.video.update'), [
            'video_iframe' => $iframe,
        ])
            ->assertRedirect();

        $this->assertDatabaseHas('settings', [
            'key' => 'help_video_iframe',
            'value' => $iframe,
        ]);

        $this->get(route('guide'))
            ->assertOk()
            ->assertSee($iframe, false);
    }

    public function test_guide_page_does_not_render_video_when_setting_is_empty(): void
    {
        Setting::create([
            'key' => 'help_video_iframe',
            'value' => null,
        ]);

        $this->get(route('guide'))
            ->assertOk()
            ->assertDontSee('<div class="guide-video">', false);
    }

    public function test_home_page_links_to_guide(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Aide / Comment ça marche')
            ->assertSee('href="'.route('guide').'"', false);
    }
}
