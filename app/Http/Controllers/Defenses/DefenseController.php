<?php

namespace App\Http\Controllers\Defenses;

use App\Actions\Defenses\DefenseEligibility;
use App\Actions\Defenses\FindScheduleConflicts;
use App\Actions\Defenses\ScheduleDefense;
use App\Enums\DefenseType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Defenses\ScheduleDefenseRequest;
use App\Models\CommitteeMember;
use App\Models\Defense;
use App\Models\GraduationProcess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class DefenseController extends Controller
{
    private const array PEOPLE = ['student.user', 'tutor.user', 'currentCommitteeMembers.teacher.user'];

    public function index(DefenseEligibility $eligibility): Response
    {
        $calendar = Defense::query()->pending()
            ->with(array_map(fn (string $relation): string => 'graduationProcess.'.$relation, self::PEOPLE))
            ->orderBy('scheduled_at')->orderBy('id')->get()
            ->map(function (Defense $act) use ($eligibility): array {
                return $this->describe($act->graduationProcess, $act->type) + [
                    'defense_id' => $act->id,
                    'scheduled_at' => $act->scheduled_at->setTimezone(FindScheduleConflicts::TIMEZONE)->format('Y-m-d\TH:i'),
                    'location' => $act->location,
                    'schedule_revision' => $act->schedule_revision,
                    'can_reschedule' => $eligibility->canReschedule($act, $act->graduationProcess),
                ];
            });

        $waiting = collect(DefenseType::cases())
            ->flatMap(fn (DefenseType $type) => $eligibility->query($type)->with(self::PEOPLE)->orderBy('id')->get()
                ->map(fn (GraduationProcess $process): array => $this->describe($process, $type)));

        return Inertia::render('defenses/index', [
            'calendar' => $calendar,
            'waiting' => $waiting,
        ]);
    }

    public function store(ScheduleDefenseRequest $request, ScheduleDefense $scheduleDefense): RedirectResponse
    {
        $rescheduling = $request->filled('defense_id');
        $start = Carbon::parse($request->string('scheduled_at')->toString(), FindScheduleConflicts::TIMEZONE)->utc();

        try {
            $scheduleDefense->handle(
                $request->integer('process_id'),
                DefenseType::from($request->string('type')->toString()),
                $start,
                $request->validated('location'),
                $rescheduling ? $request->integer('defense_id') : null,
                $rescheduling ? $request->integer('schedule_revision') : null,
            );
        } catch (ValidationException $exception) {
            $message = $exception->errors()['availability'][0] ?? null;

            if ($message === null) {
                throw $exception;
            }

            Inertia::flash('toast', ['type' => 'error', 'message' => $message]);

            return to_route('defenses.index');
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $rescheduling ? 'Se reprogramó el acto.' : 'Se programó el acto.',
        ]);

        return to_route('defenses.index');
    }

    /** @return array{process_id: int, type: string, student: string, tutor: ?string, committee: list<string>} */
    private function describe(GraduationProcess $process, DefenseType $type): array
    {
        return [
            'process_id' => $process->id,
            'type' => $type->value,
            'student' => $process->student->user->name,
            'tutor' => $process->tutor?->user->name,
            'committee' => array_values($process->currentCommitteeMembers
                ->map(fn (CommitteeMember $member): string => $member->teacher->user->name)->all()),
        ];
    }
}
