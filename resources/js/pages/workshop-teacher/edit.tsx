import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { edit, update } from '@/routes/workshop-teacher';

type TeacherOption = {
    id: number;
    name: string;
};

type Props = {
    current: TeacherOption | null;
    candidates: TeacherOption[];
    profilesInReview: number;
};

export default function EditWorkshopTeacher({
    current,
    candidates,
    profilesInReview,
}: Props) {
    const [confirming, setConfirming] = useState(false);
    const form = useForm<{ teacher_id: string }>({ teacher_id: '' });
    const chosen = candidates.find(
        (candidate) => String(candidate.id) === form.data.teacher_id,
    );

    function confirm() {
        form.submit(update(), {
            preserveScroll: true,
            onSuccess: () => {
                setConfirming(false);
                form.reset();
            },
            onError: () => setConfirming(false),
        });
    }

    return (
        <>
            <Head title="Docente de taller de grado" />

            <div className="mx-auto flex w-full max-w-2xl flex-col gap-4 p-4">
                <Card>
                    <CardHeader>
                        <CardTitle>Docente de taller de grado actual</CardTitle>
                        <CardDescription>
                            Revisa todos los perfiles de proyecto de la carrera.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {current ? (
                            <p className="font-medium" data-test="current-teacher">
                                {current.name}
                            </p>
                        ) : (
                            <Alert>
                                <AlertTitle>Sin docente designado</AlertTitle>
                                <AlertDescription>
                                    Los perfiles de proyecto presentados esperan
                                    revisor hasta la primera designación.
                                </AlertDescription>
                            </Alert>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>
                            {current ? 'Cambiar docente' : 'Designar docente'}
                        </CardTitle>
                        <CardDescription>
                            {profilesInReview} perfiles de proyecto en revisión
                            pasarán al nuevo docente.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form
                            className="grid gap-4"
                            onSubmit={(event) => {
                                event.preventDefault();
                                if (form.data.teacher_id !== '') {
                                    setConfirming(true);
                                }
                            }}
                        >
                            <div className="grid gap-2">
                                <Label htmlFor="teacher_id">Docente</Label>
                                <Select
                                    value={form.data.teacher_id}
                                    onValueChange={(value) =>
                                        form.setData('teacher_id', value)
                                    }
                                    required
                                >
                                    <SelectTrigger
                                        id="teacher_id"
                                        className="w-full"
                                        data-test="teacher-select"
                                    >
                                        <SelectValue placeholder="Elija un docente" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {candidates.map((candidate) => (
                                            <SelectItem
                                                key={candidate.id}
                                                value={String(candidate.id)}
                                            >
                                                {candidate.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={form.errors.teacher_id} />
                            </div>

                            <Button
                                type="submit"
                                disabled={
                                    form.data.teacher_id === '' ||
                                    form.processing
                                }
                                data-test="designate-button"
                            >
                                Designar
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>

            <Dialog open={confirming} onOpenChange={setConfirming}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Confirmar designación</DialogTitle>
                        <DialogDescription>
                            {chosen?.name} será el docente de taller de grado
                            {current ? ` en lugar de ${current.name}` : ''}, y{' '}
                            {profilesInReview} perfiles de proyecto pasarán a su
                            revisión.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setConfirming(false)}
                            data-test="cancel-button"
                        >
                            Cancelar
                        </Button>
                        <Button
                            onClick={confirm}
                            disabled={form.processing}
                            data-test="confirm-button"
                        >
                            {form.processing && <Spinner />}
                            Confirmar
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}

EditWorkshopTeacher.layout = {
    breadcrumbs: [
        {
            title: 'Docente de taller de grado',
            href: edit(),
        },
    ],
};
