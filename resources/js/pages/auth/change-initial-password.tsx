import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { update } from '@/routes/initial-password';

type Props = {
    passwordRules: string;
};

export default function ChangeInitialPassword({ passwordRules }: Props) {
    return (
        <>
            <Head title="Cambiar contraseña inicial" />

            <Form
                {...update.form()}
                resetOnSuccess={['password', 'password_confirmation']}
                resetOnError={['password', 'password_confirmation']}
            >
                {({ processing, errors }) => (
                    <div className="grid gap-6">
                        <div className="grid gap-2">
                            <Label htmlFor="password">Nueva contraseña</Label>
                            <PasswordInput
                                id="password"
                                name="password"
                                autoComplete="new-password"
                                className="mt-1 block w-full"
                                autoFocus
                                required
                                minLength={8}
                                placeholder="Al menos 8 caracteres"
                                passwordrules={passwordRules}
                            />
                            <InputError message={errors.password} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="password_confirmation">
                                Confirmación de la contraseña
                            </Label>
                            <PasswordInput
                                id="password_confirmation"
                                name="password_confirmation"
                                autoComplete="new-password"
                                className="mt-1 block w-full"
                                required
                                minLength={8}
                                placeholder="Repita la nueva contraseña"
                                passwordrules={passwordRules}
                            />
                            <InputError message={errors.password_confirmation} />
                        </div>

                        <Button
                            type="submit"
                            className="mt-4 w-full"
                            disabled={processing}
                            data-test="change-initial-password-button"
                        >
                            {processing && <Spinner />}
                            Cambiar contraseña
                        </Button>
                    </div>
                )}
            </Form>
        </>
    );
}

ChangeInitialPassword.layout = {
    title: 'Cambiar contraseña inicial',
    description:
        'Debe cambiar su contraseña inicial antes de continuar. Elija una contraseña de al menos 8 caracteres, distinta de la inicial.',
};
