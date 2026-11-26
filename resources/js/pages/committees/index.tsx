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
import { edit, index } from '@/routes/committees';

type CommitteeProcess = {
    id: number;
    student: string;
    project_title: string | null;
    tutor: string | null;
    committee: string[];
};

type Props = {
    processes: CommitteeProcess[];
};

export default function CommitteesIndex({ processes }: Props) {
    return (
        <>
            <Head title="Tribunal evaluador" />

            <div className="space-y-4 p-4">
                <h1 className="text-xl font-semibold">Tribunal evaluador</h1>
                <p className="text-sm text-muted-foreground">
                    Trámites de titulación en curso con perfil de proyecto
                    aprobado. Primero los que no tienen tribunal evaluador.
                </p>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Estudiante</TableHead>
                            <TableHead>Proyecto de grado</TableHead>
                            <TableHead>Tutor</TableHead>
                            <TableHead>Tribunal evaluador</TableHead>
                            <TableHead>
                                <span className="sr-only">Acción</span>
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {processes.length === 0 && (
                            <TableRow>
                                <TableCell
                                    colSpan={5}
                                    className="text-center text-muted-foreground"
                                >
                                    No hay trámites de titulación con perfil de
                                    proyecto aprobado.
                                </TableCell>
                            </TableRow>
                        )}
                        {processes.map((process) => (
                            <TableRow key={process.id}>
                                <TableCell>{process.student}</TableCell>
                                <TableCell className="whitespace-normal">
                                    {process.project_title ?? '—'}
                                </TableCell>
                                <TableCell>{process.tutor ?? '—'}</TableCell>
                                <TableCell className="whitespace-normal">
                                    {process.committee.length === 0 ? (
                                        <Badge variant="outline">
                                            Sin tribunal evaluador
                                        </Badge>
                                    ) : (
                                        process.committee.join(', ')
                                    )}
                                </TableCell>
                                <TableCell className="text-right">
                                    <Button asChild size="sm">
                                        <Link href={edit.url(process.id)}>
                                            {process.committee.length === 0
                                                ? 'Asignar tribunal'
                                                : 'Reemplazar miembro'}
                                        </Link>
                                    </Button>
                                </TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </div>
        </>
    );
}

CommitteesIndex.layout = {
    breadcrumbs: [
        {
            title: 'Tribunal evaluador',
            href: index(),
        },
    ],
};
