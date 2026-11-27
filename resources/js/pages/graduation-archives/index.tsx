import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { show } from '@/routes/graduation-archives';

export type ArchiveSummary = {
    id: number;
    student: string;
    title: string | null;
    result: string;
    ended_at: string | null;
};

export function formatDate(value: string): string {
    return new Intl.DateTimeFormat('es-BO', {
        dateStyle: 'medium',
        timeStyle: 'short',
        timeZone: 'America/La_Paz',
    }).format(new Date(value));
}

export default function GraduationArchiveIndex({
    processes,
}: {
    processes: ArchiveSummary[];
}) {
    return (
        <>
            <Head title="Archivar trámites de titulación" />
            <section className="space-y-4 p-4">
                <h1 className="text-xl font-semibold">
                    Trámites de titulación por archivar
                </h1>
                {processes.length === 0 ? (
                    <p className="rounded-md border p-4 text-sm text-muted-foreground">
                        No hay trámites de titulación por archivar.
                    </p>
                ) : (
                    <ul className="divide-y rounded-md border">
                        {processes.map((process) => (
                            <li
                                key={process.id}
                                className="flex flex-wrap items-center justify-between gap-3 p-4"
                            >
                                <div className="space-y-1">
                                    <p className="font-medium">
                                        {process.student}
                                    </p>
                                    <p className="text-sm">
                                        {process.title ??
                                            'Sin título registrado'}
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                        {process.result}
                                        {process.ended_at &&
                                            ` · ${formatDate(process.ended_at)}`}
                                    </p>
                                </div>
                                <Button asChild variant="outline">
                                    <Link href={show.url(process.id)}>
                                        Revisar trámite
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
