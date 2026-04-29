<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertRedirect()
            ->assertSessionHas('help_video_iframe', $iframe);

        $this->withSession(['help_video_iframe' => $iframe])
            ->get(route('guide'))
            ->assertOk()
            ->assertSee($iframe, false);
    }

    public function test_home_page_links_to_guide(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Aide / Comment ça marche')
            ->assertSee('href="'.route('guide').'"', false);
    }
}
