import { Form, Head, Link } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { index, replace, store } from '@/routes/committees';

type Person = {
    id: number;
    name: string;
};

type Props = {
    process: {
        id: number;
        student: string;
        project_title: string | null;
        tutor: string | null;
    };
    members: Person[];
    eligibleTeachers: Person[];
};

export default function CommitteesEdit({
    process,
    members,
    eligibleTeachers,
}: Props) {
    const isReplacement = members.length > 0;

    return (
        <>
            <Head
                title={
                    isReplacement
                        ? 'Reemplazar miembro del tribunal'
                        : 'Asignar tribunal evaluador'
                }
            />

            <div className="max-w-2xl space-y-6 p-4">
                <div className="space-y-1">
                    <h1 className="text-xl font-semibold">
                        {isReplacement
                            ? 'Reemplazar miembro del tribunal evaluador'
                            : 'Asignar tribunal evaluador'}
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Estudiante: {process.student}
                        <br />
                        Proyecto de grado: {process.project_title ?? '—'}
                        <br />
                        Tutor: {process.tutor ?? '—'}
                    </p>
                </div>

                {isReplacement ? (
                    <Form {...replace.form(process.id)}>
                        {({ processing, errors }) => (
                            <div className="grid gap-6">
                                <InputError message={errors.graduation_process} />

                                <fieldset className="grid gap-2">
                                    <legend className="text-sm font-medium">
                                        Miembro que se reemplaza
                                    </legend>
                                    {members.map((member) => (
                                        <Label
                                            key={member.id}
                                            className="font-normal"
                                        >
                                            <input
                                                type="radio"
                                                name="committee_member_id"
                                                value={member.id}
                                                required
                                            />
                                            {member.name}
                                        </Label>
                                    ))}
                                    <InputError
                                        message={errors.committee_member_id}
                                    />
                                </fieldset>

                                <fieldset className="grid gap-2">
                                    <legend className="text-sm font-medium">
                                        Docente que entra en su lugar
                                    </legend>
                                    {eligibleTeachers.map((teacher) => (
                                        <Label
                                            key={teacher.id}
                                            className="font-normal"
                                        >
                                            <input
                                                type="radio"
                                                name="teacher_id"
                                                value={teacher.id}
                                                required
                                            />
                                            {teacher.name}
                                        </Label>
                                    ))}
                                    <InputError message={errors.teacher_id} />
                                </fieldset>

                                <Actions
                                    processing={processing}
                                    label="Confirmar reemplazo"
                                />
                            </div>
                        )}
                    </Form>
                ) : (
                    <Form {...store.form(process.id)}>
                        {({ processing, errors }) => (
                            <div className="grid gap-6">
                                <InputError message={errors.graduation_process} />

                                <fieldset className="grid gap-2">
                                    <legend className="text-sm font-medium">
                                        Docentes elegibles: elija tres
                                    </legend>
                                    {eligibleTeachers.map((teacher) => (
                                        <Label
                                            key={teacher.id}
                                            className="font-normal"
                                        >
                                            <input
                                                type="checkbox"
                                                name="teacher_ids[]"
                                                value={teacher.id}
                                            />
                                            {teacher.name}
                                        </Label>
                                    ))}
                                    <InputError message={errors.teacher_ids} />
                                </fieldset>

                                <Actions
                                    processing={processing}
                                    label="Confirmar asignación"
                                />
                            </div>
                        )}
                    </Form>
                )}
            </div>
        </>
    );
}

function Actions({
    processing,
    label,
}: {
    processing: boolean;
    label: string;
}) {
    return (
        <div className="flex flex-wrap gap-2">
            <Button
                type="submit"
                disabled={processing}
                data-test="confirm-committee-button"
            >
                {processing && <Spinner />}
                {label}
            </Button>
            <Button asChild variant="outline">
                <Link href={index.url()}>Cancelar</Link>
            </Button>
        </div>
    );
}

CommitteesEdit.layout = {
    breadcrumbs: [
        {
            title: 'Tribunal evaluador',
            href: index(),
        },
    ],
};
