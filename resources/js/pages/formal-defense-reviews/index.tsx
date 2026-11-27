import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { index, show } from '@/routes/formal-defense-reviews';

export type Defense = {
    id: number;
    student: string;
    scheduled_at: string;
    location: string | null;
    schedule_revision: number;
};

export function formatSchedule(value: string): string {
    return new Date(value).toLocaleString('es-BO', {
        dateStyle: 'short',
        timeStyle: 'short',
        timeZone: 'America/La_Paz',
    });
}

export default function FormalDefenseIndex({
    defenses,
}: {
    defenses: Defense[];
}) {
    return (
        <>
            <Head title="Registrar calificación" />
            <section className="space-y-4 p-4">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <h1 className="text-xl font-semibold">
                        Defensas formales pendientes de calificación
                    </h1>
                    <Button asChild variant="outline">
                        <Link href={index.url()}>Actualizar lista</Link>
                    </Button>
                </div>
                {defenses.length === 0 ? (
                    <p className="rounded-md border p-4 text-sm text-muted-foreground">
                        No hay defensas formales pendientes de su calificación.
                    </p>
                ) : (
                    <ul className="divide-y rounded-md border">
                        {defenses.map((defense) => (
                            <li
                                key={defense.id}
                                className="flex flex-wrap items-center justify-between gap-3 p-4"
                            >
                                <div>
                                    <p className="font-medium">
                                        {defense.student}
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                        {formatSchedule(defense.scheduled_at)} ·{' '}
                                        {defense.location ?? 'Sin ambiente'}
                                    </p>
                                </div>
                                <Button asChild size="sm">
                                    <Link href={show.url(defense.id)}>
                                        Calificar
                                    </Link>
                                </Button>
                            </li>
                        ))}
                    </ul>
                )}
            </section>
        </>
    );
}
