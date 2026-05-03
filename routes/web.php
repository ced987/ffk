<?php

use App\Models\Club;
use App\Models\Combat;
use App\Models\Competition;
use App\Models\InscriptionOperationnelle;
use App\Models\Invitation;
use App\Models\Licencie;
use App\Models\ParticipantSource;
use App\Models\Poule;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Route;

$demoUsers = function () {
    return User::with('club')->orderBy('id')->get();
};

$currentDemoUser = function () use ($demoUsers) {
    $users = $demoUsers();
    $defaultUser = $users->first(fn (User $user) => $user->club?->name === 'KC Marseille 13') ?? $users->first();
    $currentUser = $users->firstWhere('id', session('current_user_id')) ?? $defaultUser;

    if ($currentUser !== null && session('current_user_id') !== $currentUser->id) {
        session(['current_user_id' => $currentUser->id]);
    }

    return $currentUser;
};

$demoCompetitionName = 'Competition Demo MVP';

if (! function_exists('redirect_to_competition_section')) {
    function redirect_to_competition_section(Competition $competition, string $section, string $status)
    {
        return redirect()
            ->back(302, [], route('competitions.show', $competition))
            ->with('status', $status);
    }
}

if (! function_exists('redirect_to_competition_detail')) {
    function redirect_to_competition_detail(Competition $competition, string $status)
    {
        return redirect()
            ->route('competitions.show', $competition)
            ->with('status', $status);
    }
}

if (! function_exists('redirect_to_competition_fragment')) {
    function redirect_to_competition_fragment(Competition $competition, string $fragment, string $status)
    {
        return redirect()
            ->to(route('competitions.show', $competition).'#'.$fragment)
            ->with('status', $status);
    }
}

if (! function_exists('participant_work_section')) {
    function participant_work_section(InscriptionOperationnelle $registration): string
    {
        if (! $registration->is_active) {
            return 'participants-retires';
        }

        return $registration->is_validated ? 'participants-valides' : 'participants-non-valides';
    }
}

if (! function_exists('poule_work_section')) {
    function poule_work_section(Poule $poule): string
    {
        return $poule->status === Poule::STATUS_DRAFT ? 'poules-brouillon' : 'poules-figees';
    }
}

if (! function_exists('combat_work_section')) {
    function combat_work_section(Combat $combat): string
    {
        return $combat->statut === Combat::STATUS_FINISHED ? 'combats-termines' : 'combats-a-saisir';
    }
}

Route::get('/', function () use ($currentDemoUser) {
    return view('welcome', [
        'currentUser' => $currentDemoUser(),
    ]);
})->name('home');

Route::get('/demo/users', function () use ($demoUsers, $currentDemoUser) {
    return view('demo.users.index', [
        'users' => $demoUsers(),
        'currentUser' => $currentDemoUser(),
    ]);
})->name('demo.users.index');

Route::redirect('/switch-user', '/demo/users')->name('switch-user');

Route::get('/demo/users/{user}/select', function (User $user) {
    session(['current_user_id' => $user->id]);

    return redirect()->route('competitions.index');
})->name('demo.users.select');

Route::post('/demo/video', function (Request $request) {
    Setting::updateOrCreate(
        ['key' => 'help_video_iframe'],
        ['value' => $request->input('video_iframe')],
    );

    return back()->with('status', 'Vidéo d’aide modifiée.');
})->name('demo.video.update');

Route::get('/guide', function () {
    return view('guide', [
        'guide' => file_get_contents(base_path('docs/guide.md')),
        'helpVideoIframe' => Setting::where('key', 'help_video_iframe')->value('value'),
        'showGuideExtras' => true,
    ]);
})->name('guide');

Route::get('/guide/jeu-test-demo', function () {
    return view('guide', [
        'guide' => file_get_contents(base_path('docs/jeu-test-demo.md')),
        'helpVideoIframe' => null,
        'showGuideExtras' => false,
    ]);
})->name('guide.jeu-test-demo');

Route::get('/demo/reset', function () {
    abort_if(app()->environment('production'), 404);

    return view('demo.reset');
})->name('demo.reset');

Route::post('/demo/reset', function (Request $request) {
    abort_if(app()->environment('production'), 404);

    $request->validate([
        'password' => ['required', 'string'],
    ]);

    $expectedPassword = config('demo.reset_password');

    if (blank($expectedPassword)) {
        return back()
            ->withErrors(['password' => 'DEMO_RESET_PASSWORD doit être renseigné.'])
            ->withInput();
    }

    if (! hash_equals((string) $expectedPassword, (string) $request->input('password'))) {
        return back()
            ->withErrors(['password' => 'Mot de passe incorrect.'])
            ->withInput();
    }

    Artisan::call('migrate:fresh', [
        '--seed' => true,
        '--force' => true,
    ]);

    return redirect()
        ->route('demo.reset')
        ->with('status', 'Démo réinitialisée.');
})->name('demo.reset.run');

Route::get('/licencies', function () use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);

    return view('licencies.index', [
        'currentUser' => $currentUser,
        'licencies' => Licencie::query()
            ->where('club_id', $currentUser->club_id)
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get(),
    ]);
})->name('licencies.index');

Route::patch('/club', function (Request $request) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);

    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
    ]);

    $currentUser->club->update([
        'name' => $validated['name'],
    ]);
    $currentUser->club->users()->update([
        'name' => $validated['name'],
    ]);

    return redirect()->route('licencies.index')->with('status', 'Nom du club modifié.');
})->name('club.update');

Route::patch('/clubs/{club}', function (Request $request, Club $club) {
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
    ]);

    $club->update([
        'name' => $validated['name'],
    ]);
    $club->users()->update([
        'name' => $validated['name'],
    ]);

    return redirect()->route('demo.users.index')->with('status', 'Nom du club modifié.');
})->name('demo.clubs.update');

Route::get('/licencies/create', function () use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);

    return view('licencies.create', [
        'currentUser' => $currentUser,
    ]);
})->name('licencies.create');

Route::post('/licencies', function (Request $request) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);

    $validated = $request->validate([
        'nom' => ['required', 'string', 'max:255'],
        'prenom' => ['required', 'string', 'max:255'],
        'date_naissance' => ['required', 'date', 'before:today'],
        'sexe' => ['required', Rule::in(['masculin', 'feminin'])],
        'poids' => ['required', 'integer', 'min:1'],
    ]);

    Licencie::create([
        'club_id' => $currentUser->club_id,
        'nom' => $validated['nom'],
        'prenom' => $validated['prenom'],
        'date_naissance' => $validated['date_naissance'],
        'sexe' => $validated['sexe'],
        'poids' => $validated['poids'],
    ]);

    return redirect()->route('licencies.index');
})->name('licencies.store');

Route::get('/licencies/{licencie}/edit', function (Licencie $licencie) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($licencie->club_id === $currentUser->club_id, 403);

    return view('licencies.edit', [
        'currentUser' => $currentUser,
        'licencie' => $licencie,
    ]);
})->name('licencies.edit');

Route::match(['put', 'patch'], '/licencies/{licencie}', function (Request $request, Licencie $licencie) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($licencie->club_id === $currentUser->club_id, 403);

    $validated = $request->validate([
        'nom' => ['required', 'string', 'max:255'],
        'prenom' => ['required', 'string', 'max:255'],
        'date_naissance' => ['required', 'date', 'before:today'],
        'sexe' => ['required', Rule::in(['masculin', 'feminin'])],
        'poids' => ['required', 'integer', 'min:1'],
    ]);

    $licencie->update([
        'nom' => $validated['nom'],
        'prenom' => $validated['prenom'],
        'date_naissance' => $validated['date_naissance'],
        'sexe' => $validated['sexe'],
        'poids' => $validated['poids'],
    ]);

    return redirect()->route('licencies.index');
})->name('licencies.update');

Route::delete('/licencies/{licencie}', function (Licencie $licencie) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($licencie->club_id === $currentUser->club_id, 403);

    $licencie->delete();

    return redirect()->route('licencies.index')->with('status', 'Licencié supprimé.');
})->name('licencies.destroy');

Route::get('/competitions', function () use ($currentDemoUser, $demoCompetitionName) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);

    $competitions = Competition::with('organizerClub')
        ->visibleForClub($currentUser->club)
        ->latest()
        ->get()
        ->sortByDesc(fn (Competition $competition) => $competition->name === $demoCompetitionName)
        ->values();

    return view('competitions.index', [
        'currentUser' => $currentUser,
        'competitions' => $competitions,
        'demoCompetitionName' => $demoCompetitionName,
    ]);
})->name('competitions.index');

Route::get('/competitions/{competition}', function (Request $request, Competition $competition) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);

    abort_unless(
        Competition::visibleForClub($currentUser->club)->whereKey($competition)->exists(),
        404
    );

    $competition->load([
        'organizerClub',
        'invitations.club',
        'operationalRegistrations',
        'poules.registrations.club',
        'poules.registrations.participantSource',
        'poules.combats.inscriptionA.participantSource',
        'poules.combats.inscriptionB.participantSource',
    ]);
    $currentInvitation = $competition->invitations
        ->firstWhere('club_id', $currentUser->club_id);
    $invitedClubIds = $competition->invitations()->pluck('club_id');
    $availableClubs = Club::query()
        ->whereKeyNot($competition->organizer_club_id)
        ->whereNotIn('id', $invitedClubIds)
        ->orderBy('name')
        ->get();
    $invitationStatusLabels = Invitation::STATUS_LABELS;
    $invitationSummary = [
        Invitation::STATUS_PRE_INVITE => $competition->invitations->where('status', Invitation::STATUS_PRE_INVITE)->count(),
        Invitation::STATUS_INVITE => $competition->invitations->where('status', Invitation::STATUS_INVITE)->count(),
        Invitation::STATUS_PARTICIPATION_CONFIRMED => $competition->invitations->where('status', Invitation::STATUS_PARTICIPATION_CONFIRMED)->count(),
        Invitation::STATUS_PARTICIPATION_DECLINED => $competition->invitations->where('status', Invitation::STATUS_PARTICIPATION_DECLINED)->count(),
    ];
    $participantValidationSummary = $competition->participantValidationSummary();
    $participantCountsByClub = $participantValidationSummary['by_club']
        ->map(fn (array $summary) => $summary['active']);
    $participantTotal = $participantValidationSummary['global']['active'];
    $organizerParticipantCount = $participantCountsByClub->get($competition->organizer_club_id, 0);
    $currentClubRegistrations = $currentUser->club
        ? $competition->operationalRegistrations()
            ->with(['participantSource', 'poule'])
            ->where('club_id', $currentUser->club_id)
            ->latest()
            ->get()
        : collect();
    $registrationsByClub = $currentUser->club?->is($competition->organizerClub)
        ? $competition->operationalRegistrations()
            ->with(['club', 'participantSource', 'poule'])
            ->orderBy('club_id')
            ->latest()
            ->get()
            ->groupBy('club_id')
        : collect();
    $eligiblePouleRegistrations = $currentUser->club?->is($competition->organizerClub)
        ? $competition->eligiblePouleRegistrations()
        : collect();
    $isOrganizer = $currentUser->club->is($competition->organizerClub);
    $canRegisterParticipants = $isOrganizer || (
        $currentInvitation?->status === Invitation::STATUS_PARTICIPATION_CONFIRMED
        && ! $competition->inscriptions_closed
    );
    $participantClubOptions = $isOrganizer
        ? collect([$competition->organizerClub])
            ->merge($competition->invitations->pluck('club'))
            ->filter()
            ->unique('id')
            ->values()
        : collect();
    $currentClubLicencies = $canRegisterParticipants
        ? Licencie::query()
            ->where('club_id', $currentUser->club_id)
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get()
        : collect();
    $registeredLicencieIds = $canRegisterParticipants
        ? ParticipantSource::query()
            ->where('club_id', $currentUser->club_id)
            ->whereNotNull('licencie_id')
            ->whereHas('operationalRegistrations', fn ($query) => $query->where('competition_id', $competition->id))
            ->pluck('licencie_id')
        : collect();
    $pouleAssistantCriteria = [
        'same_sex_only' => $request->input('same_sex_only', '1') === '1',
        'age_gap_max' => max(0, min(50, (int) $request->input('age_gap_max', 2))),
        'weight_gap_max' => max(0, min(200, (float) $request->input('weight_gap_max', 5))),
        'target_size' => max(2, min(16, (int) $request->input('target_size', 4))),
        'adult_access_age' => max(12, min(30, (int) $request->input('adult_access_age', 18))),
    ];
    $pouleAssistantResult = $isOrganizer && $request->boolean('analyze_poules')
        ? $competition->pouleAssistantProposals($pouleAssistantCriteria)
        : null;

    return view('competitions.show', [
        'currentUser' => $currentUser,
        'competition' => $competition,
        'availableClubs' => $availableClubs,
        'currentInvitation' => $currentInvitation,
        'currentClubRegistrations' => $currentClubRegistrations,
        'registrationsByClub' => $registrationsByClub,
        'eligiblePouleRegistrations' => $eligiblePouleRegistrations,
        'invitationStatusLabels' => $invitationStatusLabels,
        'invitationSummary' => $invitationSummary,
        'participantCountsByClub' => $participantCountsByClub,
        'participantValidationSummary' => $participantValidationSummary,
        'participantTotal' => $participantTotal,
        'organizerParticipantCount' => $organizerParticipantCount,
        'actionsToDo' => $competition->actionsToDoForClub($currentUser->club),
        'isOrganizer' => $isOrganizer,
        'canRegisterParticipants' => $canRegisterParticipants,
        'participantClubOptions' => $participantClubOptions,
        'currentClubLicencies' => $currentClubLicencies,
        'registeredLicencieIds' => $registeredLicencieIds,
        'pouleAssistantCriteria' => $pouleAssistantCriteria,
        'pouleAssistantResult' => $pouleAssistantResult,
    ]);
})->name('competitions.show');

Route::get('/competitions/{competition}/poules/{poule}/print', function (Competition $competition, Poule $poule) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($poule->competition_id === $competition->id, 404);
    abort_unless(
        Competition::visibleForClub($currentUser->club)->whereKey($competition)->exists(),
        404
    );

    $competition->load('organizerClub');
    $poule->load([
        'registrations.club',
        'registrations.participantSource',
        'combats.inscriptionA.participantSource',
        'combats.inscriptionB.participantSource',
    ]);

    return view('competitions.poules.print', [
        'competition' => $competition,
        'poule' => $poule,
    ]);
})->name('competitions.poules.print');

Route::get('/competitions/{competition}/poules', function (Competition $competition) {
    return redirect_to_competition_detail($competition, 'Utilisez le formulaire pour créer une poule.');
});

Route::patch('/competitions/{competition}', function (Request $request, Competition $competition) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($currentUser->club_id === $competition->organizer_club_id, 403);

    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
    ]);

    $competition->update([
        'name' => $validated['name'],
    ]);

    return redirect()->route('competitions.show', $competition)->with('status', 'Nom de la compétition modifié.');
})->name('competitions.update');

Route::post('/competitions/{competition}/poules', function (Request $request, Competition $competition) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($currentUser->club_id === $competition->organizer_club_id, 403);

    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
    ]);

    $poule = Poule::create([
        'competition_id' => $competition->id,
        'name' => $validated['name'],
        'status' => Poule::STATUS_DRAFT,
    ]);

    return redirect_to_competition_fragment($competition, 'creation-poule', 'Poule créée en préparation.');
})->name('competitions.poules.store');

Route::post('/competitions/{competition}/poules/proposals', function (Request $request, Competition $competition) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($currentUser->club_id === $competition->organizer_club_id, 403);

    $validated = $request->validate([
        'proposal_registration_ids' => ['required', 'array', 'min:1'],
        'proposal_registration_ids.*' => ['required', 'string'],
        'proposal_names' => ['nullable', 'array'],
        'proposal_names.*' => ['nullable', 'string', 'max:100'],
    ]);

    $createdCount = 0;

    foreach ($validated['proposal_registration_ids'] as $index => $registrationIds) {
        $ids = collect(explode(',', $registrationIds))
            ->map(fn (string $id) => (int) trim($id))
            ->filter()
            ->unique()
            ->values();

        if ($ids->count() < 2) {
            continue;
        }

        $registrations = InscriptionOperationnelle::query()
            ->whereIn('id', $ids)
            ->where('competition_id', $competition->id)
            ->where('is_active', true)
            ->where('is_validated', true)
            ->whereNull('poule_id')
            ->orderBy('id')
            ->get();

        if ($registrations->count() < 2) {
            continue;
        }

        $poule = Poule::create([
            'competition_id' => $competition->id,
            'name' => $validated['proposal_names'][$index] ?? 'Poule proposée '.($index + 1),
            'status' => Poule::STATUS_DRAFT,
        ]);

        $registrations->each(fn (InscriptionOperationnelle $registration) => $registration->update(['poule_id' => $poule->id]));
        $createdCount++;
    }

    if ($createdCount === 0) {
        return redirect_to_competition_fragment($competition, 'assistant-poules', 'Aucune poule proposée créée.');
    }

    return redirect_to_competition_fragment($competition, 'poules-brouillon', $createdCount.' poule(s) proposée(s) créée(s).');
})->name('competitions.poules.proposals.store');

Route::patch('/competitions/{competition}/poules/{poule}/rename', function (Request $request, Competition $competition, Poule $poule) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($poule->competition_id === $competition->id, 404);

    if ($currentUser->club_id !== $competition->organizer_club_id) {
        return redirect_to_competition_detail($competition, 'Impossible : action réservée à l’organisateur');
    }

    $validated = $request->validate([
        'name' => ['required', 'string', 'max:100'],
    ]);

    $poule->update(['name' => $validated['name']]);

    return redirect_to_competition_fragment($competition, poule_work_section($poule), 'Poule renommée.');
})->name('competitions.poules.rename');

Route::get('/competitions/{competition}/poules/{poule}/registrations', function (Competition $competition, Poule $poule) {
    return redirect_to_competition_detail($competition, 'Utilisez le formulaire pour affecter un participant.');
});

Route::post('/competitions/{competition}/poules/{poule}/registrations', function (Request $request, Competition $competition, Poule $poule) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($currentUser->club_id === $competition->organizer_club_id, 403);
    abort_unless($poule->competition_id === $competition->id, 404);

    $validated = $request->validate([
        'registration_id' => ['required', 'exists:inscription_operationnelles,id'],
    ]);

    $registration = InscriptionOperationnelle::findOrFail($validated['registration_id']);

    abort_unless($registration->competition_id === $competition->id, 403);

    if ($message = $poule->assignmentBlockedMessage($registration)) {
        return redirect_to_competition_fragment($competition, 'participants-disponibles', $message);
    }

    $registration->update(['poule_id' => $poule->id]);

    return redirect_to_competition_fragment($competition, 'participants-disponibles', 'Participant affecte a la poule.');
})->name('competitions.poules.registrations.store');

Route::get('/competitions/{competition}/poules/{poule}/freeze', function (Competition $competition) {
    return redirect_to_competition_detail($competition, 'Utilisez le formulaire pour figer une poule.');
});

Route::patch('/competitions/{competition}/poules/{poule}/freeze', function (Competition $competition, Poule $poule) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($currentUser->club_id === $competition->organizer_club_id, 403);
    abort_unless($poule->competition_id === $competition->id, 404);

    if ($message = $poule->freezeBlockedMessage()) {
        return redirect_to_competition_fragment($competition, poule_work_section($poule), $message);
    }

    $poule->update(['status' => Poule::STATUS_FROZEN]);

    if (! $poule->combats()->exists()) {
        $poule->generateCombats();
    }

    return redirect_to_competition_fragment($competition, 'poules-brouillon', 'Poule figée et combats générés.');
})->name('competitions.poules.freeze');

Route::get('/competitions/{competition}/poules/{poule}/unfreeze', function (Competition $competition) {
    return redirect_to_competition_detail($competition, 'Utilisez le formulaire pour défiger une poule.');
});

Route::patch('/competitions/{competition}/poules/{poule}/unfreeze', function (Competition $competition, Poule $poule) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($currentUser->club_id === $competition->organizer_club_id, 403);
    abort_unless($poule->competition_id === $competition->id, 404);

    if ($poule->status !== Poule::STATUS_FROZEN) {
        return redirect_to_competition_fragment($competition, poule_work_section($poule), 'Impossible : poule non figée');
    }

    $poule->combats()->delete();
    $poule->update(['status' => Poule::STATUS_DRAFT]);

    return redirect_to_competition_fragment($competition, 'poules-figees', 'Poule remise en préparation.');
})->name('competitions.poules.unfreeze');

Route::delete('/competitions/{competition}/poules/{poule}', function (Competition $competition, Poule $poule) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($poule->competition_id === $competition->id, 404);

    if ($currentUser->club_id !== $competition->organizer_club_id) {
        return redirect_to_competition_detail($competition, 'Impossible : action réservée à l’organisateur');
    }

    $section = poule_work_section($poule);

    $poule->combats()->delete();
    $poule->registrations()->update(['poule_id' => null]);
    $poule->delete();

    return redirect_to_competition_fragment($competition, $section, 'Poule supprimée.');
})->name('competitions.poules.destroy');

Route::get('/competitions/{competition}/poules/{poule}/combats/generate', function (Competition $competition) {
    return redirect_to_competition_detail($competition, 'Utilisez le formulaire pour générer les combats.');
});

Route::post('/competitions/{competition}/poules/{poule}/combats/generate', function (Competition $competition, Poule $poule) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);

    if ($currentUser->club_id !== $competition->organizer_club_id) {
        return redirect_to_competition_fragment($competition, poule_work_section($poule), 'Impossible : action réservée à l’organisateur');
    }

    abort_unless($poule->competition_id === $competition->id, 404);

    if ($message = $poule->combatGenerationBlockedMessage()) {
        return redirect_to_competition_fragment($competition, poule_work_section($poule), $message);
    }

    $poule->generateCombats();

    return redirect_to_competition_fragment($competition, poule_work_section($poule), 'Combats générés.');
})->name('competitions.poules.combats.generate');

Route::get('/competitions/{competition}/combats/{combat}/edit', function (Competition $competition, Combat $combat) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($combat->poule->competition_id === $competition->id, 404);

    if ($message = $combat->scoreAccessBlockedMessage($competition->organizer_club_id, $currentUser->club_id)) {
        return redirect_to_competition_detail($competition, $message);
    }

    $combat->load(['poule', 'inscriptionA.participantSource', 'inscriptionB.participantSource']);

    return view('competitions.combats.edit', [
        'currentUser' => $currentUser,
        'competition' => $competition,
        'combat' => $combat,
    ]);
})->name('competitions.combats.edit');

Route::get('/competitions/{competition}/combats/{combat}', function (Competition $competition) {
    return redirect_to_competition_detail($competition, 'Utilisez le formulaire pour enregistrer le score.');
});

Route::patch('/competitions/{competition}/combats/{combat}', function (Request $request, Competition $competition, Combat $combat) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($combat->poule->competition_id === $competition->id, 404);

    $fragment = 'combat-'.$combat->id;

    if ($message = $combat->scoreAccessBlockedMessage($competition->organizer_club_id, $currentUser->club_id)) {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect_to_competition_fragment($competition, $fragment, $message);
    }

    if ($request->input('action') === 'clear') {
        $combat->update([
            'resultat' => null,
            'score_a' => null,
            'score_b' => null,
            'score_texte' => null,
            'commentaire' => null,
            'absence_forfait' => false,
            'statut' => Combat::STATUS_TO_ENTER,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Résultat du combat effacé.']);
        }

        return redirect_to_competition_fragment($competition, $fragment, 'Résultat du combat effacé.');
    }

    $validated = $request->validate([
        'resultat' => ['required', Rule::in(array_keys(Combat::RESULT_LABELS))],
        'score_a' => ['nullable', 'integer', 'min:0'],
        'score_b' => ['nullable', 'integer', 'min:0'],
        'commentaire' => ['nullable', 'string', 'max:500'],
    ]);

    $scoreA = $validated['score_a'] ?? null;
    $scoreB = $validated['score_b'] ?? null;

    $combat->update([
        'resultat' => $validated['resultat'],
        'score_a' => $scoreA,
        'score_b' => $scoreB,
        'score_texte' => $scoreA !== null || $scoreB !== null ? trim(($scoreA ?? '').' - '.($scoreB ?? '')) : null,
        'commentaire' => $validated['commentaire'] ?? null,
        'absence_forfait' => false,
        'statut' => Combat::STATUS_FINISHED,
    ]);

    if ($request->expectsJson()) {
        return response()->json(['message' => 'Résultat du combat enregistré.']);
    }

    return redirect_to_competition_fragment($competition, $fragment, 'Résultat du combat enregistré.');
})->name('competitions.combats.update');

Route::get('/competitions/{competition}/registrations/{registration}/withdraw-assignment', function (Competition $competition) {
    return redirect_to_competition_detail($competition, 'Utilisez le formulaire pour retirer l affectation.');
});

Route::patch('/competitions/{competition}/registrations/{registration}/withdraw-assignment', function (Competition $competition, InscriptionOperationnelle $registration) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($currentUser->club_id === $competition->organizer_club_id, 403);
    abort_unless($registration->competition_id === $competition->id, 404);

    if ($message = $registration->withdrawAssignmentBlockedMessage()) {
        $fragment = $registration->poule ? poule_work_section($registration->poule) : participant_work_section($registration);

        return redirect_to_competition_fragment($competition, $fragment, $message);
    }

    $fragment = $registration->poule ? poule_work_section($registration->poule) : participant_work_section($registration);
    $registration->update(['poule_id' => null]);

    return redirect_to_competition_fragment($competition, $fragment, 'Affectation retiree.');
})->name('competitions.registrations.withdraw-assignment');

Route::get('/competitions/{competition}/registrations/{registration}/move-assignment', function (Competition $competition) {
    return redirect_to_competition_detail($competition, 'Utilisez le formulaire pour déplacer le participant.');
});

Route::patch('/competitions/{competition}/registrations/{registration}/move-assignment', function (Request $request, Competition $competition, InscriptionOperationnelle $registration) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($currentUser->club_id === $competition->organizer_club_id, 403);
    abort_unless($registration->competition_id === $competition->id, 404);

    $validated = $request->validate([
        'poule_id' => ['required', 'exists:poules,id'],
    ]);

    $targetPoule = Poule::findOrFail($validated['poule_id']);

    abort_unless($targetPoule->competition_id === $competition->id, 403);

    if ($message = $registration->moveAssignmentBlockedMessage($targetPoule)) {
        $fragment = $registration->poule ? poule_work_section($registration->poule) : 'participants-disponibles';

        return redirect_to_competition_fragment($competition, $fragment, $message);
    }

    $fragment = $registration->poule ? poule_work_section($registration->poule) : 'participants-disponibles';
    $registration->update(['poule_id' => $targetPoule->id]);

    return redirect_to_competition_fragment($competition, $fragment, 'Participant deplace vers une autre poule.');
})->name('competitions.registrations.move-assignment');

Route::post('/competitions/{competition}/invitations', function (Request $request, Competition $competition) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($currentUser->club_id === $competition->organizer_club_id, 403);

    $validated = $request->validate([
        'club_id' => ['required', 'exists:clubs,id'],
    ]);

    $clubId = (int) $validated['club_id'];

    if ($clubId === $competition->organizer_club_id) {
        return back()
            ->withErrors(['club_id' => 'Le club organisateur ne peut pas s inviter lui-meme.'])
            ->withInput();
    }

    if ($competition->invitations()->where('club_id', $clubId)->exists()) {
        return back()
            ->withErrors(['club_id' => 'Ce club est deja invite a cette competition.'])
            ->withInput();
    }

    Invitation::create([
        'competition_id' => $competition->id,
        'club_id' => $clubId,
        'status' => Invitation::STATUS_PRE_INVITE,
    ]);

    return redirect_to_competition_section($competition, 'clubs', 'Club ajouté en préparation de l’invitation.');
})->name('competitions.invitations.store');

Route::get('/competitions/{competition}/invitations/{invitation}/mark-sent', function (Competition $competition) {
    return redirect_to_competition_detail($competition, 'Utilisez le formulaire pour marquer l invitation envoyee.');
});

Route::post('/competitions/{competition}/invitations/{invitation}/mark-sent', function (Competition $competition, Invitation $invitation) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($currentUser->club_id === $competition->organizer_club_id, 403);
    abort_unless($invitation->competition_id === $competition->id, 404);

    if ($invitation->status === Invitation::STATUS_PRE_INVITE) {
        $invitation->markAsSent();
    }

    return redirect_to_competition_section($competition, 'clubs', 'Invitation marquee comme envoyee.');
})->name('competitions.invitations.mark-sent');

Route::get('/competitions/{competition}/invitations/{invitation}/confirm', function (Competition $competition) {
    return redirect_to_competition_detail($competition, 'Utilisez le formulaire pour confirmer la participation.');
});

Route::post('/competitions/{competition}/invitations/{invitation}/confirm', function (Competition $competition, Invitation $invitation) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($invitation->competition_id === $competition->id, 404);
    abort_unless($invitation->club_id === $currentUser->club_id, 403);
    abort_unless($currentUser->club_id !== $competition->organizer_club_id, 403);
    abort_unless($invitation->status === Invitation::STATUS_INVITE, 403);

    $invitation->confirmParticipation();

    return redirect_to_competition_section($competition, 'clubs', 'Participation confirmée.');
})->name('competitions.invitations.confirm');

Route::get('/competitions/{competition}/invitations/{invitation}/decline', function (Competition $competition) {
    return redirect_to_competition_detail($competition, 'Utilisez le formulaire pour refuser la participation.');
});

Route::post('/competitions/{competition}/invitations/{invitation}/decline', function (Competition $competition, Invitation $invitation) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($invitation->competition_id === $competition->id, 404);
    abort_unless($invitation->club_id === $currentUser->club_id, 403);
    abort_unless($currentUser->club_id !== $competition->organizer_club_id, 403);
    abort_unless($invitation->status === Invitation::STATUS_INVITE, 403);

    $invitation->declineParticipation();

    return redirect_to_competition_section($competition, 'clubs', 'Participation refusée.');
})->name('competitions.invitations.decline');

Route::patch('/competitions/{competition}/close-inscriptions', function (Competition $competition) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($currentUser->club_id === $competition->organizer_club_id, 403);

    if ($competition->inscriptions_closed) {
        return redirect_to_competition_fragment($competition, 'participants', 'Inscriptions déjà fermées.');
    }

    $competition->update(['inscriptions_closed' => true]);

    return redirect_to_competition_fragment($competition, 'participants', 'Inscriptions fermées.');
})->name('competitions.close-inscriptions');

Route::patch('/competitions/{competition}/open-inscriptions', function (Competition $competition) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($currentUser->club_id === $competition->organizer_club_id, 403);

    if (! $competition->inscriptions_closed) {
        return redirect_to_competition_fragment($competition, 'participants', 'Inscriptions déjà ouvertes.');
    }

    $competition->update(['inscriptions_closed' => false]);

    return redirect_to_competition_fragment($competition, 'participants', 'Inscriptions ouvertes.');
})->name('competitions.open-inscriptions');

Route::patch('/competitions/{competition}/informations-complementaires', function (Request $request, Competition $competition) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($currentUser->club_id === $competition->organizer_club_id, 403);

    $validated = $request->validate([
        'informations_complementaires' => ['nullable', 'string', 'max:1000'],
    ]);

    $competition->update([
        'informations_complementaires' => blank($validated['informations_complementaires'] ?? null)
            ? null
            : $validated['informations_complementaires'],
    ]);

    return redirect_to_competition_fragment($competition, 'actions', 'Informations complémentaires enregistrées.');
})->name('competitions.informations-complementaires.update');

Route::patch('/competitions/{competition}/date', function (Request $request, Competition $competition) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($currentUser->club_id === $competition->organizer_club_id, 403);

    $validated = $request->validate([
        'date_competition' => ['nullable', 'date'],
    ]);

    $competition->update([
        'date_competition' => blank($validated['date_competition'] ?? null)
            ? null
            : $validated['date_competition'],
    ]);

    return redirect_to_competition_fragment($competition, 'actions', 'Date de compétition enregistrée.');
})->name('competitions.date.update');

Route::post('/competitions/{competition}/participants', function (Request $request, Competition $competition) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);

    $isOrganizer = $currentUser->club_id === $competition->organizer_club_id;
    $invitation = $isOrganizer
        ? null
        : $competition->invitations()
            ->where('club_id', $currentUser->club_id)
            ->first();

    abort_unless($isOrganizer || $invitation?->status === Invitation::STATUS_PARTICIPATION_CONFIRMED, 403);

    if (! $isOrganizer && $competition->inscriptions_closed) {
        return redirect_to_competition_fragment($competition, 'participants-ajout', 'Inscriptions fermées.');
    }

    $validated = $request->validate([
        'last_name' => ['required', 'string', 'max:255'],
        'first_name' => ['required', 'string', 'max:255'],
        'sex' => ['required', 'string', 'max:20'],
        'age' => ['required', 'integer', 'min:1', 'max:120'],
        'approximate_weight' => ['required', 'numeric', 'min:1', 'max:300'],
        'license_number' => ['nullable', 'string', 'max:255'],
    ]);

    $clubId = $currentUser->club_id;

    if ($isOrganizer) {
        $allowedClubIds = $competition->invitations()
            ->pluck('club_id')
            ->push($competition->organizer_club_id)
            ->map(fn ($clubId) => (int) $clubId)
            ->all();

        $validatedClub = $request->validate([
            'club_id' => ['nullable', 'integer', Rule::in($allowedClubIds)],
        ]);

        $clubId = (int) ($validatedClub['club_id'] ?? $competition->organizer_club_id);
    }

    $participant = ParticipantSource::create([
        'club_id' => $clubId,
        'last_name' => $validated['last_name'],
        'first_name' => $validated['first_name'],
        'sex' => $validated['sex'],
        'age' => $validated['age'],
        'approximate_weight' => $validated['approximate_weight'],
        'license_number' => $validated['license_number'] ?? null,
    ]);

    $registration = InscriptionOperationnelle::create([
        'competition_id' => $competition->id,
        'club_id' => $clubId,
        'participant_source_id' => $participant->id,
        'is_active' => true,
        'is_validated' => false,
    ]);

    return redirect_to_competition_fragment($competition, 'participants-ajout', 'Participant inscrit.');
})->name('competitions.participants.store');

Route::post('/competitions/{competition}/participants/from-licencie', function (Request $request, Competition $competition) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);

    $isOrganizer = $currentUser->club_id === $competition->organizer_club_id;
    $invitation = $isOrganizer
        ? null
        : $competition->invitations()
            ->where('club_id', $currentUser->club_id)
            ->first();

    abort_unless($isOrganizer || $invitation?->status === Invitation::STATUS_PARTICIPATION_CONFIRMED, 403);

    if (! $isOrganizer && $competition->inscriptions_closed) {
        return redirect_to_competition_fragment($competition, 'participants-ajout', 'Inscriptions fermées.');
    }

    $validated = $request->validate([
        'licencie_id' => ['required', 'exists:licencies,id'],
    ]);

    $licencie = Licencie::findOrFail($validated['licencie_id']);
    abort_unless($licencie->club_id === $currentUser->club_id, 403);

    $alreadyRegistered = InscriptionOperationnelle::query()
        ->where('competition_id', $competition->id)
        ->whereHas('participantSource', fn ($query) => $query->where('licencie_id', $licencie->id))
        ->exists();

    if ($alreadyRegistered) {
        return redirect_to_competition_fragment($competition, 'participants-ajout', 'Ce licencié est déjà inscrit à la compétition.');
    }

    $participant = ParticipantSource::create([
        'club_id' => $currentUser->club_id,
        'licencie_id' => $licencie->id,
        'last_name' => $licencie->nom,
        'first_name' => $licencie->prenom,
        'sex' => match ($licencie->sexe) {
            'masculin' => 'M',
            'feminin' => 'F',
            default => $licencie->sexe,
        },
        'age' => $licencie->date_naissance->age,
        'approximate_weight' => $licencie->poids,
        'license_number' => null,
    ]);

    InscriptionOperationnelle::create([
        'competition_id' => $competition->id,
        'club_id' => $currentUser->club_id,
        'participant_source_id' => $participant->id,
        'is_active' => true,
        'is_validated' => false,
    ]);

    return redirect_to_competition_fragment($competition, 'participants-ajout', 'Participant inscrit depuis un licencié.');
})->name('competitions.participants.store-from-licencie');

Route::get('/competitions/{competition}/participants/{registration}/edit', function (Competition $competition, InscriptionOperationnelle $registration) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($registration->competition_id === $competition->id, 404);
    abort_unless($registration->club_id === $currentUser->club_id, 403);

    $isOrganizer = $currentUser->club_id === $competition->organizer_club_id;
    $invitation = $isOrganizer
        ? null
        : $competition->invitations()
            ->where('club_id', $currentUser->club_id)
            ->first();

    abort_unless($isOrganizer || $invitation?->status === Invitation::STATUS_PARTICIPATION_CONFIRMED, 403);

    if (! $isOrganizer && $competition->inscriptions_closed) {
        return redirect_to_competition_fragment($competition, participant_work_section($registration), 'Inscriptions fermées.');
    }

    if ($message = $registration->editBlockedMessage()) {
        return redirect_to_competition_detail($competition, $message);
    }

    $registration->load('participantSource');

    return view('competitions.participants.edit', [
        'currentUser' => $currentUser,
        'competition' => $competition->load('organizerClub'),
        'registration' => $registration,
        'participant' => $registration->participantSource,
    ]);
})->name('competitions.participants.edit');

Route::patch('/competitions/{competition}/participants/{registration}', function (Request $request, Competition $competition, InscriptionOperationnelle $registration) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($registration->competition_id === $competition->id, 404);
    abort_unless($registration->club_id === $currentUser->club_id, 403);

    $isOrganizer = $currentUser->club_id === $competition->organizer_club_id;
    $invitation = $isOrganizer
        ? null
        : $competition->invitations()
            ->where('club_id', $currentUser->club_id)
            ->first();

    abort_unless($isOrganizer || $invitation?->status === Invitation::STATUS_PARTICIPATION_CONFIRMED, 403);

    if (! $isOrganizer && $competition->inscriptions_closed) {
        return redirect_to_competition_fragment($competition, participant_work_section($registration), 'Inscriptions fermées.');
    }

    if ($message = $registration->editBlockedMessage()) {
        return redirect_to_competition_fragment($competition, participant_work_section($registration), $message);
    }

    $validated = $request->validate([
        'last_name' => ['required', 'string', 'max:255'],
        'first_name' => ['required', 'string', 'max:255'],
        'sex' => ['required', 'string', 'max:20'],
        'age' => ['required', 'integer', 'min:1', 'max:120'],
        'approximate_weight' => ['required', 'numeric', 'min:1', 'max:300'],
        'license_number' => ['nullable', 'string', 'max:255'],
    ]);

    $registration->load('participantSource');
    abort_unless($registration->participantSource->club_id === $currentUser->club_id, 403);

    $registration->participantSource->update([
        'last_name' => $validated['last_name'],
        'first_name' => $validated['first_name'],
        'sex' => $validated['sex'],
        'age' => $validated['age'],
        'approximate_weight' => $validated['approximate_weight'],
        'license_number' => $validated['license_number'] ?? null,
    ]);

    return redirect_to_competition_fragment($competition, participant_work_section($registration), 'Participant modifie.');
})->name('competitions.participants.update');

Route::get('/competitions/{competition}/participants/{registration}/withdraw', function (Competition $competition) {
    return redirect_to_competition_detail($competition, 'Utilisez le formulaire pour retirer le participant.');
});

Route::patch('/competitions/{competition}/participants/{registration}/withdraw', function (Competition $competition, InscriptionOperationnelle $registration) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($registration->competition_id === $competition->id, 404);

    $isOrganizer = $currentUser->club_id === $competition->organizer_club_id;

    abort_unless($isOrganizer || $registration->club_id === $currentUser->club_id, 403);

    if ($message = $registration->withdrawBlockedMessage()) {
        return redirect_to_competition_fragment($competition, participant_work_section($registration), $message);
    }

    $invitation = $isOrganizer
        ? null
        : $competition->invitations()
            ->where('club_id', $currentUser->club_id)
            ->first();

    abort_unless($isOrganizer || $invitation?->status === Invitation::STATUS_PARTICIPATION_CONFIRMED, 403);

    if (! $isOrganizer && $competition->inscriptions_closed) {
        return redirect_to_competition_fragment($competition, participant_work_section($registration), 'Inscriptions fermées.');
    }

    $fragment = participant_work_section($registration);

    $registration->update([
        'is_active' => false,
    ]);

    return redirect_to_competition_fragment($competition, $fragment, 'Participant retire.');
})->name('competitions.participants.withdraw');

Route::get('/competitions/{competition}/participants/{registration}/validate', function (Competition $competition) {
    return redirect_to_competition_detail($competition, 'Utilisez le formulaire pour valider le participant.');
});

Route::patch('/competitions/{competition}/participants/{registration}/validate', function (Competition $competition, InscriptionOperationnelle $registration) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($currentUser->club_id === $competition->organizer_club_id, 403);
    abort_unless($registration->competition_id === $competition->id, 404);

    if ($message = $registration->validateBlockedMessage()) {
        return redirect_to_competition_fragment($competition, participant_work_section($registration), $message);
    }

    $fragment = participant_work_section($registration);

    $registration->update([
        'is_active' => true,
        'is_validated' => true,
    ]);

    return redirect_to_competition_fragment($competition, $fragment, 'Participant validé.');
})->name('competitions.participants.validate');

Route::get('/competitions/{competition}/participants/{registration}/unvalidate', function (Competition $competition) {
    return redirect_to_competition_detail($competition, 'Utilisez le formulaire pour dévalider le participant.');
});

Route::patch('/competitions/{competition}/participants/{registration}/unvalidate', function (Competition $competition, InscriptionOperationnelle $registration) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($currentUser->club_id === $competition->organizer_club_id, 403);
    abort_unless($registration->competition_id === $competition->id, 404);

    if ($message = $registration->unvalidateBlockedMessage()) {
        return redirect_to_competition_fragment($competition, participant_work_section($registration), $message);
    }

    $registration->update(['is_validated' => false]);

    return redirect_to_competition_fragment($competition, 'participants-valides', 'Participant en attente de validation.');
})->name('competitions.participants.unvalidate');

Route::get('/competitions/{competition}/participants/{registration}/reactivate', function (Competition $competition) {
    return redirect_to_competition_detail($competition, 'Utilisez le formulaire pour réactiver le participant.');
});

Route::patch('/competitions/{competition}/participants/{registration}/reactivate', function (Competition $competition, InscriptionOperationnelle $registration) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);
    abort_unless($registration->competition_id === $competition->id, 404);
    abort_unless($registration->club_id === $currentUser->club_id, 403);

    if ($message = $registration->reactivateBlockedMessage()) {
        return redirect_to_competition_fragment($competition, participant_work_section($registration), $message);
    }

    $isOrganizer = $currentUser->club_id === $competition->organizer_club_id;
    $invitation = $isOrganizer
        ? null
        : $competition->invitations()
            ->where('club_id', $currentUser->club_id)
            ->first();

    abort_unless($isOrganizer || $invitation?->status === Invitation::STATUS_PARTICIPATION_CONFIRMED, 403);

    if (! $isOrganizer && $competition->inscriptions_closed) {
        return redirect_to_competition_fragment($competition, participant_work_section($registration), 'Inscriptions fermées.');
    }

    $registration->update(['is_active' => true]);

    return redirect_to_competition_fragment($competition, 'participants-retires', 'Participant reactive.');
})->name('competitions.participants.reactivate');

Route::post('/competitions', function (Request $request) use ($currentDemoUser) {
    $currentUser = $currentDemoUser();

    abort_if($currentUser === null || $currentUser->club === null, 403);

    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
    ]);

    Competition::create([
        'organizer_club_id' => $currentUser->club->id,
        'name' => $validated['name'],
    ]);

    return redirect()
        ->route('competitions.index')
        ->with('status', 'Competition creee pour '.$currentUser->club->name.'.');
})->name('competitions.store');
