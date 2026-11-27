import { Form, Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { download, index, store } from '@/routes/committee-reviews';

type Pronouncement = {
    name: string;
    pronounced: boolean;
    approved: boolean;
    pronounced_at: string | null;
};

type VersionObservation = {
    id: number;
    author: string;
    body: string;
    recorded_at: string;
};

type PreviousVersion = {
    id: number;
    number: number;
    phase: string;
    status: string;
    title: string;
    submitted_at: string;
    evaluated_at: string | null;
    observations: VersionObservation[];
};

type Props = {
    version: {
        id: number;
        student: string;
        title: string;
        number: number;
        phase: string;
        status: string;
        file_name: string;
        file_size_bytes: number;
        submitted_at: string;
    };
    members: Pronouncement[];
    observations: VersionObservation[];
    previousVersions: PreviousVersion[];
    canPronounce: boolean;
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

export default function CommitteeReviewsShow({
    version,
    members,
    observations,
    previousVersions,
    canPronounce,
}: Props) {
    const [decision, setDecision] = useState('');
    const [texts, setTexts] = useState<string[]>(['']);
    const [clientError, setClientError] = useState<string | null>(null);

    // UC-008 A4, BR-002, BR-006: the server validates too; this only keeps what was written.
    const checkTexts = (): boolean => {
        if (texts.some((text) => text !== '' && text.trim() === '')) {
            setClientError('Escriba la observación.');

            return false;
        }

        if (texts.some((text) => text.length > 2000)) {
            setClientError('Cada observación admite como máximo 2000 caracteres.');

            return false;
        }

        if (decision === 'observar' && texts.every((text) => text.trim() === '')) {
            setClientError('El pronunciamiento necesita al menos una observación.');

            return false;
        }

        setClientError(null);

        return true;
    };

    return (
        <>
            <Head title="Revisar versión del proyecto" />

            <div className="max-w-3xl space-y-6 p-4">
                <div className="space-y-1">
                    <h1 className="text-xl font-semibold">
                        Revisar versión del proyecto
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

                <section className="space-y-3" data-test="committee-members">
                    <h2 className="text-lg font-semibold">
                        Pronunciamiento del tribunal evaluador
                    </h2>
                    <ul className="space-y-2 text-sm">
                        {members.map((member) => (
                            <li
                                key={member.name}
                                className="rounded bg-muted p-2"
                            >
                                <span className="block font-medium">
                                    {member.name}
                                </span>
                                <span className="text-xs text-muted-foreground">
                                    {member.approved
                                        ? 'Aprobó'
                                        : member.pronounced
                                          ? 'Registró observaciones'
                                          : 'Pendiente'}
                                    {member.pronounced_at === null
                                        ? ''
                                        : `, ${formatDate(member.pronounced_at)}`}
                                </span>
                            </li>
                        ))}
                    </ul>
                </section>

                <section className="space-y-3" data-test="version-observations">
                    <h2 className="text-lg font-semibold">
                        Observaciones de esta versión
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

                {previousVersions.length > 0 && (
                    <section className="space-y-3" data-test="previous-versions">
                        <h2 className="text-lg font-semibold">
                            Versiones anteriores
                        </h2>
                        {previousVersions.map((previous) => (
                            <div
                                key={previous.id}
                                className="space-y-2 rounded-md border p-3"
                            >
                                <p className="text-sm font-medium">
                                    Versión {previous.number}, fase{' '}
                                    {previous.phase}
                                    <Badge className="ml-2" variant="outline">
                                        {previous.status}
                                    </Badge>
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    {previous.title}; presentada el{' '}
                                    {formatDate(previous.submitted_at)}
                                </p>
                                <ul className="space-y-1 text-sm">
                                    {previous.observations.map((observation) => (
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
                            </div>
                        ))}
                    </section>
                )}

                {canPronounce ? (
                    <Form
                        {...store.form(version.id)}
                        onBefore={checkTexts}
                        onSuccess={() => {
                            setDecision('');
                            setTexts(['']);
                        }}
                    >
                        {({ processing, errors }) => (
                            <div className="grid gap-4">
                                <fieldset className="grid gap-2">
                                    <legend className="text-sm font-medium">
                                        Pronunciamiento
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
                                        Aprobar la versión
                                    </Label>
                                    <Label className="font-normal">
                                        <input
                                            type="radio"
                                            name="decision"
                                            value="observar"
                                            required
                                            checked={decision === 'observar'}
                                            onChange={() => setDecision('observar')}
                                        />
                                        Observar sin aprobar
                                    </Label>
                                    <InputError message={errors.decision} />
                                </fieldset>

                                <div className="grid gap-3">
                                    <Label>
                                        Observaciones o sugerencias
                                        {decision === 'observar'
                                            ? ' (obligatorias)'
                                            : ' (opcionales)'}
                                    </Label>
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
                                                message={errors[`observations.${position}`]}
                                            />
                                            {texts.length > 1 && (
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
                                                setTexts((current) => [
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
                                        data-test="confirm-pronouncement-button"
                                    >
                                        {processing && <Spinner />}
                                        Confirmar pronunciamiento
                                    </Button>
                                    <Button asChild variant="outline">
                                        <Link href={index.url()}>Volver</Link>
                                    </Button>
                                </div>
                            </div>
                        )}
                    </Form>
                ) : (
                    <p
                        className="rounded-md border p-4 text-muted-foreground"
                        data-test="cannot-pronounce"
                    >
                        El pronunciamiento ya no puede registrarse.
                    </p>
                )}
            </div>
        </>
    );
}

CommitteeReviewsShow.layout = {
    breadcrumbs: [
        {
            title: 'Revisión de versiones',
            href: index(),
        },
    ],
};
