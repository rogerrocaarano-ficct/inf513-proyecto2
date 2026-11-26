<?php

namespace App\Http\Middleware;

use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                'permissions' => fn (): array => $user?->permissionKeys() ?? [],
            ],
            'menu' => fn (): array => $user === null ? [] : $this->menuFor($user),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * The menu options the permissions of all the user's roles enable, in their order.
     *
     * @return list<array{title: string, href: string, icon: string|null}>
     */
    private function menuFor(User $user): array
    {
        // UC-018 BR-006: The menu holds the options the permissions of all the user's roles enable.
        return array_values(MenuItem::query()
            ->whereNull('parent_id')
            ->whereNotNull('route_name')
            ->whereHas('permission', fn (Builder $query) => $query->whereIn('key', $user->permissionKeys()))
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (MenuItem $item): bool => Route::has($item->route_name))
            ->map(fn (MenuItem $item): array => [
                'title' => $item->title,
                'href' => route($item->route_name, absolute: false),
                'icon' => $item->icon,
            ])
            ->all());
    }
}
