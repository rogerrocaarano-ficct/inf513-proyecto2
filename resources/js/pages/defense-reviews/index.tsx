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
import { index, show } from '@/routes/defense-reviews';

type PendingPreDefense = {
    id: number;
    student: string;
    scheduled_at: string;
    location: string | null;
    schedule_revision: number;
    reported_by: number;
};

type Props = {
    preDefenses: PendingPreDefense[];
    message: string | null;
};

function formatSchedule(value: string): string {
    return new Date(value).toLocaleString('es-BO', {
        dateStyle: 'short',
        timeStyle: 'short',
    });
}

/**
 * UC-010 steps 1-3 and A4: the pending pre-defenses of the member's current tribunals.
 */
export default function DefenseReviewsIndex({ preDefenses, message }: Props) {
    return (
        <>
            <Head title="Registrar pre-defensa" />
            <div className="space-y-4 p-4">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div className="space-y-1">
                        <h1 className="text-xl font-semibold">
                            Pre-defensas pendientes de registro
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Seleccione una pre-defensa para registrar sus
                            observaciones sobre el documento y el prototipo.
                        </p>
                    </div>
                    <Button asChild variant="outline">
                        <Link href={index.url()}>Actualizar lista</Link>
                    </Button>
                </div>

                {message ? (
                    <p
                        className="rounded-md border p-4 text-sm text-muted-foreground"
                        data-test="no-pending-pre-defenses"
                    >
                        {message}
                    </p>
                ) : (
                    <Table data-test="pending-pre-defenses">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Estudiante</TableHead>
                                <TableHead>Fecha y hora de inicio</TableHead>
                                <TableHead>Ambiente</TableHead>
                                <TableHead>Registros del tribunal</TableHead>
                                <TableHead className="text-right">
                                    Acción
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {preDefenses.map((act) => (
                                <TableRow key={act.id}>
                                    <TableCell className="font-medium">
                                        {act.student}
                                    </TableCell>
                                    <TableCell>
                                        {formatSchedule(act.scheduled_at)}
                                    </TableCell>
                                    <TableCell>
                                        {act.location ?? 'Sin ambiente'}
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="secondary">
                                            {act.reported_by} de 3 registraron
                                        </Badge>
                                    </TableCell>
                                    <TableCell className="text-right">
                                        <Button asChild size="sm">
                                            <Link href={show.url(act.id)}>
                                                Registrar pre-defensa
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
