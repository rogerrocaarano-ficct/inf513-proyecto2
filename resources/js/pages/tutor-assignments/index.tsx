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
import { edit, index } from '@/routes/tutor-assignments';

type ProcessRow = {
    id: number;
    student: string;
    phase: string;
    tutor: string | null;
    proposedTutor: string | null;
};

type Props = {
    processes: ProcessRow[];
};

export default function TutorAssignmentsIndex({ processes }: Props) {
    return (
        <>
            <Head title="Asignar tutor" />

            <div className="flex flex-col gap-4 p-4">
                <h1 className="text-xl font-semibold">Asignar tutor</h1>

                {processes.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        No hay trámites de titulación en curso.
                    </p>
                ) : (
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Estudiante</TableHead>
                                <TableHead>Fase</TableHead>
                                <TableHead>Tutor</TableHead>
                                <TableHead>Tutor propuesto</TableHead>
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
                                    <TableCell>
                                        {process.tutor ?? (
                                            <Badge variant="outline">
                                                Sin tutor
                                            </Badge>
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        {process.proposedTutor ?? '—'}
                                    </TableCell>
                                    <TableCell className="text-right">
                                        <Button asChild size="sm">
                                            <Link
                                                href={edit(process.id)}
                                                data-test={`assign-tutor-${process.id}`}
                                            >
                                                {process.tutor
                                                    ? 'Cambiar tutor'
                                                    : 'Asignar tutor'}
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

TutorAssignmentsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Asignar tutor',
            href: index(),
        },
    ],
};
