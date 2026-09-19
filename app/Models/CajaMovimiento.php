<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CajaMovimiento extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'caja_movimientos';

    protected $fillable = [
        'restaurante_id', 'caja_id', 'user_id', 'tipo', 'concepto', 'monto', 'metodo_pago',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];

    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
