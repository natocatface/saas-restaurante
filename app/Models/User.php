<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, BelongsToTenant;

    protected $fillable = [
        'restaurante_id', 'name', 'email', 'password', 'role', 'telefono', 'avatar', 'activo',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function hasRole(string|array $roles): bool
    {
        return in_array($this->role, (array) $roles, true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function getInicialesAttribute(): string
    {
        $parts = preg_split('/\s+/', trim($this->name));
        $ini = collect($parts)->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('');
        return mb_strtoupper($ini ?: 'U');
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'superadmin' => 'Super Admin',
            'admin'  => 'Administrador',
            'cajero' => 'Cajero',
            'mesero' => 'Mesero',
            'cocina' => 'Cocina',
            default  => ucfirst($this->role),
        };
    }
}
