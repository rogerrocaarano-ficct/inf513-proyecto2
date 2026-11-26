import { Form, Head } from "@inertiajs/react";
import { useState } from "react";
import InputError from "@/components/input-error";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Spinner } from "@/components/ui/spinner";
import { create, store } from "@/routes/project-versions";

type VersionObservation = {
    id: number;
    author: string;
    body: string;
    recorded_at: string;
};

type LastVersion = {
    number: number;
    phase: string;
    status: string;
    title: string;
    submitted_at: string;
    observations: VersionObservation[];
};

type Props = {
    phase: string | null;
    reason: string | null;
    requiresTitle: boolean;
    lastVersion: LastVersion | null;
};

// UC-004 A4: the same texts the server returns, so the client validation reads like the server's.
const messages = {
    title: "Ingrese el título del proyecto de grado.",
    fileRequired: "Adjunte el archivo de la versión.",
    filePdf: "El archivo debe ser PDF.",
    fileSize: "El archivo no debe superar los 20 MB.",
} as const;

const maxFileBytes = 20 * 1024 * 1024;

function fileProblem(file: File | undefined): string | null {
    if (!file) {
        return null;
    }

    if (file.type !== "application/pdf") {
        return messages.filePdf;
    }

    return file.size > maxFileBytes ? messages.fileSize : null;
}

const formatDate = (iso: string): string =>
    new Date(iso).toLocaleString("es-BO", {
        dateStyle: "medium",
        timeStyle: "short",
    });

export default function ProjectVersionsCreate({
    phase,
    reason,
    requiresTitle,
    lastVersion,
}: Props) {
    const [fileError, setFileError] = useState<string | null>(null);

    return (
        <>
            <Head title="Presentar versión" />

            <div className="flex max-w-3xl flex-col gap-6 p-4">
                <div className="space-y-1">
                    <h1 className="text-xl font-semibold">
                        Presentar versión del proyecto
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Fase actual de su trámite de titulación: {phase ?? "—"}
                    </p>
                </div>

                {lastVersion && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex flex-wrap items-center gap-2 text-base">
                                Última versión: versión {lastVersion.number}
                                <Badge variant="outline">
                                    {lastVersion.status}
                                </Badge>
                            </CardTitle>
                            <CardDescription>
                                {lastVersion.phase} ·{" "}
                                {formatDate(lastVersion.submitted_at)}
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            <p className="text-sm">
                                Título del proyecto de grado:{" "}
                                {lastVersion.title}
                            </p>
                            <h2 className="text-sm font-medium">
                                Observaciones
                            </h2>
                            {lastVersion.observations.length === 0 ? (
                                <p className="text-sm text-muted-foreground">
                                    Esta versión no tiene observaciones.
                                </p>
                            ) : (
                                <ul className="space-y-2">
                                    {lastVersion.observations.map(
                                        (observation) => (
                                            <li
                                                key={observation.id}
                                                className="rounded-md border p-3 text-sm"
                                            >
                                                <p className="text-xs text-muted-foreground">
                                                    {observation.author} ·{" "}
                                                    {formatDate(
                                                        observation.recorded_at,
                                                    )}
                                                </p>
                                                <p className="mt-1 break-words whitespace-pre-line">
                                                    {observation.body}
                                                </p>
                                            </li>
                                        ),
                                    )}
                                </ul>
                            )}
                        </CardContent>
                    </Card>
                )}

                {reason ? (
                    <p
                        className="rounded-md border p-3 text-sm"
                        data-test="not-admitted-reason"
                    >
                        {reason}
                    </p>
                ) : (
                    <Form
                        {...store.form()}
                        encType="multipart/form-data"
                        className="grid gap-6"
                    >
                        {({ processing, errors }) => (
                            <>
                                <InputError message={errors.project_version} />

                                {requiresTitle && (
                                    <div className="grid gap-2">
                                        <Label htmlFor="title">
                                            Título del proyecto de grado
                                        </Label>
                                        <Input
                                            id="title"
                                            name="title"
                                            maxLength={300}
                                            required
                                            onInvalid={(event) =>
                                                event.currentTarget.setCustomValidity(
                                                    messages.title,
                                                )
                                            }
                                            onInput={(event) =>
                                                event.currentTarget.setCustomValidity(
                                                    "",
                                                )
                                            }
                                        />
                                        <InputError message={errors.title} />
                                    </div>
                                )}

                                <div className="grid gap-2">
                                    <Label htmlFor="file">
                                        Archivo de la versión (PDF, hasta 20 MB)
                                    </Label>
                                    <Input
                                        id="file"
                                        name="file"
                                        type="file"
                                        accept="application/pdf"
                                        required
                                        aria-invalid={fileError !== null}
                                        onInvalid={(event) => {
                                            event.currentTarget.setCustomValidity(
                                                fileError ??
                                                    messages.fileRequired,
                                            );
                                        }}
                                        onChange={(event) => {
                                            const problem = fileProblem(
                                                event.target.files?.[0],
                                            );
                                            event.target.setCustomValidity(
                                                problem ?? "",
                                            );
                                            setFileError(problem);
                                        }}
                                    />
                                    <InputError
                                        message={fileError ?? errors.file}
                                    />
                                </div>

                                <div>
                                    <Button
                                        type="submit"
                                        disabled={processing}
                                        data-test="submit-version-button"
                                    >
                                        {processing && <Spinner />}
                                        Presentar versión
                                    </Button>
                                </div>
                            </>
                        )}
                    </Form>
                )}
            </div>
        </>
    );
}

ProjectVersionsCreate.layout = {
    breadcrumbs: [
        {
            title: "Presentar versión",
            href: create(),
        },
    ],
};
