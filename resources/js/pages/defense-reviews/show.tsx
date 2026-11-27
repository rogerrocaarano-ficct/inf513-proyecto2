import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { index, store } from '@/routes/defense-reviews';

type DecisionValue = 'observaciones' | 'sin-observaciones' | 'inasistencia';

type DecisionOption = {
    value: DecisionValue;
    label: string;
};

type Version = {
    id: number;
    number: number;
    title: string;
    phase: string;
    status: string;
    file_name: string;
    file_size_bytes: number;
    submitted_at: string;
    download_url: string;
};

type PreDefense = {
    id: number;
    student: string;
    scheduled_at: string;
    schedule_revision: number;
    location: string | null;
    type: string;
    version: Version | null;
};

type ReportedBy = {
    id: number;
    name: string;
    recorded_at: string;
};

type Props = {
    preDefense: PreDefense;
    reportedBy: ReportedBy[];
    decisions: DecisionOption[];
};

type ClientErrors = {
    decision?: string;
    observations?: string;
    rows?: Record<number, string>;
};

const MAX_OBSERVATION_LENGTH = 2000;

function formatDate(value: string): string {
    return new Date(value).toLocaleString('es-BO', {
        dateStyle: 'short',
        timeStyle: 'short',
    });
}

function formatSize(bytes: number): string {
    return `${(bytes / 1024).toFixed(1)} KB`;
}

/**
 * UC-010 steps 3-11: consult the approved version and register a decision.
 * The form data is owned by useForm, so what the member wrote survives A3 errors.
 */
export default function DefenseReviewsShow({
    preDefense,
    reportedBy,
    decisions,
}: Props) {
    const form = useForm({
        decision: '' as DecisionValue | '',
        observations: [''],
        schedule_revision: preDefense.schedule_revision,
    });
    const [clientErrors, setClientErrors] = useState<ClientErrors>({});

    function clearDecisionError() {
        form.clearErrors('decision');
        setClientErrors((current) => ({ ...current, decision: undefined }));
    }

    function selectDecision(value: DecisionValue) {
        form.setData('decision', value);
        clearDecisionError();
        form.clearErrors('observations');
        setClientErrors((current) => ({
            ...current,
            observations: undefined,
            rows: undefined,
        }));
    }

    function updateObservation(index: number, value: string) {
        const next = [...form.data.observations];
        next[index] = value;
        form.setData('observations', next);
        form.clearErrors('observations');
        form.clearErrors(`observations.${index}`);
        setClientErrors((current) => ({
            ...current,
            observations: undefined,
            rows: { ...current.rows, [index]: '' },
        }));
    }

    function addObservation() {
        form.setData('observations', [...form.data.observations, '']);
    }

    function removeObservation(index: number) {
        form.setData(
            'observations',
            form.data.observations.filter((_, position) => position !== index),
        );
        setClientErrors((current) => ({
            ...current,
            rows: { ...current.rows, [index]: '' },
        }));
    }

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        const rows: Record<number, string> = {};

        form.data.observations.forEach((text, index) => {
            if (Array.from(text).length > MAX_OBSERVATION_LENGTH) {
                rows[index] =
                    'Cada observación admite como máximo 2000 caracteres.';
            } else if (
                form.data.decision === 'observaciones' &&
                text.trim() === ''
            ) {
                rows[index] = 'La observación no puede estar vacía.';
            }
        });

        const errors: ClientErrors = { rows };

        if (form.data.decision === '') {
            errors.decision =
                'Elija si registra observaciones, que no las hubo o la inasistencia del estudiante.';
        } else if (
            form.data.decision === 'observaciones' &&
            !form.data.observations.some((text) => text.trim() !== '')
        ) {
            errors.observations = 'Registre al menos una observación.';
        }

        setClientErrors(errors);

        if (
            errors.decision ||
            errors.observations ||
            Object.values(rows).some((message) => message !== '')
        ) {
            return;
        }

        form.transform((data) => ({
            ...data,
            observations: data.observations.filter(
                (text) => text.trim() !== '',
            ),
        }));
        form.post(store.url(preDefense.id), {
            preserveScroll: true,
        });
    }

    return (
        <>
            <Head title="Registrar pre-defensa" />
            <div className="space-y-6 p-4">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div className="space-y-1">
                        <h1 className="text-xl font-semibold">
                            Registrar pre-defensa
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Estudiante: {preDefense.student}
                        </p>
                    </div>
                    <Button asChild variant="outline">
                        <Link href={index.url()}>Volver a la lista</Link>
                    </Button>
                </div>

                <section
                    className="space-y-3 rounded-lg border p-4"
                    aria-labelledby="pre-defense-detail-title"
                    data-test="pre-defense-detail"
                >
                    <h2
                        id="pre-defense-detail-title"
                        className="text-lg font-semibold"
                    >
                        Detalle de la pre-defensa
                    </h2>
                    <dl className="grid gap-x-4 gap-y-2 text-sm sm:grid-cols-[auto_1fr]">
                        <dt className="font-medium">Fase</dt>
                        <dd>Pre-defensa</dd>
                        <dt className="font-medium">Fecha y hora de inicio</dt>
                        <dd>{formatDate(preDefense.scheduled_at)}</dd>
                        <dt className="font-medium">Ambiente</dt>
                        <dd>{preDefense.location ?? 'Sin ambiente'}</dd>
                        <dt className="font-medium">
                            Revisión de programación
                        </dt>
                        <dd>{preDefense.schedule_revision}</dd>
                    </dl>
                </section>

                <section
                    className="space-y-3 rounded-lg border p-4"
                    aria-labelledby="version-title"
                    data-test="approved-version"
                >
                    <h2 id="version-title" className="text-lg font-semibold">
                        Versión del proyecto aprobada de la fase pre-defensa
                    </h2>
                    {preDefense.version === null ? (
                        <p className="text-sm text-muted-foreground">
                            No hay una versión aprobada disponible para
                            descargar.
                        </p>
                    ) : (
                        <div className="flex flex-wrap items-center justify-between gap-3">
                            <div className="space-y-1 text-sm">
                                <p className="font-medium">
                                    Versión {preDefense.version.number}:{' '}
                                    {preDefense.version.title}
                                </p>
                                <p className="text-muted-foreground">
                                    {preDefense.version.file_name} ·{' '}
                                    {formatSize(
                                        preDefense.version.file_size_bytes,
                                    )}{' '}
                                    · presentada el{' '}
                                    {formatDate(
                                        preDefense.version.submitted_at,
                                    )}
                                </p>
                            </div>
                            <Button asChild variant="outline" size="sm">
                                <a
                                    href={preDefense.version.download_url}
                                    data-test="download-version"
                                >
                                    Descargar versión
                                </a>
                            </Button>
                        </div>
                    )}
                </section>

                <section
                    className="space-y-3 rounded-lg border p-4"
                    aria-labelledby="reported-by-title"
                    data-test="reported-by"
                >
                    <h2
                        id="reported-by-title"
                        className="text-lg font-semibold"
                    >
                        Registros del tribunal evaluador
                    </h2>
                    {reportedBy.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            Ningún miembro del tribunal registró aún esta
                            pre-defensa.
                        </p>
                    ) : (
                        <ul className="space-y-2 text-sm">
                            {reportedBy.map((member) => (
                                <li
                                    key={member.id}
                                    className="flex flex-wrap items-center justify-between gap-2"
                                >
                                    <span>{member.name}</span>
                                    <Badge variant="secondary">
                                        Registrado el{' '}
                                        {formatDate(member.recorded_at)}
                                    </Badge>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>

                <section
                    className="space-y-4 rounded-lg border p-4"
                    aria-labelledby="review-form-title"
                >
                    <h2
                        id="review-form-title"
                        className="text-lg font-semibold"
                    >
                        Su registro
                    </h2>
                    <form className="space-y-4" onSubmit={submit} noValidate>
                        <fieldset className="space-y-2">
                            <legend className="text-sm font-medium">
                                Decisión
                            </legend>
                            {decisions.map((option) => (
                                <label
                                    key={option.value}
                                    className="flex items-center gap-2 text-sm"
                                >
                                    <input
                                        type="radio"
                                        name="decision"
                                        value={option.value}
                                        checked={
                                            form.data.decision === option.value
                                        }
                                        onChange={() =>
                                            selectDecision(option.value)
                                        }
                                        data-test={`decision-${option.value}`}
                                    />
                                    {option.label}
                                </label>
                            ))}
                            <div aria-live="polite">
                                <InputError
                                    message={
                                        clientErrors.decision ??
                                        form.errors.decision
                                    }
                                />
                            </div>
                        </fieldset>

                        {form.data.decision === 'observaciones' && (
                            <div className="space-y-3" data-test="observations">
                                <div className="flex flex-wrap items-center justify-between gap-2">
                                    <Label htmlFor="observation-0">
                                        Observaciones sobre el documento y el
                                        prototipo
                                    </Label>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        onClick={addObservation}
                                        disabled={form.processing}
                                        data-test="add-observation"
                                    >
                                        Agregar observación
                                    </Button>
                                </div>
                                {form.data.observations.map((text, index) => (
                                    <div
                                        key={index}
                                        className="space-y-2"
                                        data-test={`observation-row-${index}`}
                                    >
                                        <Textarea
                                            id={`observation-${index}`}
                                            value={text}
                                            maxLength={
                                                MAX_OBSERVATION_LENGTH + 1
                                            }
                                            onChange={(event) =>
                                                updateObservation(
                                                    index,
                                                    event.target.value,
                                                )
                                            }
                                            aria-invalid={Boolean(
                                                clientErrors.rows?.[index] ??
                                                form.errors[
                                                    `observations.${index}`
                                                ],
                                            )}
                                            aria-describedby={`observation-error-${index}`}
                                            data-test={`observation-${index}`}
                                        />
                                        <div
                                            id={`observation-error-${index}`}
                                            aria-live="polite"
                                        >
                                            <InputError
                                                message={
                                                    clientErrors.rows?.[
                                                        index
                                                    ] ??
                                                    form.errors[
                                                        `observations.${index}`
                                                    ]
                                                }
                                            />
                                        </div>
                                        {form.data.observations.length > 1 && (
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                onClick={() =>
                                                    removeObservation(index)
                                                }
                                                disabled={form.processing}
                                                data-test={`remove-observation-${index}`}
                                            >
                                                Quitar observación
                                            </Button>
                                        )}
                                    </div>
                                ))}
                                <div aria-live="polite">
                                    <InputError
                                        message={
                                            clientErrors.observations ??
                                            form.errors.observations
                                        }
                                    />
                                </div>
                            </div>
                        )}

                        <div className="flex flex-wrap gap-2">
                            <Button
                                type="submit"
                                disabled={form.processing}
                                data-test="confirm-registration"
                            >
                                Confirmar registro
                            </Button>
                        </div>
                    </form>
                </section>
            </div>
        </>
    );
}
