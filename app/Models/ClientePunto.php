<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientePunto extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'cliente_puntos';

    protected $fillable = [
        'restaurante_id', 'cliente_id', 'pedido_id', 'tipo', 'puntos', 'valor', 'descripcion',
    ];

    protected $casts = ['valor' => 'decimal:2'];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
