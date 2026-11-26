import { Form, Head, usePage } from '@inertiajs/react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { index, store, update, deactivate, reactivate } from '@/routes/accounts';

type Account = {
    id: number;
    name: string;
    email: string;
    is_active: boolean;
    roles: string[];
    blocking_assignments: Assignment[];
};

type Assignment = { student: string | null; role: string };

type Blocked = {
    message: string;
    assignments: Assignment[];
};

const BLOCKED_MESSAGE =
    'No se puede desactivar la cuenta ni quitarle el rol docente. El director de carrera debe reasignar antes:';

type Props = {
    accounts: Account[];
    assignableRoles: string[];
    blocked: Blocked | null;
};

const TEACHER_ROLE = 'Docente';
const DIRECTOR_ROLE = 'Director de carrera';

function AccountFormDialog({
    account,
    open,
    onOpenChange,
    assignableRoles,
}: {
    account: Account | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
    assignableRoles: string[];
}) {
    const [roles, setRoles] = useState<string[]>(account?.roles ?? []);

    const toggleRole = (role: string, checked: boolean) => {
        setRoles((current) => {
            const next = checked
                ? [...new Set([...current, role])]
                : current.filter((item) => item !== role);

            // UC-018 BR-001: el director de carrera tambien es docente.
            return role === DIRECTOR_ROLE && checked && !next.includes(TEACHER_ROLE)
                ? [...next, TEACHER_ROLE]
                : next;
        });
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {account ? 'Cambiar cuenta' : 'Registrar cuenta'}
                    </DialogTitle>
                    <DialogDescription>
                        {account
                            ? 'Cambie el nombre, el correo electrónico o los roles de la cuenta.'
                            : 'Ingrese los datos de la cuenta y la contraseña inicial, que el usuario deberá cambiar en su primer inicio de sesión.'}
                    </DialogDescription>
                </DialogHeader>

                <Form
                    {...(account ? update.form(account.id) : store.form())}
                    resetOnError={['password']}
                    onSuccess={() => onOpenChange(false)}
                >
                    {({ processing, errors }) => (
                        <div className="grid gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor="name">Nombre completo</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    defaultValue={account?.name}
                                    required
                                    maxLength={255}
                                    autoComplete="off"
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">
                                    Correo electrónico
                                </Label>
                                <Input
                                    id="email"
                                    name="email"
                                    type="email"
                                    defaultValue={account?.email}
                                    required
                                    maxLength={255}
                                    autoComplete="off"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <fieldset className="grid gap-2">
                                <legend className="text-sm font-medium">
                                    Roles
                                </legend>
                                {assignableRoles.map((role) => (
                                    <label
                                        key={role}
                                        className="flex items-center gap-2 text-sm"
                                    >
                                        <input
                                            type="checkbox"
                                            name="roles[]"
                                            value={role}
                                            checked={roles.includes(role)}
                                            onChange={(event) =>
                                                toggleRole(
                                                    role,
                                                    event.target.checked,
                                                )
                                            }
                                            className="size-4"
                                        />
                                        {role}
                                    </label>
                                ))}
                                <InputError
                                    message={errors.roles ?? errors['roles.0']}
                                />
                            </fieldset>

                            {!account && (
                                <div className="grid gap-2">
                                    <Label htmlFor="password">
                                        Contraseña inicial
                                    </Label>
                                    <PasswordInput
                                        id="password"
                                        name="password"
                                        autoComplete="new-password"
                                        required
                                        minLength={8}
                                        placeholder="Al menos 8 caracteres"
                                    />
                                    <InputError message={errors.password} />
                                </div>
                            )}

                            <DialogFooter>
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => onOpenChange(false)}
                                >
                                    Cancelar
                                </Button>
                                <Button
                                    type="submit"
                                    disabled={processing || roles.length === 0}
                                    data-test="account-submit-button"
                                >
                                    {processing && <Spinner />}
                                    {account ? 'Guardar cambios' : 'Registrar'}
                                </Button>
                            </DialogFooter>
                        </div>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

function AssignmentList({ assignments }: { assignments: Assignment[] }) {
    return (
        <ul className="list-disc pl-4 text-sm">
            {assignments.map((assignment, position) => (
                <li key={position}>
                    {assignment.student
                        ? `${assignment.student}: ${assignment.role}`
                        : assignment.role}
                </li>
            ))}
        </ul>
    );
}

function AccountStateDialog({
    account,
    onClose,
}: {
    account: Account | null;
    onClose: () => void;
}) {
    const isActive = account?.is_active ?? true;
    const assignments = isActive ? (account?.blocking_assignments ?? []) : [];

    // UC-018 A2 step 1 / A3: a blocked account shows what to reassign instead of the confirmation.
    if (account && assignments.length > 0) {
        return (
            <Dialog open onOpenChange={(open) => !open && onClose()}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Desactivar cuenta</DialogTitle>
                        <DialogDescription>{BLOCKED_MESSAGE}</DialogDescription>
                    </DialogHeader>
                    <AssignmentList assignments={assignments} />
                    <DialogFooter>
                        <Button type="button" onClick={onClose}>
                            Cerrar
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        );
    }

    return (
        <Dialog open={account !== null} onOpenChange={(open) => !open && onClose()}>
            <DialogContent>
                {account && (
                    <>
                        <DialogHeader>
                            <DialogTitle>
                                {isActive
                                    ? 'Desactivar cuenta'
                                    : 'Reactivar cuenta'}
                            </DialogTitle>
                            <DialogDescription>
                                {isActive
                                    ? `La cuenta de ${account.name} ya no podrá iniciar sesión. La cuenta no se borra.`
                                    : `La cuenta de ${account.name} volverá a poder iniciar sesión.`}
                            </DialogDescription>
                        </DialogHeader>

                        <Form
                            {...(isActive
                                ? deactivate.form(account.id)
                                : reactivate.form(account.id))}
                            onSuccess={onClose}
                        >
                            {({ processing, errors }) => (
                                <div className="grid gap-4">
                                    <InputError message={errors.account} />
                                    <DialogFooter>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={onClose}
                                        >
                                            Cancelar
                                        </Button>
                                        <Button
                                            type="submit"
                                            variant={
                                                isActive
                                                    ? 'destructive'
                                                    : 'default'
                                            }
                                            disabled={processing}
                                            data-test="account-state-button"
                                        >
                                            {processing && <Spinner />}
                                            {isActive
                                                ? 'Desactivar'
                                                : 'Reactivar'}
                                        </Button>
                                    </DialogFooter>
                                </div>
                            )}
                        </Form>
                    </>
                )}
            </DialogContent>
        </Dialog>
    );
}

function RoleBadges({ roles }: { roles: string[] }) {
    return (
        <>
            {roles.map((role) => (
                <Badge key={role} variant="secondary">
                    {role}
                </Badge>
            ))}
        </>
    );
}

function StateBadge({ active }: { active: boolean }) {
    return (
        <Badge variant={active ? 'default' : 'outline'}>
            {active ? 'Activa' : 'Inactiva'}
        </Badge>
    );
}

function AccountActions({
    account,
    onChange,
    onChangeState,
    className,
}: {
    account: Account;
    onChange: (account: Account) => void;
    onChangeState: (account: Account) => void;
    className?: string;
}) {
    return (
        <div className={`flex flex-wrap gap-2 ${className ?? ''}`}>
            <Button size="sm" variant="outline" onClick={() => onChange(account)}>
                Cambiar
            </Button>
            <Button
                size="sm"
                variant="outline"
                onClick={() => onChangeState(account)}
            >
                {account.is_active ? 'Desactivar' : 'Reactivar'}
            </Button>
        </div>
    );
}

export default function AccountsIndex({
    accounts,
    assignableRoles,
    blocked,
}: Props) {
    const { errors } = usePage().props;
    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState<Account | null>(null);
    const [changingState, setChangingState] = useState<Account | null>(null);

    const openForm = (account: Account | null) => {
        setEditing(account);
        setFormOpen(true);
    };

    return (
        <>
            <Head title="Cuentas de usuario" />

            <div className="flex flex-col gap-4 p-4">
                <div className="flex flex-wrap items-center justify-between gap-2">
                    <h1 className="text-xl font-semibold">
                        Cuentas de usuario
                    </h1>
                    <Button
                        onClick={() => openForm(null)}
                        data-test="register-account-button"
                    >
                        Registrar cuenta
                    </Button>
                </div>

                {blocked && (
                    <Alert variant="destructive" data-test="blocked-alert">
                        <AlertTitle>{blocked.message}</AlertTitle>
                        <AlertDescription>
                            <AssignmentList assignments={blocked.assignments} />
                        </AlertDescription>
                    </Alert>
                )}

                {errors.account && (
                    <Alert variant="destructive">
                        <AlertTitle>{errors.account}</AlertTitle>
                    </Alert>
                )}

                <ul className="grid gap-3 md:hidden" data-test="accounts-cards">
                    {accounts.length === 0 && (
                        <li className="text-sm">No hay cuentas registradas.</li>
                    )}
                    {accounts.map((account) => (
                        <li
                            key={account.id}
                            className="grid gap-2 rounded-lg border p-3"
                        >
                            <div className="font-medium break-words">
                                {account.name}
                            </div>
                            <div className="text-sm break-all">
                                {account.email}
                            </div>
                            <div className="flex flex-wrap gap-1">
                                <RoleBadges roles={account.roles} />
                                <StateBadge active={account.is_active} />
                            </div>
                            <AccountActions
                                account={account}
                                onChange={openForm}
                                onChangeState={setChangingState}
                            />
                        </li>
                    ))}
                </ul>

                <div className="hidden md:block">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Nombre</TableHead>
                                <TableHead>Correo electrónico</TableHead>
                                <TableHead>Roles</TableHead>
                                <TableHead>Estado</TableHead>
                                <TableHead className="text-right">
                                    Acciones
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {accounts.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={5}>
                                        No hay cuentas registradas.
                                    </TableCell>
                                </TableRow>
                            )}
                            {accounts.map((account) => (
                                <TableRow key={account.id}>
                                    <TableCell className="font-medium break-words whitespace-normal">
                                        {account.name}
                                    </TableCell>
                                    <TableCell className="break-all whitespace-normal">
                                        {account.email}
                                    </TableCell>
                                    <TableCell className="whitespace-normal">
                                        <div className="flex flex-wrap gap-1">
                                            <RoleBadges roles={account.roles} />
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <StateBadge active={account.is_active} />
                                    </TableCell>
                                    <TableCell className="whitespace-normal">
                                        <AccountActions
                                            account={account}
                                            onChange={openForm}
                                            onChangeState={setChangingState}
                                            className="justify-end"
                                        />
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>
            </div>

            {formOpen && (
                <AccountFormDialog
                    key={editing?.id ?? 'new'}
                    account={editing}
                    open={formOpen}
                    onOpenChange={setFormOpen}
                    assignableRoles={assignableRoles}
                />
            )}
            <AccountStateDialog
                account={changingState}
                onClose={() => setChangingState(null)}
            />
        </>
    );
}

AccountsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Cuentas de usuario',
            href: index(),
        },
    ],
};
