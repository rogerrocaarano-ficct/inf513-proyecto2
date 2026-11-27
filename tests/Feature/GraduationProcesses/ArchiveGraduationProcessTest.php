<?php

use App\Actions\Defenses\RegisterFormalDefense;
use App\Enums\Phase;
use App\Models\Defense;
use App\Models\GraduationProcess;
use App\Models\Observation;
use App\Models\ProjectVersion;
use App\Models\RequirementDocument;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    $this->withoutVite();
    $this->freezeSecond();
});

test('UC-012 NFR-005: only the administrative assistant can view or archive ended processes', function () {
    $process = GraduationProcess::factory()->approved()->create();
    $index = route('graduation-archives.index');

    $this->get($index)->assertRedirect(route('login'));
    $this->actingAs($process->student->user)->get($index)->assertForbidden();
    $this->get(route('graduation-archives.show', $process))->assertForbidden();
    $this->post(route('graduation-archives.store', $process))->assertForbidden();

    $this->actingAs(User::factory()->administrativeAssistant()->create())
        ->get(route('dashboard'))->assertInertia(fn (AssertableInertia $page) => $page
        ->where('menu', fn ($menu): bool => collect($menu)->pluck('title')->contains('Archivar trámite')));
});

test('UC-012 step 2 and A1: only pending archive records appear oldest first with their outcomes', function () {
    $this->actingAs(User::factory()->administrativeAssistant()->create());
    $index = route('graduation-archives.index');

    GraduationProcess::factory()->create();
    GraduationProcess::factory()->approved()->inPhase(Phase::Archive)->create();
    $this->get($index)->assertInertia(fn (AssertableInertia $page) => $page->has('processes', 0));

    $passed = GraduationProcess::factory()->approved()->create();
    $absent = GraduationProcess::factory()->abandoned()->create(['abandoned_at' => now()->subDays(3)]);
    $failed = GraduationProcess::factory()->failed()->create(['concluded_at' => now()->subDays(2)]);

    $this->get($index)->assertInertia(fn (AssertableInertia $page) => $page
        ->component('graduation-archives/index')->has('processes', 3)
        ->where('processes.0.id', $absent->id)->where('processes.0.result', 'Abandonado')
        ->where('processes.1.id', $failed->id)->where('processes.1.result', 'Reprobado')
        ->where('processes.2.id', $passed->id)->where('processes.2.result', 'Aprobado')
        ->where('processes.2.title', $passed->project_title)
        ->where('processes.2.student', $passed->student->user->name));
});

test('UC-012 archives the UC-011 result once and preserves the history, documents and observations', function (bool $absent) {
    $process = GraduationProcess::factory()->withCommittee()->inPhase(Phase::FormalDefense)->create();
    $defense = Defense::factory()->for($process)->formalDefense()->create(['scheduled_at' => now()->subHour()]);
    $members = $process->currentCommitteeMembers()->orderBy('id')->get();
    $register = app(RegisterFormalDefense::class);

    foreach ($absent ? $members->take(1) : $members as $member) {
        $register->handle($member->teacher->user, $defense, $defense->schedule_revision, $absent, $absent ? null : 51);
    }

    $process->refresh();
    $endedAt = $process->concluded_at ?? $process->abandoned_at;
    $this->travel(1)->second();

    Storage::fake('local');
    $assistant = User::factory()->administrativeAssistant()->create();
    $document = RequirementDocument::factory()->for($process)->create();
    $version = ProjectVersion::factory()->for($process)->create();
    $other = ProjectVersion::factory()->create();
    $observation = Observation::factory()->forVersion($version)->create();
    Storage::disk('local')->put($document->file_path, 'PDF');
    Storage::disk('local')->put($version->file_path, 'PDF');

    $index = route('graduation-archives.index');
    $detail = route('graduation-archives.show', $process);
    $submission = route('graduation-archives.store', $process);
    $file = route('graduation-archives.documents', [$process, $document]);
    $pdf = route('graduation-archives.versions', [$process, $version]);

    $this->actingAs($assistant)->get($detail)->assertInertia(fn (AssertableInertia $page) => $page
        ->where('process.grade', $absent ? null : 51)
        ->where('process.absence', $absent ? [
            'act' => $defense->type->value,
            'scheduled_at' => $defense->scheduled_at->toIso8601String(),
        ] : null)
        ->where('process.tutor', $process->tutor->user->name)
        ->has('process.committee', 3)
        ->where('documents.0.url', $file)->where('versions.0.url', $pdf));

    // A2: reviewing the detail and returning to the list makes no changes.
    $this->get($index)->assertInertia(fn (AssertableInertia $page) => $page->has('processes', 1));
    expect($process->fresh()->archived_at)->toBeNull();
    $this->get($file)->assertDownload($document->original_file_name);
    $this->get($pdf)->assertDownload($version->original_file_name);

    $this->post($submission)->assertRedirect($index)->assertInertiaFlash('toast.type', 'success');

    $archived = $process->fresh();
    expect($archived->phase)->toBe(Phase::Archive)
        ->and($archived->archived_at->greaterThan($endedAt))->toBeTrue()
        ->and(($archived->concluded_at ?? $archived->abandoned_at)->equalTo($endedAt))->toBeTrue()
        ->and($archived->phaseChanges()->where('phase', Phase::Archive)->sole()->entered_at->equalTo($archived->archived_at))->toBeTrue();
    $this->assertDatabaseHas('phase_changes', ['graduation_process_id' => $process->id, 'phase' => Phase::Archive->value, 'changed_by' => $assistant->id]);
    $this->assertModelExists($observation);
    expect($defense->fresh()->grade)->toBe($absent ? null : 51)
        ->and(GraduationProcess::query()->inProgress()->whereKey($process->id)->exists())->toBeFalse();

    $this->get($index)->assertInertia(fn (AssertableInertia $page) => $page->has('processes', 0));
    $this->get($detail)->assertInertia(fn (AssertableInertia $page) => $page->where('process.archived_at', $archived->archived_at->toIso8601String()));
    $this->get($file)->assertDownload($document->original_file_name);
    $this->get($pdf)->assertDownload($version->original_file_name);
    $this->get(route('graduation-archives.versions', [$process, $other]))->assertNotFound();
    $this->actingAs($process->student->user)->get($file)->assertForbidden();

    $this->actingAs($assistant)->post($submission)->assertRedirect($index)
        ->assertInertiaFlash('toast.message', 'El trámite de titulación ya fue archivado.');

    expect($archived->notifications()->where('message', 'Su trámite de titulación fue archivado.')
        ->sole()->user_id)->toBe($process->student->user_id);
})->with(['concluded' => [false], 'abandoned' => [true]]);

test('UC-012 A3 and BR-001: rejects an active process or an archive at the same instant as its conclusion', function () {
    $active = GraduationProcess::factory()->create();
    $justConcluded = GraduationProcess::factory()->approved()->create(['concluded_at' => now()]);
    $this->actingAs(User::factory()->administrativeAssistant()->create());
    $index = route('graduation-archives.index');

    $this->post(route('graduation-archives.store', $active))->assertRedirect($index)
        ->assertInertiaFlash('toast.message', 'El trámite de titulación sigue en curso y todavía no se puede archivar.');
    $this->post(route('graduation-archives.store', $justConcluded))->assertRedirect($index)
        ->assertInertiaFlash('toast.type', 'error');
    $this->assertDatabaseMissing('phase_changes', ['phase' => Phase::Archive->value]);
    $this->assertDatabaseCount('notifications', 0);
});
