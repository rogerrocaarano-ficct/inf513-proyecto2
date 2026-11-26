<?php

namespace App\Http\Controllers\Committees;

use App\Actions\Committees\AssignCommittee;
use App\Actions\Committees\FindEligibleTeachers;
use App\Actions\Committees\ListProcessesForCommittee;
use App\Actions\Committees\ReplaceCommitteeMember;
use App\Enums\DefenseType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Committees\AssignCommitteeRequest;
use App\Http\Requests\Committees\ReplaceCommitteeMemberRequest;
use App\Models\CommitteeMember;
use App\Models\GraduationProcess;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class CommitteeController extends Controller
{
    /**
     * Step 2: the processes with an approved profile, those without committee first.
     */
    public function index(ListProcessesForCommittee $listProcesses): Response
    {
        return Inertia::render('committees/index', [
            'processes' => $listProcesses->handle()->map(fn (GraduationProcess $process): array => [
                'id' => $process->id,
                'student' => $process->student->user->name,
                'project_title' => $process->project_title,
                'tutor' => $process->tutor?->user->name,
                'committee' => $process->currentCommitteeMembers->map(
                    fn (CommitteeMember $member): string => $member->teacher->user->name,
                )->values(),
            ])->values(),
        ]);
    }

    /**
     * Steps 3-5 and A2: the eligible teachers, to assign the committee or to replace one member.
     */
    public function edit(
        GraduationProcess $graduationProcess,
        ListProcessesForCommittee $listProcesses,
        FindEligibleTeachers $findEligibleTeachers,
    ): Response|RedirectResponse {
        // UC-006 A3: A committee cannot change once the formal defense was held.
        if ($graduationProcess->committeeMembers()->exists()
            && $graduationProcess->defenses()->where('type', DefenseType::FormalDefense)->where('is_held', true)->exists()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'El tribunal evaluador ya no se puede cambiar porque la defensa formal se realizó.',
            ]);

            return to_route('committees.index');
        }

        abort_unless($listProcesses->query()->whereKey($graduationProcess->id)->exists(), 404);

        $graduationProcess->load(['student.user', 'tutor.user', 'currentCommitteeMembers.teacher.user']);

        return Inertia::render('committees/edit', [
            'process' => [
                'id' => $graduationProcess->id,
                'student' => $graduationProcess->student->user->name,
                'project_title' => $graduationProcess->project_title,
                'tutor' => $graduationProcess->tutor?->user->name,
            ],
            'members' => $graduationProcess->currentCommitteeMembers->map(fn (CommitteeMember $member): array => [
                'id' => $member->id,
                'name' => $member->teacher->user->name,
            ])->values(),
            'eligibleTeachers' => $findEligibleTeachers->handle($graduationProcess)->map(fn (Teacher $teacher): array => [
                'id' => $teacher->id,
                'name' => $teacher->user->name,
            ])->values(),
        ]);
    }

    /**
     * Steps 6-10: assign the three teachers of the committee.
     */
    public function store(
        AssignCommitteeRequest $request,
        GraduationProcess $graduationProcess,
        AssignCommittee $assignCommittee,
    ): RedirectResponse {
        $teachers = $assignCommittee->handle(
            $graduationProcess,
            $request->validated('teacher_ids') ?? [],
            $request->user(),
        );

        return $this->confirmed($teachers->map(fn (Teacher $teacher): string => $teacher->user->name), $graduationProcess);
    }

    /**
     * A2: replace one member of the committee.
     */
    public function replace(
        ReplaceCommitteeMemberRequest $request,
        GraduationProcess $graduationProcess,
        ReplaceCommitteeMember $replaceCommitteeMember,
    ): RedirectResponse {
        $replaceCommitteeMember->handle(
            $graduationProcess,
            $request->integer('committee_member_id'),
            $request->integer('teacher_id'),
        );

        $names = $graduationProcess->currentCommitteeMembers()->with('teacher.user')->orderBy('id')->get()
            ->map(fn (CommitteeMember $member): string => $member->teacher->user->name);

        return $this->confirmed($names, $graduationProcess);
    }

    /**
     * Step 10: the confirmation with the committee and the phase of the process.
     *
     * @param  Collection<int, string>  $names
     */
    private function confirmed(Collection $names, GraduationProcess $process): RedirectResponse
    {
        $process->refresh();
        $list = $names->slice(0, -1)->implode(', ').' y '.$names->last();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Tribunal evaluador asignado: '.$list.'. Fase: '.mb_strtolower($process->phase->value).'.',
        ]);

        return to_route('committees.index');
    }
}
