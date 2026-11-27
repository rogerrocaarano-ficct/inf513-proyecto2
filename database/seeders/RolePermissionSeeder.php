<?php

namespace Database\Seeders;

use App\Enums\PermissionKey;
use App\Models\MenuItem;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Fixed roles, their permissions and the menu options the permissions enable (FR-027, FR-029, ADR-005).
 */
class RolePermissionSeeder extends Seeder
{
    /**
     * @var array<string, array{name: string, description: string}>
     */
    private const array PERMISSIONS = [
        'inicio.ver' => ['name' => 'Ver inicio', 'description' => 'Abrir la página de inicio.'],
        'cuentas.gestionar' => ['name' => 'Gestionar cuentas de usuario', 'description' => 'Registrar, cambiar, desactivar y reactivar cuentas (UC-018).'],
        'tramites.iniciar' => ['name' => 'Iniciar trámite de titulación', 'description' => 'Registrar el trámite de titulación de un estudiante (UC-001).'],
        'tutores.asignar' => ['name' => 'Asignar tutor', 'description' => 'Asignar o cambiar el tutor de un trámite de titulación (UC-002).'],
        'taller.designar' => ['name' => 'Designar docente de taller de grado', 'description' => 'Designar al docente de taller de grado (UC-003).'],
        'tribunales.asignar' => ['name' => 'Asignar tribunal evaluador', 'description' => 'Asignar el tribunal evaluador o reemplazar a un miembro (UC-006).'],
        'versiones.presentar' => ['name' => 'Presentar versión del proyecto', 'description' => 'Subir una nueva versión del proyecto de grado en la fase actual del trámite (UC-004).'],
        'perfiles.evaluar' => ['name' => 'Evaluar perfil de proyecto', 'description' => 'Aprobar o rechazar los perfiles de proyecto de los que es revisor (UC-005).'],
        'versiones.observar' => ['name' => 'Observar versión del proyecto como tutor', 'description' => 'Registrar observaciones sobre las versiones del proyecto de los estudiantes que tutela (UC-007).'],
        'defensas.programar' => ['name' => 'Programar defensas', 'description' => 'Programar o reprogramar pre-defensas y defensas formales (UC-009).'],
        'pre-defensas.registrar' => ['name' => 'Registrar pre-defensa', 'description' => 'Registrar observaciones de la pre-defensa o la inasistencia del estudiante (UC-010).'],
        'defensas.calificar' => ['name' => 'Registrar calificación', 'description' => 'Registrar una calificación o la inasistencia a la defensa formal (UC-011).'],
    ];

    /**
     * @var array<string, array{description: string, is_system: bool, permissions: list<PermissionKey>}>
     */
    private const array ROLES = [
        'Administrador del sistema' => [
            'description' => 'Gestiona las cuentas de usuario y consulta la bitácora.',
            'is_system' => true,
            'permissions' => [PermissionKey::ViewHome, PermissionKey::ManageAccounts],
        ],
        'Asistente administrativo' => [
            'description' => 'Registra estudiantes, recibe la documentación de requisitos y archiva los trámites.',
            'is_system' => false,
            'permissions' => [PermissionKey::ViewHome, PermissionKey::StartGraduationProcess],
        ],
        'Director de carrera' => [
            'description' => 'Asigna tutor y tribunal evaluador, y designa al docente de taller de grado.',
            'is_system' => false,
            'permissions' => [
                PermissionKey::ViewHome, PermissionKey::AssignTutor,
                PermissionKey::DesignateWorkshopTeacher, PermissionKey::AssignCommittee,
                PermissionKey::ScheduleDefense,
            ],
        ],
        'Docente' => [
            'description' => 'Puede ser tutor, revisor o miembro del tribunal.',
            'is_system' => false,
            'permissions' => [
                PermissionKey::ViewHome, PermissionKey::EvaluateProjectProfile,
                PermissionKey::ObserveProjectVersion, PermissionKey::RegisterPreDefense,
                PermissionKey::GradeFormalDefense,
            ],
        ],
        'Estudiante' => [
            'description' => 'Elabora su proyecto de grado y sigue su trámite de titulación.',
            'is_system' => false,
            'permissions' => [PermissionKey::ViewHome, PermissionKey::SubmitProjectVersion],
        ],
    ];

    /**
     * @var list<array{title: string, route_name: string, icon: string, sort_order: int, permission: PermissionKey}>
     */
    private const array MENU = [
        ['title' => 'Inicio', 'route_name' => 'dashboard', 'icon' => 'layout-grid', 'sort_order' => 1, 'permission' => PermissionKey::ViewHome],
        ['title' => 'Cuentas de usuario', 'route_name' => 'accounts.index', 'icon' => 'users', 'sort_order' => 10, 'permission' => PermissionKey::ManageAccounts],
        ['title' => 'Iniciar trámite', 'route_name' => 'graduation-processes.create', 'icon' => 'file-plus', 'sort_order' => 20, 'permission' => PermissionKey::StartGraduationProcess],
        ['title' => 'Asignar tutor', 'route_name' => 'tutor-assignments.index', 'icon' => 'user-check', 'sort_order' => 30, 'permission' => PermissionKey::AssignTutor],
        ['title' => 'Docente de taller de grado', 'route_name' => 'workshop-teacher.edit', 'icon' => 'book-open', 'sort_order' => 40, 'permission' => PermissionKey::DesignateWorkshopTeacher],
        ['title' => 'Tribunal evaluador', 'route_name' => 'committees.index', 'icon' => 'gavel', 'sort_order' => 50, 'permission' => PermissionKey::AssignCommittee],
        ['title' => 'Presentar versión', 'route_name' => 'project-versions.create', 'icon' => 'file-up', 'sort_order' => 60, 'permission' => PermissionKey::SubmitProjectVersion],
        ['title' => 'Perfiles de proyecto', 'route_name' => 'project-profiles.index', 'icon' => 'clipboard-check', 'sort_order' => 70, 'permission' => PermissionKey::EvaluateProjectProfile],
        ['title' => 'Mis estudiantes', 'route_name' => 'tutor-observations.index', 'icon' => 'message-square-text', 'sort_order' => 80, 'permission' => PermissionKey::ObserveProjectVersion],
        ['title' => 'Calendario de defensas', 'route_name' => 'defenses.index', 'icon' => 'calendar-days', 'sort_order' => 90, 'permission' => PermissionKey::ScheduleDefense],
        ['title' => 'Registrar pre-defensa', 'route_name' => 'defense-reviews.index', 'icon' => 'clipboard-list', 'sort_order' => 100, 'permission' => PermissionKey::RegisterPreDefense],
        ['title' => 'Registrar calificación', 'route_name' => 'formal-defense-reviews.index', 'icon' => 'clipboard-check', 'sort_order' => 110, 'permission' => PermissionKey::GradeFormalDefense],
    ];

    /**
     * Seed the fixed roles, permissions and menu options; running it again keeps them in step.
     */
    public function run(): void
    {
        foreach (self::PERMISSIONS as $key => $attributes) {
            Permission::query()->updateOrCreate(['key' => $key], $attributes);
        }

        $permissionIds = Permission::query()->pluck('id', 'key');

        foreach (self::ROLES as $name => $definition) {
            $role = Role::query()->updateOrCreate(['name' => $name], [
                'description' => $definition['description'],
                'is_system' => $definition['is_system'],
            ]);

            $role->permissions()->sync(array_map(
                fn (PermissionKey $key): int => $permissionIds[$key->value],
                $definition['permissions'],
            ));
        }

        foreach (self::MENU as $item) {
            MenuItem::query()->updateOrCreate(['route_name' => $item['route_name']], [
                'title' => $item['title'],
                'icon' => $item['icon'],
                'sort_order' => $item['sort_order'],
                'parent_id' => null,
                'permission_id' => $permissionIds[$item['permission']->value],
            ]);
        }
    }
}
