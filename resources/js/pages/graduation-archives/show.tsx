import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Button } from '@/components/ui/button';
import { index, store } from '@/routes/graduation-archives';
import { formatDate } from './index';
import type { ArchiveSummary } from './index';

type Props = {
    process: ArchiveSummary & {
        archived_at: string | null;
        tutor: string | null;
        committee: string[];
        grade: number | null;
        absence: { act: string; scheduled_at: string } | null;
    };
    documents: { id: number; type: string; name: string; url: string }[];
    versions: {
        id: number;
        number: number;
        phase: string;
        name: string;
        url: string;
    }[];
};

export default function GraduationArchiveShow({
    process,
    documents,
    versions,
}: Props) {
    const form = useForm({});

    function archive(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        if (
            window.confirm(
                '¿Confirma el archivo definitivo del trámite de titulación?',
            )
        ) {
            form.post(store.url(process.id));
        }
    }

    return (
        <>
            <Head
                title={
                    process.archived_at
                        ? 'Trámite archivado'
                        : 'Archivar trámite'
                }
            />
            <article className="max-w-3xl space-y-6 p-4">
                <header className="space-y-2">
                    <h1 className="text-xl font-semibold">{process.student}</h1>
                    <p>{process.title ?? 'Sin título registrado'}</p>
                    <p className="text-sm text-muted-foreground">
                        {process.result}
                        {process.ended_at &&
                            ` · ${formatDate(process.ended_at)}`}
                    </p>
                    {process.archived_at && (
                        <p className="text-sm">
                            Archivo · {formatDate(process.archived_at)}
                        </p>
                    )}
                    {process.grade !== null && (
                        <p>Calificación conjunta: {process.grade} de 100</p>
                    )}
                    {process.absence && (
                        <p>
                            Inasistencia · {process.absence.act} ·{' '}
                            {formatDate(process.absence.scheduled_at)}
                        </p>
                    )}
                </header>
                <dl className="space-y-3">
                    <div>
                        <dt className="font-semibold">Tutor</dt>
                        <dd>{process.tutor ?? 'Sin tutor registrado'}</dd>
                    </div>
                    <div>
                        <dt className="font-semibold">Tribunal evaluador</dt>
                        <dd>
                            {process.committee.length
                                ? process.committee.join(', ')
                                : 'Sin tribunal registrado'}
                        </dd>
                    </div>
                </dl>
                <section className="space-y-2">
                    <h2 className="font-semibold">
                        Documentación de requisitos
                    </h2>
                    {documents.length === 0 ? (
                        <p>Sin documentos registrados.</p>
                    ) : (
                        <ul className="space-y-2">
                            {documents.map((document) => (
                                <li key={document.id}>
                                    <a
                                        className="underline"
                                        href={document.url}
                                    >
                                        {document.type} ({document.name})
                                    </a>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
                <section className="space-y-2">
                    <h2 className="font-semibold">Versiones presentadas</h2>
                    {versions.length === 0 ? (
                        <p>Sin versiones presentadas.</p>
                    ) : (
                        <ul className="space-y-2">
                            {versions.map((version) => (
                                <li key={version.id}>
                                    <a className="underline" href={version.url}>
                                        Versión {version.number} ·{' '}
                                        {version.phase} ({version.name})
                                    </a>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
                <form onSubmit={archive} className="flex flex-wrap gap-2">
                    <Button asChild variant="outline">
                        <Link href={index.url()}>
                            {process.archived_at
                                ? 'Volver a la lista'
                                : 'Cancelar'}
                        </Link>
                    </Button>
                    {!process.archived_at && (
                        <Button disabled={form.processing}>
                            Confirmar archivo
                        </Button>
                    )}
                </form>
            </article>
        </>
    );
}
