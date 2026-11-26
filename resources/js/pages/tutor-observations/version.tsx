import { Form, Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { download, index, show, store } from '@/routes/tutor-observations';

type Props = {
    version: {
        id: number;
        process_id: number;
        student: string;
        number: number;
        phase: string;
        status: string;
        title: string;
        file_name: string;
        file_size_bytes: number;
        submitted_at: string;
    };
    observations: {
        id: number;
        author: string;
        body: string;
        recorded_at: string;
    }[];
};

function formatDate(value: string): string {
    return new Date(value).toLocaleString('es-BO', {
        dateStyle: 'short',
        timeStyle: 'short',
    });
}

function formatSize(bytes: number): string {
    return bytes >= 1_000_000
        ? `${(bytes / 1_000_000).toFixed(1)} MB`
        : `${Math.max(1, Math.round(bytes / 1000))} KB`;
}

export default function TutorObservationsVersion({
    version,
    observations,
}: Props) {
    const [texts, setTexts] = useState<string[]>(['']);
    const [clientError, setClientError] = useState<string | null>(null);

    // UC-007 A1: an observation is not empty and has at most 2000 characters (the server validates it too).
    const checkTexts = (): boolean => {
        if (texts.some((text) => text.trim() === '')) {
            setClientError('La observación no puede estar vacía.');

            return false;
        }

        setClientError(null);

        return true;
    };

    return (
        <>
            <Head title="Observar versión del proyecto" />

            <div className="max-w-3xl space-y-6 p-4">
                <div className="space-y-1">
                    <h1 className="text-xl font-semibold">
                        Observar versión del proyecto
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Estudiante: {version.student}
                        <br />
                        Título: {version.title}
                        <br />
                        Versión {version.number}, fase {version.phase}, estado{' '}
                        {version.status}
                        <br />
                        Presentada el {formatDate(version.submitted_at)}
                    </p>
                    <a
                        href={download.url(version.id)}
                        className="text-sm font-medium underline underline-offset-4"
                        data-test="download-version"
                    >
                        Descargar {version.file_name} (
                        {formatSize(version.file_size_bytes)})
                    </a>
                </div>

                <section className="space-y-3" data-test="observations">
                    <h2 className="text-lg font-semibold">
                        Observaciones registradas
                    </h2>
                    {observations.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            Esta versión aún no tiene observaciones.
                        </p>
                    ) : (
                        <ul className="space-y-1 text-sm">
                            {observations.map((observation) => (
                                <li
                                    key={observation.id}
                                    className="rounded bg-muted p-2"
                                >
                                    <span className="block whitespace-pre-line">
                                        {observation.body}
                                    </span>
                                    <span className="text-xs text-muted-foreground">
                                        {observation.author},{' '}
                                        {formatDate(observation.recorded_at)}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>

                <Form
                    {...store.form(version.id)}
                    onBefore={checkTexts}
                    onSuccess={() => setTexts([''])}
                >
                    {({ processing, errors }) => (
                        <div className="grid gap-4">
                            <Label>Nuevas observaciones</Label>
                            {texts.map((text, position) => (
                                <div key={position} className="grid gap-1">
                                    <Textarea
                                        name="observations[]"
                                        value={text}
                                        maxLength={2000}
                                        rows={3}
                                        aria-label={`Observación ${position + 1}`}
                                        onChange={(event) =>
                                            setTexts((current) =>
                                                current.map((item, i) =>
                                                    i === position
                                                        ? event.target.value
                                                        : item,
                                                ),
                                            )
                                        }
                                    />
                                    <InputError
                                        message={
                                            errors[`observations.${position}`]
                                        }
                                    />
                                    {texts.length > 1 && (
                                        <div>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                onClick={() =>
                                                    setTexts((current) =>
                                                        current.filter(
                                                            (_, i) =>
                                                                i !== position,
                                                        ),
                                                    )
                                                }
                                            >
                                                Quitar observación
                                            </Button>
                                        </div>
                                    )}
                                </div>
                            ))}
                            <InputError
                                message={clientError ?? errors.observations}
                            />
                            <InputError message={errors.project_version} />
                            <div>
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        setTexts((current) => [...current, ''])
                                    }
                                >
                                    Agregar observación
                                </Button>
                            </div>

                            <div className="flex flex-wrap gap-2">
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    data-test="confirm-observations-button"
                                >
                                    {processing && <Spinner />}
                                    Confirmar observaciones
                                </Button>
                                <Button asChild variant="outline">
                                    <Link href={show.url(version.process_id)}>
                                        Volver
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    )}
                </Form>
            </div>
        </>
    );
}

TutorObservationsVersion.layout = {
    breadcrumbs: [
        {
            title: 'Mis estudiantes',
            href: index(),
        },
    ],
};
