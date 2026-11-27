import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index, store } from '@/routes/formal-defense-reviews';
import { formatSchedule } from './index';
import type { Defense } from './index';

type Props = {
    defense: Defense;
    members: { id: number; name: string; graded: boolean }[];
    canReportAbsence: boolean;
    version: { number: number; name: string; download_url: string } | null;
};

export default function FormalDefenseShow({
    defense,
    members,
    canReportAbsence,
    version,
}: Props) {
    const form = useForm({
        grade: '',
        absent: false,
        schedule_revision: defense.schedule_revision,
    });

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        if (
            !form.data.absent &&
            (!/^\d{1,3}$/.test(form.data.grade) ||
                Number(form.data.grade) > 100)
        ) {
            form.setError(
                'grade',
                'Ingrese una calificación entera entre 0 y 100.',
            );
            return;
        }

        if (
            window.confirm(
                form.data.absent
                    ? '¿Confirma la inasistencia? El trámite de titulación quedará abandonado.'
                    : '¿Confirma su calificación? Una vez registrada, es definitiva.',
            )
        ) {
            form.transform((data) => ({
                ...data,
                grade: data.absent ? null : Number(data.grade),
            }));
            form.post(store.url(defense.id));
        }
    }

    return (
        <>
            <Head title="Calificar defensa formal" />
            <div className="max-w-2xl space-y-6 p-4">
                <Button asChild variant="outline">
                    <Link href={index.url()}>Volver a defensas pendientes</Link>
                </Button>
                <header>
                    <h1 className="text-xl font-semibold">
                        Defensa formal de {defense.student}
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        {formatSchedule(defense.scheduled_at)} ·{' '}
                        {defense.location ?? 'Sin ambiente'}
                    </p>
                </header>
                <section className="space-y-2">
                    <h2 className="font-semibold">Versión del proyecto</h2>
                    {version ? (
                        <a className="underline" href={version.download_url}>
                            Descargar versión {version.number} ({version.name})
                        </a>
                    ) : (
                        <p className="text-sm text-muted-foreground">
                            No hay una versión disponible para descargar.
                        </p>
                    )}
                </section>
                <section className="space-y-2">
                    <h2 className="font-semibold">Tribunal evaluador</h2>
                    <ul className="divide-y rounded-md border">
                        {members.map((member) => (
                            <li
                                key={member.id}
                                className="flex flex-wrap justify-between gap-2 p-3 text-sm"
                            >
                                <span>{member.name}</span>
                                <span>
                                    {member.graded ? 'Calificó' : 'Pendiente'}
                                </span>
                            </li>
                        ))}
                    </ul>
                </section>
                <form onSubmit={submit} className="space-y-4" noValidate>
                    {canReportAbsence && (
                        <Label className="flex items-center gap-2">
                            <input
                                type="checkbox"
                                checked={form.data.absent}
                                onChange={(event) => {
                                    form.setData(
                                        'absent',
                                        event.target.checked,
                                    );
                                    form.clearErrors('grade');
                                }}
                            />
                            El estudiante no se presentó
                        </Label>
                    )}
                    {!form.data.absent && (
                        <div className="space-y-2">
                            <Label htmlFor="grade">
                                Calificación (0 a 100)
                            </Label>
                            <Input
                                id="grade"
                                inputMode="numeric"
                                value={form.data.grade}
                                aria-invalid={Boolean(form.errors.grade)}
                                aria-describedby="grade-error"
                                onChange={(event) =>
                                    form.setData('grade', event.target.value)
                                }
                            />
                            <div id="grade-error">
                                <InputError message={form.errors.grade} />
                            </div>
                        </div>
                    )}
                    <InputError
                        message={
                            form.errors.absent ?? form.errors.schedule_revision
                        }
                    />
                    <Button disabled={form.processing}>
                        {form.data.absent
                            ? 'Registrar inasistencia'
                            : 'Registrar calificación'}
                    </Button>
                </form>
            </div>
        </>
    );
}
