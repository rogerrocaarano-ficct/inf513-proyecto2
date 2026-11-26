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
import { index, show } from '@/routes/committee-reviews';

type ProcessRow = {
    id: number;
    student: string;
    project_title: string | null;
    phase: string;
    pending_version: {
        id: number;
        number: number;
        submitted_at: string;
    } | null;
    last_version: {
        id: number;
        number: number;
        phase: string;
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

export default function CommitteeReviewsIndex({ processes }: Props) {
    const hasPending = processes.some((process) => process.pending_version !== null);

    return (
        <>
            <Head title="Revisión de versiones" />

            <div className="space-y-4 p-4">
                <h1 className="text-xl font-semibold">Revisión de versiones</h1>
                <p className="text-sm text-muted-foreground">
                    Trámites de titulación de su tribunal evaluador, primero los
                    que esperan su pronunciamiento.
                </p>

                {!hasPending && (
                    <p
                        className="rounded-md border p-4 text-center text-muted-foreground"
                        data-test="no-pending-versions"
                    >
                        No hay versiones del proyecto pendientes de su revisión.
                    </p>
                )}

                {processes.length > 0 && (
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Estudiante</TableHead>
                                <TableHead>Título del proyecto</TableHead>
                                <TableHead>Última versión</TableHead>
                                <TableHead>
                                    <span className="sr-only">Acción</span>
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {processes.map((process) => (
                                <TableRow key={process.id}>
                                    <TableCell>
                                        {process.student}
                                        <span className="block text-xs text-muted-foreground">
                                            {process.phase}
                                        </span>
                                    </TableCell>
                                    <TableCell className="whitespace-normal">
                                        {process.project_title ?? 'Sin título'}
                                    </TableCell>
                                    <TableCell>
                                        {process.last_version === null ? (
                                            <span className="text-muted-foreground">
                                                Sin versiones
                                            </span>
                                        ) : (
                                            <>
                                                <span className="block">
                                                    Versión {process.last_version.number}, fase{' '}
                                                    {process.last_version.phase}
                                                </span>
                                                <Badge variant="outline">
                                                    {process.last_version.status}
                                                </Badge>
                                                <span className="block text-xs text-muted-foreground">
                                                    {formatDate(process.last_version.submitted_at)}
                                                </span>
                                            </>
                                        )}
                                        {process.pending_version !== null && (
                                            <Badge className="ml-2" data-test="pending-badge">
                                                Pendiente de su pronunciamiento
                                            </Badge>
                                        )}
                                    </TableCell>
                                    <TableCell className="text-right">
                                        {process.pending_version !== null && (
                                            <Button asChild size="sm">
                                                <Link
                                                    href={show.url(
                                                        process.pending_version.id,
                                                    )}
                                                >
                                                    Revisar
                                                </Link>
                                            </Button>
                                        )}
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

CommitteeReviewsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Revisión de versiones',
            href: index(),
        },
    ],
};
