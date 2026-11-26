import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { create, store } from '@/routes/graduation-processes';

type Lookup = {
    status: 'not_registered' | 'in_progress' | 'approved' | 'returning';
    canStart: boolean;
    studentName: string | null;
    idCard: string | null;
    phase: string | null;
    previousProcesses: { startedAt: string; outcome: string }[];
};

type Props = {
    tutors: { id: number; name: string }[];
    registrationNumber: string | null;
    lookup: Lookup | null;
};

const selectClassName =
    'border-input flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-base shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm';

const documents = [
    { name: 'enrollment_slip', label: 'Boleta de inscripción' },
    { name: 'curriculum_vitae', label: 'Hoja de vida' },
    { name: 'request_letter', label: 'Carta de solicitud' },
] as const;

const maxDocumentBytes = 20 * 1024 * 1024;

function documentProblem(file: File | undefined): string | null {
    if (!file) {
        return null;
    }

    if (file.type !== 'application/pdf') {
        return 'El archivo debe ser PDF.';
    }

    if (file.size > maxDocumentBytes) {
        return 'El archivo no debe superar los 20 MB.';
    }

    return null;
}

function DocumentInput({
    name,
    label,
    serverError,
}: {
    name: string;
    label: string;
    serverError?: string;
}) {
    const [clientError, setClientError] = useState<string | null>(null);

    return (
        <div className="grid gap-2">
            <Label htmlFor={name}>{label} (PDF, hasta 20 MB)</Label>
            <Input
                id={name}
                name={name}
                type="file"
                accept="application/pdf"
                required
                aria-invalid={clientError !== null}
                onChange={(event) => {
                    const problem = documentProblem(event.target.files?.[0]);
                    event.target.setCustomValidity(problem ?? '');
                    setClientError(problem);
                }}
            />
            <InputError message={clientError ?? serverError} />
        </div>
    );
}

function formatDate(isoDate: string): string {
    return new Date(isoDate).toLocaleDateString('es-BO');
}

export default function CreateGraduationProcess({
    tutors,
    registrationNumber,
    lookup,
}: Props) {
    return (
        <>
            <Head title="Iniciar trámite de titulación" />

            <div className="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4">
                <h1 className="text-2xl font-semibold">
                    Iniciar trámite de titulación
                </h1>

                <Card>
                    <CardHeader>
                        <CardTitle>Estudiante</CardTitle>
                        <CardDescription>
                            Ingrese el número de registro universitario para
                            verificar si el estudiante ya está registrado.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Form
                            {...create.form()}
                            className="flex flex-col gap-3 sm:flex-row sm:items-end"
                        >
                            {({ processing }) => (
                                <>
                                    <div className="grid flex-1 gap-2">
                                        <Label htmlFor="lookup_registration_number">
                                            Número de registro universitario
                                        </Label>
                                        <Input
                                            id="lookup_registration_number"
                                            name="registration_number"
                                            defaultValue={
                                                registrationNumber ?? ''
                                            }
                                            required
                                            maxLength={20}
                                            autoFocus
                                        />
                                    </div>
                                    <Button
                                        type="submit"
                                        disabled={processing}
                                        data-test="lookup-student-button"
                                    >
                                        {processing && <Spinner />}
                                        Buscar
                                    </Button>
                                </>
                            )}
                        </Form>

                        {lookup?.status === 'in_progress' && (
                            <Alert className="mt-4" data-test="lookup-in-progress">
                                <AlertTitle>Trámite en curso</AlertTitle>
                                <AlertDescription>
                                    {lookup.studentName} ya tiene un trámite de
                                    titulación en curso, en la fase{' '}
                                    {lookup.phase}.
                                </AlertDescription>
                            </Alert>
                        )}

                        {lookup?.status === 'approved' && (
                            <Alert className="mt-4" data-test="lookup-approved">
                                <AlertTitle>Trámite aprobado</AlertTitle>
                                <AlertDescription>
                                    {lookup.studentName} ya aprobó su trámite de
                                    titulación.
                                </AlertDescription>
                            </Alert>
                        )}

                        {lookup?.status === 'returning' && (
                            <div className="mt-4 grid gap-3" data-test="lookup-returning">
                                <p className="text-sm">
                                    <span className="font-medium">
                                        {lookup.studentName}
                                    </span>
                                    , cédula de identidad {lookup.idCard}
                                </p>
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>
                                                Trámite de titulación anterior
                                            </TableHead>
                                            <TableHead>Resultado</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {lookup.previousProcesses.map(
                                            (process, index) => (
                                                <TableRow key={index}>
                                                    <TableCell>
                                                        Iniciado el{' '}
                                                        {formatDate(
                                                            process.startedAt,
                                                        )}
                                                    </TableCell>
                                                    <TableCell>
                                                        {process.outcome}
                                                    </TableCell>
                                                </TableRow>
                                            ),
                                        )}
                                    </TableBody>
                                </Table>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {lookup?.canStart && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Datos del trámite</CardTitle>
                            <CardDescription>
                                {lookup.status === 'returning'
                                    ? 'El estudiante ya está registrado: adjunte su nueva documentación de requisitos.'
                                    : 'Ingrese los datos del estudiante y adjunte su documentación de requisitos.'}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Form
                                {...store.form()}
                                className="grid gap-6"
                                disableWhileProcessing
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <input
                                            type="hidden"
                                            name="registration_number"
                                            value={registrationNumber ?? ''}
                                        />

                                        {lookup.status === 'returning' ? (
                                            <div className="grid gap-2">
                                                <label className="flex items-center gap-2 text-sm">
                                                    <input
                                                        type="checkbox"
                                                        name="confirmed_student"
                                                        value="1"
                                                        required
                                                        data-test="confirmed-student"
                                                    />
                                                    Confirmo que{' '}
                                                    {lookup.studentName} es el
                                                    estudiante que se presenta.
                                                </label>
                                                <InputError
                                                    message={
                                                        errors.confirmed_student
                                                    }
                                                />
                                            </div>
                                        ) : (
                                            <div className="grid gap-4 sm:grid-cols-2">
                                                <div className="grid gap-2 sm:col-span-2">
                                                    <Label htmlFor="name">
                                                        Nombre completo
                                                    </Label>
                                                    <Input
                                                        id="name"
                                                        name="name"
                                                        required
                                                        maxLength={255}
                                                        autoComplete="off"
                                                    />
                                                    <InputError
                                                        message={errors.name}
                                                    />
                                                </div>
                                                <div className="grid gap-2 sm:col-span-2">
                                                    <Label htmlFor="email">
                                                        Correo electrónico
                                                    </Label>
                                                    <Input
                                                        id="email"
                                                        name="email"
                                                        type="email"
                                                        required
                                                        maxLength={255}
                                                        autoComplete="off"
                                                    />
                                                    <InputError
                                                        message={errors.email}
                                                    />
                                                </div>
                                                <div className="grid gap-2">
                                                    <Label htmlFor="id_card_number">
                                                        Número de cédula de
                                                        identidad
                                                    </Label>
                                                    <Input
                                                        id="id_card_number"
                                                        name="id_card_number"
                                                        required
                                                        inputMode="numeric"
                                                        pattern="[0-9]+"
                                                        title="Solo dígitos"
                                                        maxLength={20}
                                                        autoComplete="off"
                                                    />
                                                    <InputError
                                                        message={
                                                            errors.id_card_number
                                                        }
                                                    />
                                                </div>
                                                <div className="grid gap-2">
                                                    <Label htmlFor="id_card_complement">
                                                        Complemento (opcional)
                                                    </Label>
                                                    <Input
                                                        id="id_card_complement"
                                                        name="id_card_complement"
                                                        pattern="[0-9A-Za-z]+"
                                                        title="Solo letras y dígitos"
                                                        maxLength={5}
                                                        autoComplete="off"
                                                    />
                                                    <InputError
                                                        message={
                                                            errors.id_card_complement
                                                        }
                                                    />
                                                </div>
                                                <div className="grid gap-2">
                                                    <Label htmlFor="phone">
                                                        Teléfono (opcional)
                                                    </Label>
                                                    <Input
                                                        id="phone"
                                                        name="phone"
                                                        type="tel"
                                                        pattern="(\+591)?[0-9]{7,8}"
                                                        title="7 u 8 dígitos, con el prefijo +591 opcional"
                                                        maxLength={12}
                                                        autoComplete="off"
                                                    />
                                                    <InputError
                                                        message={errors.phone}
                                                    />
                                                </div>
                                            </div>
                                        )}

                                        <div className="grid gap-4">
                                            <h2 className="text-lg font-medium">
                                                Documentación de requisitos
                                            </h2>
                                            {documents.map((document) => (
                                                <DocumentInput
                                                    key={document.name}
                                                    name={document.name}
                                                    label={document.label}
                                                    serverError={
                                                        errors[document.name]
                                                    }
                                                />
                                            ))}
                                        </div>

                                        <div className="grid gap-4">
                                            <h2 className="text-lg font-medium">
                                                Tutor
                                            </h2>
                                            <div className="grid gap-2">
                                                <Label htmlFor="tutor_id">
                                                    Tutor propuesto en la carta
                                                    de solicitud
                                                </Label>
                                                <select
                                                    id="tutor_id"
                                                    name="tutor_id"
                                                    defaultValue=""
                                                    className={selectClassName}
                                                >
                                                    <option value="">
                                                        Sin tutor
                                                    </option>
                                                    {tutors.map((tutor) => (
                                                        <option
                                                            key={tutor.id}
                                                            value={tutor.id}
                                                        >
                                                            {tutor.name}
                                                        </option>
                                                    ))}
                                                </select>
                                                <InputError
                                                    message={errors.tutor_id}
                                                />
                                            </div>
                                            <div className="grid gap-2">
                                                <Label htmlFor="proposed_tutor_name">
                                                    Nombre del tutor propuesto,
                                                    si no figura entre los
                                                    docentes elegibles
                                                    (opcional)
                                                </Label>
                                                <Input
                                                    id="proposed_tutor_name"
                                                    name="proposed_tutor_name"
                                                    maxLength={150}
                                                    autoComplete="off"
                                                />
                                                <InputError
                                                    message={
                                                        errors.proposed_tutor_name
                                                    }
                                                />
                                            </div>
                                        </div>

                                        <InputError
                                            message={errors.registration_number}
                                        />

                                        <Button
                                            type="submit"
                                            disabled={processing}
                                            data-test="start-graduation-process-button"
                                        >
                                            {processing && <Spinner />}
                                            Iniciar trámite de titulación
                                        </Button>
                                    </>
                                )}
                            </Form>
                        </CardContent>
                    </Card>
                )}
            </div>
        </>
    );
}

CreateGraduationProcess.layout = {
    breadcrumbs: [
        {
            title: 'Iniciar trámite de titulación',
            href: create(),
        },
    ],
};
