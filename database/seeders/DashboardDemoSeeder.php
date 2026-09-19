<?php

namespace Database\Seeders;

use App\Models\Caja;
use App\Models\CajaMovimiento;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Insumo;
use App\Models\InsumoMovimiento;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Producto;
use App\Models\Promocion;
use App\Models\Reserva;
use App\Models\Restaurante;
use App\Models\User;
use App\Facturacion\Models\Comprobante;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Carga datos de demostración para que el dashboard (paneles y gráficos)
 * muestre información realista distribuida en distintas fechas.
 *
 * Agrega ~10 registros a cada módulo y, sobre todo, pedidos PAGADOS hoy
 * en distintas horas para alimentar "Actividad por hora" y
 * "Avance de meta diaria".
 *
 * Es IDEMPOTENTE: puede re-ejecutarse cualquier día; primero limpia sus
 * propios datos demo y luego los regenera con la fecha actual.
 *
 * Ejecutar:  php artisan db:seed --class=Database\Seeders\DashboardDemoSeeder
 */
class DashboardDemoSeeder extends Seeder
{
    public function run(): void
    {
        $rest = Restaurante::where('slug', 'mi-restaurante-vip')->first()
            ?? Restaurante::orderBy('id')->first();

        if (! $rest) {
            $this->command->error('No se encontró ningún restaurante. Ejecuta primero el seeder principal.');
            return;
        }

        app()->instance('currentTenantId', $rest->id);
        $igv = (float) ($rest->igv ?: 18);

        try {
            $userId = User::where('restaurante_id', $rest->id)->orderBy('id')->value('id');
            $this->command->info("Restaurante destino: {$rest->nombre} (id {$rest->id})");

            /* ───────────── 0. LIMPIEZA (idempotente) ─────────────
             * Elimina datos demo de ejecuciones anteriores para que el
             * seeder pueda volver a correrse cualquier día y siempre genere
             * ~10 registros por módulo + ventas PAGADAS de HOY (sin duplicar).
             */
            $this->command->info('Limpiando datos demo anteriores…');
            $catNames = ['Menú Ejecutivo', 'Mariscos', 'Pastas', 'Pizzas', 'Ensaladas', 'Cócteles', 'Carta de Vinos', 'Jugos Naturales', 'Especiales del Chef', 'Para Compartir'];
            $prodNames = ['Ceviche Mixto', 'Ravioles de Ricotta', 'Pizza Cuatro Quesos', 'Ensalada César', 'Pisco Sour', 'Copa de Malbec', 'Jugo de Maracuyá', 'Risotto de Hongos', 'Tabla de Piqueos', 'Menú del Día'];
            $insNames = ['Lomo fino', 'Camarones', 'Harina', 'Queso mozzarella', 'Tomate', 'Lechuga', 'Pisco', 'Vino tinto', 'Maracuyá', 'Hongos'];
            $promoCodes = ['PIZZA2X1', 'HHCOCKTAIL', 'MENU10', 'FAMILIA', 'PASTAS15', 'CUMPLE', 'VIP10', 'ENVIOFREE', 'POSTRE', 'FINDE'];

            $mesaDemoIds = Mesa::where('numero', 'like', 'V2%')->pluck('id');
            $cliDemoIds = Cliente::where('email', 'like', 'cliente%@demo.test')->pluck('id');

            // Comprobantes demo (series ficticias) antes de borrar sus pedidos
            Comprobante::whereIn('serie', ['FDEMO', 'BDEMO'])->delete();
            // Pedidos demo (la cascada elimina sus items)
            Pedido::where('codigo', 'like', 'DEMO-%')->delete();
            // Reservas que referencian mesas/clientes demo
            Reserva::where(function ($q) use ($mesaDemoIds, $cliDemoIds) {
                $q->whereIn('mesa_id', $mesaDemoIds)->orWhereIn('cliente_id', $cliDemoIds);
            })->delete();
            // Kardex / movimientos de insumo demo
            InsumoMovimiento::where('referencia', 'like', 'KDX-%')->delete();
            // Caja y movimientos demo
            CajaMovimiento::where('concepto', 'like', '%(demo)%')->delete();
            Caja::where('notas_apertura', 'like', '%(demo)%')->delete();
            // Catálogo demo (las cascadas limpian recetas/puntos asociados)
            $catDemoIds = Categoria::whereIn('nombre', $catNames)->pluck('id');
            // FK productos.categoria_id es restrictOnDelete: hay que borrar TODO
            // producto que apunte a una categoría demo antes de borrar la categoría.
            Producto::where(function ($q) use ($prodNames, $catDemoIds) {
                $q->whereIn('nombre', $prodNames)->orWhereIn('categoria_id', $catDemoIds);
            })->delete();
            Categoria::whereIn('id', $catDemoIds)->delete();
            Insumo::whereIn('nombre', $insNames)->delete();
            Promocion::whereIn('codigo', $promoCodes)->delete();
            Cliente::whereIn('id', $cliDemoIds)->delete();
            Mesa::whereIn('id', $mesaDemoIds)->delete();
            // Usuarios demo (nunca borra al admin real)
            User::where('email', 'like', 'demo.user%@demo.test')->delete();

            /* ───────────── 1. CATEGORÍAS (10) ───────────── */
            $catData = [
                ['Menú Ejecutivo', '🍱', '#f97316'], ['Mariscos', '🦐', '#0ea5e9'],
                ['Pastas', '🍝', '#eab308'], ['Pizzas', '🍕', '#ef4444'],
                ['Ensaladas', '🥗', '#22c55e'], ['Cócteles', '🍸', '#a855f7'],
                ['Carta de Vinos', '🍷', '#be123c'], ['Jugos Naturales', '🧃', '#10b981'],
                ['Especiales del Chef', '👨‍🍳', '#6366f1'], ['Para Compartir', '🍢', '#f59e0b'],
            ];
            $cats = [];
            foreach ($catData as $i => $c) {
                $f = Carbon::now()->subDays(40 - $i * 3);
                $cats[] = Categoria::create([
                    'nombre' => $c[0], 'icono' => $c[1], 'color' => $c[2],
                    'orden' => 50 + $i, 'activo' => true,
                    'created_at' => $f, 'updated_at' => $f,
                ]);
            }

            /* ───────────── 2. PRODUCTOS / CARTA (10) ───────────── */
            $prodData = [
                ['Ceviche Mixto', 34, 14], ['Ravioles de Ricotta', 29, 11],
                ['Pizza Cuatro Quesos', 38, 15], ['Ensalada César', 22, 8],
                ['Pisco Sour', 18, 6], ['Copa de Malbec', 24, 9],
                ['Jugo de Maracuyá', 12, 3], ['Risotto de Hongos', 33, 13],
                ['Tabla de Piqueos', 49, 20], ['Menú del Día', 25, 10],
            ];
            $prodNuevos = [];
            foreach ($prodData as $i => $p) {
                $f = Carbon::now()->subDays(38 - $i * 3);
                $prodNuevos[] = Producto::create([
                    'categoria_id' => $cats[$i % count($cats)]->id,
                    'nombre' => $p[0], 'precio' => $p[1], 'costo' => $p[2],
                    'disponible' => true, 'controla_stock' => false,
                    'vendidos' => rand(8, 140),
                    'created_at' => $f, 'updated_at' => $f,
                ]);
            }

            /* ───────────── 3. MESAS (10) ───────────── */
            $zonas = ['Salón principal', 'Terraza', 'Privados', 'Barra'];
            $estadosMesa = ['libre', 'libre', 'ocupada', 'reservada', 'cuenta'];
            for ($i = 1; $i <= 10; $i++) {
                $f = Carbon::now()->subDays(35 - $i);
                Mesa::create([
                    'numero' => 'V'.(200 + $i),
                    'nombre' => 'Mesa VIP '.$i,
                    'capacidad' => [2, 4, 4, 6, 8][array_rand([2, 4, 4, 6, 8])],
                    'zona' => $zonas[array_rand($zonas)],
                    'estado' => $estadosMesa[array_rand($estadosMesa)],
                    'created_at' => $f, 'updated_at' => $f,
                ]);
            }

            /* ───────────── 4. CLIENTES (10) ───────────── */
            $cliNombres = [
                'Rosa Quispe', 'Daniel Flores', 'Inversiones Sur SAC', 'Lucía Mendoza',
                'Andrés Castillo', 'Gabriela Ríos', 'Comercial Lima EIRL', 'Miguel Salazar',
                'Patricia Vega', 'Jorge Huamán',
            ];
            $clientesNuevos = [];
            foreach ($cliNombres as $i => $n) {
                // Los primeros 3 se registran HOY (alimenta "Clientes nuevos")
                $f = $i < 3 ? Carbon::now()->subHours(rand(1, 8)) : Carbon::now()->subDays(rand(2, 30));
                $clientesNuevos[] = Cliente::create([
                    'nombre' => $n,
                    'documento' => (string) rand(10000000, 99999999),
                    'telefono' => '9'.rand(10000000, 99999999),
                    'email' => 'cliente'.($i + 1).'@demo.test',
                    'puntos' => rand(0, 600),
                    'created_at' => $f, 'updated_at' => $f,
                ]);
            }

            /* ───────────── 4b. USUARIOS / EQUIPO (10) ───────────── */
            $userData = [
                ['Carlos Ramírez', 'cajero'], ['María Torres', 'mesero'],
                ['José Aguilar', 'cocina'], ['Elena Paredes', 'mesero'],
                ['Raúl Espinoza', 'cajero'], ['Sofía Chávez', 'admin'],
                ['Pedro Ñahui', 'cocina'], ['Valeria Rojas', 'mesero'],
                ['Diego Cárdenas', 'mesero'], ['Ana Beltrán', 'cajero'],
            ];
            $usuariosDemo = [];
            foreach ($userData as $i => $u) {
                $f = Carbon::now()->subDays(rand(3, 45));
                $usuariosDemo[] = User::create([
                    'restaurante_id' => $rest->id,
                    'name' => $u[0],
                    'email' => 'demo.user'.($i + 1).'@demo.test',
                    'password' => 'demo1234', // el cast 'hashed' del modelo lo encripta
                    'role' => $u[1],
                    'telefono' => '9'.rand(10000000, 99999999),
                    'activo' => $i < 8,
                    'email_verified_at' => $f,
                    'created_at' => $f, 'updated_at' => $f,
                ]);
            }
            // Pool de responsables para pedidos/movimientos (admin real + equipo demo)
            $userPool = collect($usuariosDemo)->pluck('id')->push($userId)->filter()->values();

            /* ───────────── 5. RESERVAS (10) ───────────── */
            $mesasAll = Mesa::inRandomOrder()->take(20)->get();
            for ($i = 1; $i <= 10; $i++) {
                $f = Carbon::now()->subDays(rand(0, 6));
                Reserva::create([
                    'cliente_id' => $clientesNuevos[array_rand($clientesNuevos)]->id,
                    'mesa_id' => $mesasAll->random()->id,
                    'nombre_cliente' => $cliNombres[array_rand($cliNombres)],
                    'telefono' => '9'.rand(10000000, 99999999),
                    'fecha' => Carbon::now()->addDays(rand(0, 8))->toDateString(),
                    'hora' => sprintf('%02d:00:00', rand(12, 21)),
                    'personas' => rand(2, 10),
                    'estado' => collect(['pendiente', 'confirmada', 'cumplida'])->random(),
                    'created_at' => $f, 'updated_at' => $f,
                ]);
            }

            /* ───────────── 6. INSUMOS / INVENTARIO (10) ───────────── */
            $insData = [
                ['Lomo fino', 'kg', 30, 10, 32], ['Camarones', 'kg', 18, 8, 45],
                ['Harina', 'kg', 60, 20, 3.2], ['Queso mozzarella', 'kg', 25, 12, 22],
                ['Tomate', 'kg', 5, 12, 4.5], ['Lechuga', 'und', 8, 15, 2.5],
                ['Pisco', 'lt', 14, 6, 38], ['Vino tinto', 'botella', 22, 10, 28],
                ['Maracuyá', 'kg', 9, 10, 7], ['Hongos', 'kg', 6, 8, 18],
            ];
            $insumos = [];
            foreach ($insData as $i => $x) {
                $f = Carbon::now()->subDays(36 - $i * 2);
                $insumos[] = Insumo::create([
                    'nombre' => $x[0], 'unidad' => $x[1], 'stock' => $x[2],
                    'stock_minimo' => $x[3], 'costo' => $x[4],
                    'proveedor' => 'Proveedor '.chr(65 + ($i % 5)),
                    'created_at' => $f, 'updated_at' => $f,
                ]);
            }

            /* ───────────── 7. KARDEX / MOVIMIENTOS DE INSUMO (10) ───────────── */
            foreach (range(1, 10) as $i) {
                $ins = $insumos[array_rand($insumos)];
                $tipo = collect(['entrada', 'salida', 'ajuste'])->random();
                $cant = round(rand(10, 200) / 10, 2);
                $antes = (float) $ins->stock;
                $despues = $tipo === 'entrada' ? $antes + $cant
                    : ($tipo === 'salida' ? max(0, $antes - $cant) : $cant);
                $f = Carbon::now()->subDays(rand(0, 25))->setTime(rand(8, 19), rand(0, 59));
                InsumoMovimiento::create([
                    'insumo_id' => $ins->id, 'user_id' => $userId, 'tipo' => $tipo,
                    'cantidad' => $cant, 'stock_anterior' => $antes, 'stock_nuevo' => $despues,
                    'motivo' => ucfirst($tipo).' de inventario (demo)',
                    'referencia' => 'KDX-'.$f->format('ymd').'-'.$i,
                    'created_at' => $f, 'updated_at' => $f,
                ]);
                $ins->update(['stock' => $despues]);
            }

            /* ───────────── 8. PROMOCIONES (10) ───────────── */
            $promoData = [
                ['2x1 en Pizzas', 'PIZZA2X1', 'porcentaje', 50], ['Happy Hour Cócteles', 'HHCOCKTAIL', 'porcentaje', 30],
                ['Descuento Menú Día', 'MENU10', 'monto', 5], ['Combo Familiar', 'FAMILIA', 'porcentaje', 20],
                ['Martes de Pastas', 'PASTAS15', 'porcentaje', 15], ['Cumpleañero', 'CUMPLE', 'porcentaje', 25],
                ['Cliente Frecuente', 'VIP10', 'monto', 10], ['Delivery Gratis', 'ENVIOFREE', 'monto', 8],
                ['Postre de Regalo', 'POSTRE', 'porcentaje', 100], ['Fin de Semana', 'FINDE', 'porcentaje', 12],
            ];
            foreach ($promoData as $i => $p) {
                $inicia = Carbon::now()->subDays(rand(5, 30));
                Promocion::create([
                    'nombre' => $p[0], 'codigo' => $p[1], 'tipo' => $p[2], 'valor' => $p[3],
                    'alcance' => 'total', 'min_compra' => collect([null, 30, 50, 80])->random(),
                    'inicia_at' => $inicia->toDateString(),
                    'termina_at' => $inicia->copy()->addDays(rand(20, 60))->toDateString(),
                    'activo' => $i < 7,
                    'created_at' => $inicia, 'updated_at' => $inicia,
                ]);
            }

            /* ───────────── 9. CAJA + MOVIMIENTOS (10) ───────────── */
            $caja = Caja::create([
                'user_id' => $userId, 'estado' => 'abierta', 'monto_inicial' => 200,
                'notas_apertura' => 'Apertura de caja (demo)',
                'abierta_at' => Carbon::now()->startOfDay()->addHours(8),
                'created_at' => Carbon::now()->startOfDay()->addHours(8),
                'updated_at' => Carbon::now(),
            ]);
            $movConceptos = [
                ['ingreso', 'Venta en efectivo'], ['ingreso', 'Cobro de pedido'], ['egreso', 'Compra de insumos'],
                ['egreso', 'Pago a proveedor'], ['ingreso', 'Venta delivery'], ['egreso', 'Gastos varios'],
                ['ingreso', 'Propina'], ['egreso', 'Movilidad'], ['ingreso', 'Venta para llevar'], ['egreso', 'Servicios'],
            ];
            foreach ($movConceptos as $i => $mv) {
                $f = Carbon::now()->subDays(rand(0, 6))->setTime(rand(8, 21), rand(0, 59));
                CajaMovimiento::create([
                    'caja_id' => $caja->id, 'user_id' => $userId, 'tipo' => $mv[0],
                    'concepto' => $mv[1].' (demo)',
                    'monto' => $mv[0] === 'ingreso' ? rand(50, 400) : rand(20, 200),
                    'metodo_pago' => collect(['efectivo', 'tarjeta', 'yape', 'plin'])->random(),
                    'created_at' => $f, 'updated_at' => $f,
                ]);
            }

            /* ───────────── 10. PEDIDOS (clave para los gráficos) ───────────── */
            $productosAll = Producto::inRandomOrder()->take(40)->get();
            $clientesAll = Cliente::inRandomOrder()->take(40)->get();
            $mesasPool = Mesa::inRandomOrder()->take(40)->get();
            $contador = 0;

            $crearPedido = function (Carbon $fecha, string $estado) use (
                $igv, $userPool, $productosAll, $clientesAll, $mesasPool, &$contador
            ) {
                $contador++;
                $pedido = Pedido::create([
                    'codigo' => 'DEMO-'.$fecha->format('ymd').'-'.str_pad((string) $contador, 4, '0', STR_PAD_LEFT),
                    'mesa_id' => $mesasPool->random()->id,
                    'cliente_id' => rand(0, 1) ? $clientesAll->random()->id : null,
                    'user_id' => $userPool->random(),
                    'tipo' => collect(['mesa', 'mesa', 'llevar', 'delivery'])->random(),
                    'estado' => $estado,
                    'metodo_pago' => $estado === 'pagado' ? collect(['efectivo', 'tarjeta', 'yape', 'plin'])->random() : null,
                    'pagado_at' => $estado === 'pagado' ? $fecha : null,
                    'created_at' => $fecha, 'updated_at' => $fecha,
                ]);
                $subtotal = 0;
                foreach ($productosAll->random(rand(1, 4)) as $prod) {
                    $cant = rand(1, 3);
                    $sub = $cant * (float) $prod->precio;
                    $subtotal += $sub;
                    PedidoItem::create([
                        'pedido_id' => $pedido->id, 'producto_id' => $prod->id,
                        'nombre_producto' => $prod->nombre, 'cantidad' => $cant,
                        'precio' => $prod->precio, 'subtotal' => $sub,
                    ]);
                }
                $imp = round($subtotal * $igv / 100, 2);
                $pedido->update(['subtotal' => $subtotal, 'impuesto' => $imp, 'total' => $subtotal + $imp]);
                return $pedido;
            };

            // 10a. HOY: pagados en distintas horas → "Actividad por hora" + "Avance de meta diaria"
            foreach ([8, 10, 11, 12, 13, 14, 15, 17, 18, 19, 20, 21] as $h) {
                $crearPedido(Carbon::today()->setTime($h, rand(0, 59)), 'pagado');
            }
            // 10b. HOY: algunos pedidos activos (alimenta "Pedidos activos")
            foreach (['pendiente', 'preparando', 'servido', 'pendiente', 'preparando'] as $e) {
                $crearPedido(Carbon::now()->subMinutes(rand(5, 180)), $e);
            }
            // 10c. Últimos 6 días: pagados → "Ventas últimos 7 días" + meta mensual
            for ($d = 1; $d <= 6; $d++) {
                foreach (range(1, rand(3, 6)) as $n) {
                    $fecha = Carbon::today()->subDays($d)->setTime(rand(11, 22), rand(0, 59));
                    $crearPedido($fecha, 'pagado');
                }
            }
            // 10d. Resto del mes actual (dispersos) para la meta mensual
            for ($d = 7; $d <= 28; $d += 2) {
                $fecha = Carbon::today()->subDays($d)->setTime(rand(11, 22), rand(0, 59));
                $crearPedido($fecha, 'pagado');
            }
            // 10e. Historial largo: ~5 meses hacia atrás para Reportes/tendencias
            //      (varias ventas por día en fechas dispersas de meses anteriores)
            for ($d = 30; $d <= 150; $d += rand(2, 4)) {
                foreach (range(1, rand(2, 5)) as $n) {
                    $fecha = Carbon::today()->subDays($d)->setTime(rand(11, 22), rand(0, 59));
                    $crearPedido($fecha, 'pagado');
                }
            }

            /* ───────────── 11. COMPROBANTES / FACTURACIÓN (10) ───────────── */
            $pagadosDemo = Pedido::where('codigo', 'like', 'DEMO-%')
                ->where('estado', 'pagado')
                ->whereNotNull('total')
                ->inRandomOrder()->take(10)->get();
            $estadosComp = ['aceptado', 'aceptado', 'aceptado', 'aceptado', 'pendiente',
                            'observado', 'rechazado', 'anulado', 'enviando', 'aceptado'];
            $corr = ['FDEMO' => 0, 'BDEMO' => 0];
            $compCreados = 0;
            foreach ($pagadosDemo as $i => $ped) {
                $tipo  = $i % 3 === 0 ? 'factura' : 'boleta';
                $serie = $tipo === 'factura' ? 'FDEMO' : 'BDEMO';
                $corr[$serie]++;
                $estado  = $estadosComp[$i] ?? 'aceptado';
                $enviado = in_array($estado, ['aceptado', 'observado', 'rechazado', 'anulado'], true);
                $f = $ped->pagado_at ? Carbon::parse($ped->pagado_at) : Carbon::now()->subDays($i);
                Comprobante::create([
                    'pedido_id'            => $ped->id,
                    'pais'                 => 'PE',
                    'tipo'                 => $tipo,
                    'serie'                => $serie,
                    'correlativo'          => $corr[$serie],
                    'moneda'               => 'PEN',
                    'subtotal'             => $ped->subtotal,
                    'impuesto'             => $ped->impuesto,
                    'total'                => $ped->total,
                    'estado'               => $estado,
                    'identificador_fiscal' => $enviado ? 'DEMO-'.strtoupper(substr(md5($ped->id.$serie), 0, 12)) : null,
                    'hash'                 => $enviado ? substr(md5($ped->codigo), 0, 28) : null,
                    'codigo_respuesta'     => $estado === 'aceptado' ? '0' : ($estado === 'rechazado' ? '2335' : null),
                    'mensaje'              => $estado === 'aceptado' ? 'El comprobante ha sido aceptado (demo)'
                                              : ($estado === 'rechazado' ? 'El comprobante fue rechazado (demo)'
                                              : ($estado === 'observado' ? 'Aceptado con observaciones (demo)' : null)),
                    'intentos'             => $enviado ? rand(1, 2) : 0,
                    'enviado_at'           => $enviado ? $f->copy()->addMinutes(rand(1, 30)) : null,
                    'created_at'           => $f, 'updated_at' => $f,
                ]);
                $compCreados++;
            }
            $this->command->info("Comprobantes demo creados: {$compCreados}");

            $this->command->info("Pedidos demo creados: {$contador}");
            $ventasHoy = Pedido::where('estado', 'pagado')->whereDate('pagado_at', Carbon::today())->sum('total');
            $this->command->info('Ventas de HOY (pagadas): S/ '.number_format((float) $ventasHoy, 2));
            $this->command->info('✔ Datos de demostración cargados correctamente.');
        } finally {
            app()->forgetInstance('currentTenantId');
        }
    }
}
