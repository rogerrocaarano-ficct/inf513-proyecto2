<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Opción de la barra de navegación, visible para los roles que tienen su permiso (FR-029).
 *
 * @property int $id
 * @property string $title
 * @property string|null $route_name
 * @property string|null $icon
 * @property int $sort_order
 * @property int|null $parent_id
 * @property int $permission_id
 */
#[Fillable(['title', 'route_name', 'icon', 'sort_order', 'parent_id', 'permission_id'])]
class MenuItem extends Model
{
    public $timestamps = false;

    /**
     * @return BelongsTo<Permission, $this>
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    /**
     * @return BelongsTo<MenuItem, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * @return HasMany<MenuItem, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order');
    }
}
