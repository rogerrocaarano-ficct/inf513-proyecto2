import { Form, Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { index, update } from '@/routes/tutor-assignments';

type Props = {
    process: {
        id: number;
        student: string;
        phase: string;
        tutor: string | null;
        proposedTutor: string | null;
    };
    teachers: { id: number; name: string }[];
};

export default function TutorAssignmentsEdit({ process, teachers }: Props) {
    const [teacherId, setTeacherId] = useState('');
    const isChange = process.tutor !== null;

    return (
        <>
            <Head title={isChange ? 'Cambiar tutor' : 'Asignar tutor'} />

            <div className="flex max-w-xl flex-col gap-4 p-4">
                <h1 className="text-xl font-semibold">
                    {isChange ? 'Cambiar tutor' : 'Asignar tutor'}
                </h1>

                <dl className="grid grid-cols-[auto_1fr] gap-x-4 gap-y-1 text-sm">
                    <dt className="text-muted-foreground">Estudiante</dt>
                    <dd>{process.student}</dd>
                    <dt className="text-muted-foreground">Fase</dt>
                    <dd>{process.phase}</dd>
                    {process.tutor && (
                        <>
                            <dt className="text-muted-foreground">
                                Tutor actual
                            </dt>
                            <dd>{process.tutor}</dd>
                        </>
                    )}
                    {process.proposedTutor && (
                        <>
                            <dt className="text-muted-foreground">
                                Tutor propuesto
                            </dt>
                            <dd>{process.proposedTutor}</dd>
                        </>
                    )}
                </dl>

                <Form {...update.form(process.id)} className="grid gap-4">
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="teacher_id">Docente</Label>
                                <input
                                    type="hidden"
                                    name="teacher_id"
                                    value={teacherId}
                                />
                                <Select
                                    value={teacherId}
                                    onValueChange={setTeacherId}
                                >
                                    <SelectTrigger
                                        id="teacher_id"
                                        className="w-full"
                                    >
                                        <SelectValue placeholder="Elija un docente" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {teachers.map((teacher) => (
                                            <SelectItem
                                                key={teacher.id}
                                                value={String(teacher.id)}
                                            >
                                                {teacher.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {teachers.length === 0 && (
                                    <p className="text-sm text-muted-foreground">
                                        No hay docentes elegibles como tutor de
                                        este trámite de titulación.
                                    </p>
                                )}
                                <InputError message={errors.teacher_id} />
                            </div>

                            <div className="flex flex-wrap gap-2">
                                <Button
                                    type="submit"
                                    disabled={processing || teacherId === ''}
                                    data-test="confirm-tutor-button"
                                >
                                    {processing && <Spinner />}
                                    {isChange
                                        ? 'Confirmar cambio'
                                        : 'Confirmar asignación'}
                                </Button>
                                <Button asChild variant="outline">
                                    <Link href={index()}>Cancelar</Link>
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

TutorAssignmentsEdit.layout = {
    breadcrumbs: [
        {
            title: 'Asignar tutor',
            href: index(),
        },
    ],
};
