<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Restaurante extends Model
{
    use HasFactory;

    protected $table = 'restaurantes';

    protected $fillable = [
        'nombre', 'slug', 'email', 'telefono', 'ruc', 'direccion', 'logo',
        'moneda', 'igv', 'meta_mensual', 'plan_id', 'estado',
        'trial_ends_at', 'subscription_ends_at', 'activo',
        'pais', 'ubigeo', 'fe_activo', 'sunat_ambiente',
        'sunat_sol_user', 'sunat_sol_pass', 'sunat_cert_path',
    ];

    protected $casts = [
        'igv' => 'decimal:2',
        'meta_mensual' => 'decimal:2',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'activo' => 'boolean',
        'fe_activo' => 'boolean',
        'sunat_sol_pass' => 'encrypted',
    ];

    /** Tenant activo actual (resuelto por el middleware o el usuario logueado). */
    public static function actual(): ?self
    {
        if (app()->bound('currentTenant')) {
            return app('currentTenant');
        }

        return auth()->user()?->restaurante;
    }

    // Compatibilidad con las vistas que usaban $config->nombre_restaurante
    public function getNombreRestauranteAttribute(): string
    {
        return $this->nombre;
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    public function suscripciones()
    {
        return $this->hasMany(Suscripcion::class);
    }

    /* ----------------- Estado de suscripción ----------------- */

    public function enTrial(): bool
    {
        return $this->estado === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function vigente(): bool
    {
        if (! $this->activo || $this->estado === 'suspendido' || $this->estado === 'cancelado') {
            return false;
        }
        if ($this->estado === 'trial') {
            return $this->trial_ends_at && $this->trial_ends_at->isFuture();
        }
        if ($this->estado === 'activo') {
            return ! $this->subscription_ends_at || $this->subscription_ends_at->isFuture();
        }
        return false;
    }

    public function diasRestantes(): int
    {
        $fin = $this->estado === 'trial' ? $this->trial_ends_at : $this->subscription_ends_at;
        return $fin ? max(0, (int) now()->startOfDay()->diffInDays($fin->copy()->startOfDay(), false)) : 0;
    }

    public function estadoLabel(): string
    {
        return match (true) {
            $this->estado === 'trial' && $this->vigente()  => 'Prueba',
            $this->estado === 'trial'                       => 'Prueba vencida',
            $this->estado === 'activo' && $this->vigente()  => 'Activo',
            $this->estado === 'activo'                      => 'Vencido',
            $this->estado === 'suspendido'                  => 'Suspendido',
            default                                          => 'Cancelado',
        };
    }

    public function estadoColor(): string
    {
        return match (true) {
            $this->vigente() && $this->estado === 'trial'  => 'amber',
            $this->vigente()                                => 'emerald',
            default                                         => 'rose',
        };
    }

    /* ----------------- Límites de plan ----------------- */

    public function limiteAlcanzado(string $campo, int $actual): bool
    {
        $limite = $this->plan?->limite($campo);
        return ! is_null($limite) && $actual >= $limite;
    }
}
