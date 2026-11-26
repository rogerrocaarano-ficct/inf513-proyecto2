import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { index, show } from '@/routes/project-profiles';

type ProfileRow = {
    id: number;
    student: string;
    title: string;
    number: number;
    submitted_at: string;
};

type Props = {
    profiles: ProfileRow[];
};

function formatDate(value: string): string {
    return new Date(value).toLocaleString('es-BO', {
        dateStyle: 'short',
        timeStyle: 'short',
    });
}

export default function ProjectProfilesIndex({ profiles }: Props) {
    return (
        <>
            <Head title="Perfiles de proyecto" />

            <div className="space-y-4 p-4">
                <h1 className="text-xl font-semibold">Perfiles de proyecto</h1>
                <p className="text-sm text-muted-foreground">
                    Perfiles de proyecto que esperan su revisión, los más
                    antiguos primero.
                </p>

                {profiles.length === 0 ? (
                    <p
                        className="rounded-md border p-4 text-center text-muted-foreground"
                        data-test="no-profiles"
                    >
                        No hay perfiles de proyecto que esperen su revisión.
                    </p>
                ) : (
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Estudiante</TableHead>
                                <TableHead>Título</TableHead>
                                <TableHead>Presentado</TableHead>
                                <TableHead>
                                    <span className="sr-only">Acción</span>
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {profiles.map((profile) => (
                                <TableRow key={profile.id}>
                                    <TableCell>{profile.student}</TableCell>
                                    <TableCell className="whitespace-normal">
                                        {profile.title}
                                        <span className="block text-xs text-muted-foreground">
                                            Versión {profile.number}
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        {formatDate(profile.submitted_at)}
                                    </TableCell>
                                    <TableCell className="text-right">
                                        <Button asChild size="sm">
                                            <Link href={show.url(profile.id)}>
                                                Evaluar
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

ProjectProfilesIndex.layout = {
    breadcrumbs: [
        {
            title: 'Perfiles de proyecto',
            href: index(),
        },
    ],
};
