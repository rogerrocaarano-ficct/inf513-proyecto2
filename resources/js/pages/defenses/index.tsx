import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useEffect, useRef, useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { store } from '@/routes/defenses';

type WaitingDefense = {
    process_id: number;
    type: 'Pre-defensa' | 'Defensa formal';
    student: string;
    tutor: string | null;
    committee: string[];
};

type ScheduledDefense = WaitingDefense & {
    defense_id: number;
    scheduled_at: string;
    location: string | null;
    schedule_revision: number;
    can_reschedule: boolean;
};

type Props = {
    calendar: ScheduledDefense[];
    waiting: WaitingDefense[];
};

function formatSchedule(scheduledAt: string) {
    const [date, time] = scheduledAt.split('T');

    return `${date.split('-').reverse().join('/')} ${time}`;
}

function ScheduleForm({
    act,
    close,
}: {
    act: WaitingDefense | ScheduledDefense;
    close: () => void;
}) {
    const scheduled = 'defense_id' in act ? act : null;
    const form = useForm({
        process_id: act.process_id,
        type: act.type,
        defense_id: scheduled?.defense_id ?? null,
        schedule_revision: scheduled?.schedule_revision ?? null,
        scheduled_at: scheduled?.scheduled_at ?? '',
        location: scheduled?.location ?? '',
    });
    const [dateError, setDateError] = useState('');
    const dateInput = useRef<HTMLInputElement>(null);

    useEffect(() => {
        dateInput.current?.focus();
    }, []);

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        const parts = new Intl.DateTimeFormat('sv-SE', {
            timeZone: 'America/La_Paz',
            dateStyle: 'short',
            timeStyle: 'short',
        }).format(new Date());
        const now = parts.replace(' ', 'T');

        if (form.data.scheduled_at <= now) {
            setDateError('La fecha y hora deben ser futuras.');

            return;
        }

        form.post(store.url(), { preserveScroll: true, onSuccess: close });
    }

    return (
        <section
            className="space-y-4 rounded-lg border p-4"
            aria-labelledby="defense-form-title"
        >
            <h2 id="defense-form-title" className="text-lg font-semibold">
                {scheduled ? 'Reprogramar' : 'Programar'}{' '}
                {act.type.toLowerCase()}
            </h2>
            <dl className="grid gap-x-4 gap-y-2 text-sm sm:grid-cols-[auto_1fr]">
                <dt className="font-medium">Estudiante</dt>
                <dd>{act.student}</dd>
                <dt className="font-medium">Tutor</dt>
                <dd>{act.tutor ?? 'Sin tutor'}</dd>
                <dt className="font-medium">Tribunal evaluador</dt>
                <dd>{act.committee.join(', ') || 'Sin tribunal evaluador'}</dd>
            </dl>
            <form className="space-y-4" onSubmit={submit} noValidate>
                <div className="grid gap-4 sm:grid-cols-2">
                    <div className="space-y-2">
                        <Label htmlFor="scheduled-at">
                            Fecha y hora de Bolivia
                        </Label>
                        <Input
                            id="scheduled-at"
                            ref={dateInput}
                            type="datetime-local"
                            required
                            value={form.data.scheduled_at}
                            onChange={(event) => {
                                form.setData(
                                    'scheduled_at',
                                    event.target.value,
                                );
                                form.clearErrors('scheduled_at');
                                setDateError('');
                            }}
                            aria-describedby="schedule-error"
                        />
                        <div id="schedule-error" aria-live="polite">
                            <InputError
                                message={dateError || form.errors.scheduled_at}
                            />
                        </div>
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="defense-location">
                            Ambiente (opcional)
                        </Label>
                        <Input
                            id="defense-location"
                            maxLength={100}
                            value={form.data.location}
                            onChange={(event) =>
                                form.setData('location', event.target.value)
                            }
                        />
                        <InputError message={form.errors.location} />
                    </div>
                </div>
                <div className="flex flex-wrap gap-2">
                    <Button disabled={form.processing}>
                        Confirmar{' '}
                        {scheduled ? 'reprogramación' : 'programación'}
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        disabled={form.processing}
                        onClick={close}
                    >
                        Cancelar
                    </Button>
                </div>
            </form>
        </section>
    );
}

export default function DefensesIndex({ calendar, waiting }: Props) {
    const [selected, setSelected] = useState<
        WaitingDefense | ScheduledDefense | null
    >(null);

    return (
        <>
            <Head title="Calendario de defensas" />
            <div className="space-y-6 p-4">
                <h1 className="text-xl font-semibold">
                    Calendario de defensas
                </h1>
                {selected && (
                    <ScheduleForm
                        key={
                            'defense_id' in selected
                                ? `scheduled-${selected.defense_id}`
                                : `${selected.process_id}-${selected.type}`
                        }
                        act={selected}
                        close={() => setSelected(null)}
                    />
                )}
                <section aria-labelledby="scheduled-defenses">
                    <h2
                        id="scheduled-defenses"
                        className="text-lg font-semibold"
                    >
                        Actos programados
                    </h2>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Fecha y hora (Bolivia)</TableHead>
                                <TableHead>Acto</TableHead>
                                <TableHead>Ambiente</TableHead>
                                <TableHead>Estudiante</TableHead>
                                <TableHead>Tutor</TableHead>
                                <TableHead>Tribunal evaluador</TableHead>
                                <TableHead>
                                    <span className="sr-only">Acción</span>
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {calendar.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={7}>
                                        No hay actos programados sin realizar.
                                    </TableCell>
                                </TableRow>
                            )}
                            {calendar.map((act) => (
                                <TableRow key={act.defense_id}>
                                    <TableCell>
                                        {formatSchedule(act.scheduled_at)}
                                    </TableCell>
                                    <TableCell>{act.type}</TableCell>
                                    <TableCell>
                                        {act.location ?? 'Sin definir'}
                                    </TableCell>
                                    <TableCell>{act.student}</TableCell>
                                    <TableCell>
                                        {act.tutor ?? 'Sin tutor'}
                                    </TableCell>
                                    <TableCell className="whitespace-normal">
                                        {act.committee.join(', ') ||
                                            'Sin tribunal evaluador'}
                                    </TableCell>
                                    <TableCell>
                                        {act.can_reschedule && (
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                onClick={() => setSelected(act)}
                                                aria-label={`Reprogramar ${act.type.toLowerCase()} de ${act.student}`}
                                            >
                                                Reprogramar
                                            </Button>
                                        )}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </section>
                <section aria-labelledby="waiting-defenses">
                    <h2 id="waiting-defenses" className="text-lg font-semibold">
                        Trámites pendientes de programación
                    </h2>
                    {waiting.length === 0 && (
                        <p className="text-sm text-muted-foreground">
                            No hay trámites pendientes de programación.
                        </p>
                    )}
                    <ul className="divide-y">
                        {waiting.map((act) => (
                            <li
                                key={`${act.process_id}-${act.type}`}
                                className="flex flex-wrap items-center justify-between gap-2 py-3"
                            >
                                <span>
                                    {act.student} · {act.type}
                                </span>
                                <Button
                                    size="sm"
                                    onClick={() => setSelected(act)}
                                    aria-label={`Programar ${act.type.toLowerCase()} de ${act.student}`}
                                >
                                    Programar
                                </Button>
                            </li>
                        ))}
                    </ul>
                </section>
            </div>
        </>
    );
}
