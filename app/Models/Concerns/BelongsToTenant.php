<?php

namespace App\Models\Concerns;

use App\Models\Restaurante;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Aísla automáticamente los registros por restaurante (tenant).
 *
 * - Aplica un global scope que filtra por el tenant activo (app('currentTenantId')).
 * - Al crear un registro, asigna el tenant activo si no se especificó.
 *
 * El valor se lee en tiempo de ejecución, por lo que funciona correctamente
 * tanto en peticiones web (un tenant por request) como en seeders/consola
 * donde se cambia de tenant varias veces.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (app()->bound('currentTenantId')) {
                $builder->where($builder->getModel()->getTable().'.restaurante_id', app('currentTenantId'));
            }
        });

        static::creating(function ($model) {
            if (empty($model->restaurante_id) && app()->bound('currentTenantId')) {
                $model->restaurante_id = app('currentTenantId');
            }
        });
    }

    public function restaurante(): BelongsTo
    {
        return $this->belongsTo(Restaurante::class);
    }
}
