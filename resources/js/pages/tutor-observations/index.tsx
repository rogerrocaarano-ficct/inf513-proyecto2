import { Head, Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { index, show } from '@/routes/tutor-observations';

type ProcessRow = {
    id: number;
    student: string;
    phase: string;
    has_unobserved_version: boolean;
    last_version: {
        number: number;
        status: string;
        submitted_at: string;
    } | null;
};

type Props = {
    processes: ProcessRow[];
};

function formatDate(value: string): string {
    return new Date(value).toLocaleString('es-BO', {
        dateStyle: 'short',
        timeStyle: 'short',
    });
}

export default function TutorObservationsIndex({ processes }: Props) {
    return (
        <>
            <Head title="Mis estudiantes" />

            <div className="space-y-4 p-4">
                <h1 className="text-xl font-semibold">Mis estudiantes</h1>
                <p className="text-sm text-muted-foreground">
                    Trámites de titulación en curso de los que es tutor, primero
                    los que tienen una versión del proyecto que aún no observó.
                </p>

                {processes.length === 0 ? (
                    <p
                        className="rounded-md border p-4 text-center text-muted-foreground"
                        data-test="no-processes"
                    >
                        No tiene trámites de titulación en curso como tutor.
                    </p>
                ) : (
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Estudiante</TableHead>
                                <TableHead>Fase</TableHead>
                                <TableHead>Última versión</TableHead>
                                <TableHead>
                                    <span className="sr-only">Acción</span>
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {processes.map((process) => (
                                <TableRow key={process.id}>
                                    <TableCell>{process.student}</TableCell>
                                    <TableCell>{process.phase}</TableCell>
                                    <TableCell className="whitespace-normal">
                                        {process.last_version ? (
                                            <>
                                                Versión{' '}
                                                {process.last_version.number}
                                                <span className="block text-xs text-muted-foreground">
                                                    {process.last_version.status}
                                                    ,{' '}
                                                    {formatDate(
                                                        process.last_version
                                                            .submitted_at,
                                                    )}
                                                </span>
                                            </>
                                        ) : (
                                            <span className="text-muted-foreground">
                                                Sin versiones
                                            </span>
                                        )}
                                        {process.has_unobserved_version && (
                                            <Badge
                                                variant="outline"
                                                className="mt-1 block w-fit"
                                            >
                                                Sin observar
                                            </Badge>
                                        )}
                                    </TableCell>
                                    <TableCell className="text-right">
                                        <Button asChild size="sm">
                                            <Link href={show.url(process.id)}>
                                                Ver versiones
                                            </Link>
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                )}
            </div>
        </>
    );
}

TutorObservationsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Mis estudiantes',
            href: index(),
        },
    ],
};
