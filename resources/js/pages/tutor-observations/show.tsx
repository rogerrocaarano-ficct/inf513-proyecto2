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
import { index, version } from '@/routes/tutor-observations';

type VersionRow = {
    id: number;
    number: number;
    phase: string;
    status: string;
    title: string;
    submitted_at: string;
    observations_count: number;
    observed_by_me: boolean;
};

type Props = {
    process: {
        id: number;
        student: string;
        phase: string;
        project_title: string | null;
    };
    versions: VersionRow[];
};

function formatDate(value: string): string {
    return new Date(value).toLocaleString('es-BO', {
        dateStyle: 'short',
        timeStyle: 'short',
    });
}

export default function TutorObservationsShow({ process, versions }: Props) {
    return (
        <>
            <Head title="Versiones del proyecto" />

            <div className="space-y-4 p-4">
                <div className="space-y-1">
                    <h1 className="text-xl font-semibold">
                        Versiones del proyecto
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Estudiante: {process.student}
                        <br />
                        Fase: {process.phase}
                        {process.project_title && (
                            <>
                                <br />
                                Proyecto: {process.project_title}
                            </>
                        )}
                    </p>
                </div>

                {versions.length === 0 ? (
                    <p
                        className="rounded-md border p-4 text-center text-muted-foreground"
                        data-test="no-versions"
                    >
                        El estudiante aún no presentó versiones del proyecto.
                    </p>
                ) : (
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Versión</TableHead>
                                <TableHead>Fase</TableHead>
                                <TableHead>Estado</TableHead>
                                <TableHead>Presentada</TableHead>
                                <TableHead>Observaciones</TableHead>
                                <TableHead>
                                    <span className="sr-only">Acción</span>
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {versions.map((item) => (
                                <TableRow key={item.id}>
                                    <TableCell className="whitespace-normal">
                                        Versión {item.number}
                                        <span className="block text-xs text-muted-foreground">
                                            {item.title}
                                        </span>
                                    </TableCell>
                                    <TableCell>{item.phase}</TableCell>
                                    <TableCell>{item.status}</TableCell>
                                    <TableCell>
                                        {formatDate(item.submitted_at)}
                                    </TableCell>
                                    <TableCell>
                                        {item.observations_count}
                                        {!item.observed_by_me && (
                                            <Badge
                                                variant="outline"
                                                className="ml-2"
                                            >
                                                Sin observar
                                            </Badge>
                                        )}
                                    </TableCell>
                                    <TableCell className="text-right">
                                        <Button asChild size="sm">
                                            <Link href={version.url(item.id)}>
                                                Observar
                                            </Link>
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                )}

                <Button asChild variant="outline">
                    <Link href={index.url()}>Volver</Link>
                </Button>
            </div>
        </>
    );
}

TutorObservationsShow.layout = {
    breadcrumbs: [
        {
            title: 'Mis estudiantes',
            href: index(),
        },
    ],
};
