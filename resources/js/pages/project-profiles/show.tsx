import { Form, Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { download, index, update } from '@/routes/project-profiles';

type PreviousProfile = {
    id: number;
    number: number;
    title: string;
    file_name: string;
    submitted_at: string;
    evaluated_at: string | null;
    observations: {
        id: number;
        author: string;
        body: string;
        recorded_at: string;
    }[];
};

type Props = {
    profile: {
        id: number;
        student: string;
        title: string;
        number: number;
        file_name: string;
        file_size_bytes: number;
        submitted_at: string;
    };
    previousProfiles: PreviousProfile[];
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

export default function ProjectProfilesShow({
    profile,
    previousProfiles,
}: Props) {
    const [observations, setObservations] = useState<string[]>(['']);
    const [decision, setDecision] = useState('');
    const [clientError, setClientError] = useState<string | null>(null);

    // UC-005 A2: a rejection needs at least one observation (the server validates it too).
    const checkRejection = (): boolean => {
        if (
            decision === 'rechazar' &&
            observations.every((text) => text.trim() === '')
        ) {
            setClientError('El rechazo necesita al menos una observación.');

            return false;
        }

        setClientError(null);

        return true;
    };

    return (
        <>
            <Head title="Evaluar perfil de proyecto" />

            <div className="max-w-3xl space-y-6 p-4">
                <div className="space-y-1">
                    <h1 className="text-xl font-semibold">
                        Evaluar perfil de proyecto
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Estudiante: {profile.student}
                        <br />
                        Título: {profile.title}
                        <br />
                        Versión {profile.number}, presentado el{' '}
                        {formatDate(profile.submitted_at)}
                    </p>
                    <a
                        href={download.url(profile.id)}
                        className="text-sm font-medium underline underline-offset-4"
                        data-test="download-profile"
                    >
                        Descargar {profile.file_name} (
                        {formatSize(profile.file_size_bytes)})
                    </a>
                </div>

                {previousProfiles.length > 0 && (
                    <section className="space-y-3" data-test="previous-profiles">
                        <h2 className="text-lg font-semibold">
                            Perfiles de proyecto rechazados anteriores
                        </h2>
                        {previousProfiles.map((previous) => (
                            <div
                                key={previous.id}
                                className="space-y-2 rounded-md border p-3"
                            >
                                <p className="text-sm font-medium">
                                    Versión {previous.number}: {previous.title}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    Presentado el{' '}
                                    {formatDate(previous.submitted_at)}
                                    {previous.evaluated_at &&
                                        `, rechazado el ${formatDate(previous.evaluated_at)}`}
                                </p>
                                <a
                                    href={download.url(previous.id)}
                                    className="text-sm underline underline-offset-4"
                                >
                                    Descargar {previous.file_name}
                                </a>
                                <ul className="space-y-1 text-sm">
                                    {previous.observations.map(
                                        (observation) => (
                                            <li
                                                key={observation.id}
                                                className="rounded bg-muted p-2"
                                            >
                                                <span className="block whitespace-pre-line">
                                                    {observation.body}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    {observation.author},{' '}
                                                    {formatDate(
                                                        observation.recorded_at,
                                                    )}
                                                </span>
                                            </li>
                                        ),
                                    )}
                                </ul>
                            </div>
                        ))}
                    </section>
                )}

                <Form {...update.form(profile.id)} onBefore={checkRejection}>
                    {({ processing, errors }) => (
                        <div className="grid gap-6">
                            <fieldset className="grid gap-2">
                                <legend className="text-sm font-medium">
                                    Resultado de la evaluación
                                </legend>
                                <Label className="font-normal">
                                    <input
                                        type="radio"
                                        name="decision"
                                        value="aprobar"
                                        required
                                        checked={decision === 'aprobar'}
                                        onChange={() => setDecision('aprobar')}
                                    />
                                    Aprobar
                                </Label>
                                <Label className="font-normal">
                                    <input
                                        type="radio"
                                        name="decision"
                                        value="rechazar"
                                        required
                                        checked={decision === 'rechazar'}
                                        onChange={() => setDecision('rechazar')}
                                    />
                                    Rechazar
                                </Label>
                                <InputError message={errors.decision} />
                            </fieldset>

                            <div className="grid gap-3">
                                <Label>
                                    Observaciones (obligatoria al rechazar)
                                </Label>
                                {observations.map((text, position) => (
                                    <Textarea
                                        key={position}
                                        name="observations[]"
                                        value={text}
                                        maxLength={2000}
                                        rows={3}
                                        aria-label={`Observación ${position + 1}`}
                                        onChange={(event) =>
                                            setObservations((current) =>
                                                current.map((item, i) =>
                                                    i === position
                                                        ? event.target.value
                                                        : item,
                                                ),
                                            )
                                        }
                                    />
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
                                            setObservations((current) => [
                                                ...current,
                                                '',
                                            ])
                                        }
                                    >
                                        Agregar observación
                                    </Button>
                                </div>
                            </div>

                            <div className="flex flex-wrap gap-2">
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    data-test="confirm-evaluation-button"
                                >
                                    {processing && <Spinner />}
                                    Confirmar evaluación
                                </Button>
                                <Button asChild variant="outline">
                                    <Link href={index.url()}>Cancelar</Link>
                                </Button>
                            </div>
                        </div>
                    )}
                </Form>
            </div>
        </>
    );
}

ProjectProfilesShow.layout = {
    breadcrumbs: [
        {
            title: 'Perfiles de proyecto',
            href: index(),
        },
    ],
};
