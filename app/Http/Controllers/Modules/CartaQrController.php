<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\Restaurante;

class CartaQrController extends Controller
{
    /** Pantalla con la URL pública de la carta y su código QR para imprimir. */
    public function index()
    {
        $restaurante = Restaurante::actual();
        $url = route('carta.publica', $restaurante->slug);

        return view('modules.carta_qr.index', compact('restaurante', 'url'));
    }
}
