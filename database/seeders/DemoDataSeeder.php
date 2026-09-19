<?php

namespace Database\Seeders;

use App\Models\Caja;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\ClientePunto;
use App\Models\Insumo;
use App\Models\InsumoMovimiento;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Producto;
use App\Models\Promocion;
use App\Models\Receta;
use App\Models\Reserva;
use App\Models\Restaurante;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Datos de demostración para poblar el dashboard y todos los módulos.
 *
 * Agrega ~10 registros por módulo (Categorías, Carta/Productos, Mesas,
 * Clientes, Inventario, Reservas, Pedidos, Recetas, Caja, Usuarios,
 * Promociones, Kardex y Puntos de cliente) y reparte los pedidos en
 * distintas fechas y horas (hoy, últimos 7 días y últimos 30 días).
 *
 * Ejecutar:  php artisan db:seed --class=DemoDataSeeder
 *
 * Es RE-EJECUTABLE: antes de generar, borra sus propios datos demo
 * (pedidos con código "DEMO-%", reservas/cajas/promos/kardex marcados
 * como demo) y los vuelve a crear anclados a la fecha de HOY. Así el
 * gráfico "Actividad por hora" y el medidor "Ingreso actual" siempre
 * muestran movimiento del día actual. NUNCA toca datos reales.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $rest = Restaurante::where('slug', 'mi-restaurante-vip')->first()
            ?? Restaurante::first();

        if (! $rest) {
            $this->command->warn('No hay restaurante. Corre primero: php artisan db:seed');
            return;
        }

        app()->instance('currentTenantId', $rest->id);
        app()->instance('currentTenant', $rest);

        try {
            $this->limpiarDemo();        // re-ejecutable: borra demo previa
            $this->categorias();
            $this->productos();
            $this->mesas();
            $this->clientes();
            $this->usuarios($rest);
            $this->insumos();
            $this->promociones();
            $this->reservas();
            $this->pedidos($rest);
            $this->recetas();
            $this->kardex();
            $this->puntos();
            $this->caja($rest);
            $this->command->info('✅ DemoDataSeeder completado para: '.$rest->nombre);
        } finally {
            app()->forgetInstance('currentTenantId');
            app()->forgetInstance('currentTenant');
        }
    }

    /**
     * Borra únicamente datos generados por este seeder, para poder
     * re-ejecutar sin acumular ni duplicar (especialmente los pedidos,
     * que deben quedar anclados al día de hoy).
     */
    private function limpiarDemo(): void
    {
        $ids = Pedido::where('codigo', 'like', 'DEMO-%')->pluck('id');
        if ($ids->isNotEmpty()) {
            PedidoItem::whereIn('pedido_id', $ids)->delete();
            ClientePunto::whereIn('pedido_id', $ids)->delete();
            Pedido::whereIn('id', $ids)->delete();
        }
        Reserva::where('notas', 'demo-seed')->delete();
        InsumoMovimiento::where('referencia', 'demo-seed')->delete();
        ClientePunto::where('descripcion', 'like', '%[demo]%')->delete();
        if (Schema::hasTable('promociones')) {
            Promocion::where('codigo', 'like', 'DEMO%')->delete();
        }
        // Cajas demo de días anteriores (se regeneran)
        Caja::where('notas_apertura', 'like', 'cajademo-%')->each(function ($c) {
            $c->movimientos()->delete();
            $c->delete();
        });
    }

    private function categorias(): void
    {
        $items = [
            ['Pastas', '🍝', '#ea580c'], ['Ensaladas', '🥗', '#16a34a'], ['Pizzas', '🍕', '#dc2626'],
            ['Mariscos', '🦐', '#0ea5e9'], ['Vegetariano', '🥦', '#22c55e'], ['Sándwiches', '🥪', '#f59e0b'],
            ['Desayunos', '🍳', '#fbbf24'], ['Menú Infantil', '🧒', '#a855f7'], ['Tragos', '🍹', '#ec4899'],
            ['Cafés', '☕', '#92400e'],
        ];
        foreach ($items as $i => $c) {
            Categoria::firstOrCreate(['nombre' => $c[0]], [
                'icono' => $c[1], 'color' => $c[2], 'orden' => 10 + $i, 'activo' => true,
            ]);
        }
    }

    private function productos(): void
    {
        $items = [
            ['Pastas', 'Fettuccine Alfredo', 30, 11], ['Pastas', 'Lasaña de Carne', 34, 13],
            ['Ensaladas', 'Ensalada César', 24, 8], ['Pizzas', 'Pizza Margarita', 36, 12],
            ['Pizzas', 'Pizza Americana', 40, 14], ['Mariscos', 'Ceviche Mixto', 42, 16],
            ['Mariscos', 'Chicharrón de Pescado', 38, 14], ['Vegetariano', 'Risotto de Champiñones', 32, 10],
            ['Sándwiches', 'Sándwich de Lomo', 22, 8], ['Desayunos', 'Desayuno Americano', 26, 9],
            ['Tragos', 'Pisco Sour', 20, 6], ['Cafés', 'Capuccino', 10, 2.5],
        ];
        foreach ($items as $p) {
            $cat = Categoria::where('nombre', $p[0])->first();
            if (! $cat) continue;
            Producto::firstOrCreate(['nombre' => $p[1]], [
                'categoria_id' => $cat->id, 'precio' => $p[2], 'costo' => $p[3],
                'disponible' => true, 'vendidos' => rand(10, 90),
            ]);
        }
    }

    private function mesas(): void
    {
        $estados = ['libre', 'ocupada', 'reservada', 'cuenta', 'libre', 'ocupada'];
        $zonas = ['Salón principal', 'Terraza', 'Privados', 'Barra'];
        for ($i = 13; $i <= 22; $i++) {
            Mesa::firstOrCreate(['numero' => (string) $i], [
                'nombre' => 'Mesa '.$i,
                'capacidad' => [2, 4, 6, 8][array_rand([2, 4, 6, 8])],
                'zona' => $zonas[array_rand($zonas)],
                'estado' => $estados[array_rand($estados)],
            ]);
        }
    }

    private function clientes(): void
    {
        $nombres = [
            ['Lucía Fernández', '40112233'], ['Diego Castillo', '41223344'], ['Valeria Ríos', '42334455'],
            ['Andrés Mendoza', '43445566'], ['Camila Vargas', '44556677'], ['Renzo Salazar', '45667788'],
            ['Fiorella Chávez', '46778899'], ['Gonzalo Pérez', '47889900'], ['Inversiones Sur SAC', '20600112233'],
            ['Martín Aguirre', '48990011'],
        ];
        foreach ($nombres as $i => $c) {
            $cliente = Cliente::firstOrCreate(['nombre' => $c[0]], [
                'documento' => $c[1],
                'telefono' => '9'.str_pad((string) rand(0, 99999999), 8, '0', STR_PAD_LEFT),
                'puntos' => rand(0, 600),
            ]);
            // Fechas de registro repartidas (algunos hoy → "Clientes nuevos")
            $fecha = $i < 3 ? now() : now()->subDays(rand(1, 25));
            $cliente->forceFill(['created_at' => $fecha])->saveQuietly();
        }
    }

    private function usuarios(Restaurante $rest): void
    {
        $items = [
            ['Carla Mendoza', 'cajero1@demo.test', 'cajero'],
            ['Pedro Quispe', 'cajero2@demo.test', 'cajero'],
            ['Lucía Torres', 'mesero1@demo.test', 'mesero'],
            ['José Ramírez', 'mesero2@demo.test', 'mesero'],
            ['Ana Flores', 'mesero3@demo.test', 'mesero'],
            ['Miguel Soto', 'cocina1@demo.test', 'cocina'],
            ['Rosa Núñez', 'cocina2@demo.test', 'cocina'],
            ['Jorge Díaz', 'mesero4@demo.test', 'mesero'],
            ['Elena Vega', 'cajero3@demo.test', 'cajero'],
            ['Hugo Paredes', 'admin2@demo.test', 'admin'],
        ];
        foreach ($items as $i => $u) {
            User::firstOrCreate(
                ['email' => $u[1]],
                [
                    'restaurante_id' => $rest->id,
                    'name' => $u[0],
                    'password' => Hash::make('password'),
                    'role' => $u[2],
                    'telefono' => '9'.str_pad((string) rand(0, 99999999), 8, '0', STR_PAD_LEFT),
                    'activo' => $i !== 7, // uno inactivo para variedad
                ]
            );
        }
    }

    private function insumos(): void
    {
        $items = [
            ['Tomate', 'kg', 18, 5, 3.5], ['Cebolla', 'kg', 22, 6, 3.0], ['Queso mozzarella', 'kg', 9, 4, 22],
            ['Harina', 'kg', 35, 10, 3.2], ['Lechuga', 'und', 30, 8, 2.0], ['Camarón', 'kg', 7, 3, 45],
            ['Pescado fresco', 'kg', 12, 5, 26], ['Leche', 'lt', 25, 8, 4.5], ['Huevos', 'und', 120, 30, 0.5],
            ['Café en grano', 'kg', 6, 2, 38],
        ];
        foreach ($items as $x) {
            Insumo::firstOrCreate(['nombre' => $x[0]], [
                'unidad' => $x[1], 'stock' => $x[2], 'stock_minimo' => $x[3], 'costo' => $x[4],
            ]);
        }
    }

    private function promociones(): void
    {
        if (! Schema::hasTable('promociones')) {
            $this->command->warn('Tabla "promociones" no existe aún. Corre: php artisan migrate');
            return;
        }
        $items = [
            ['2x1 en Pizzas',        'porcentaje', 50, 'producto', 'Pizza Margarita', 0,  -5, 10, true],
            ['Happy Hour Tragos',    'porcentaje', 30, 'producto', 'Pisco Sour',      0,  -2, 20, true],
            ['Combo Almuerzo',       'monto',       8, 'total',     null,             40, -10, 15, true],
            ['Descuento Cumpleaños', 'porcentaje', 20, 'total',     null,             0,  -1, 30, true],
            ['Café Gratis +50',      'monto',       5, 'producto', 'Capuccino',       50, -3, 25, true],
            ['Martes de Mariscos',   'porcentaje', 15, 'producto', 'Ceviche Mixto',   0,   0,  7, true],
            ['Delivery sin Costo',   'monto',       7, 'total',     null,             60, -7, 14, true],
            ['Promo Familiar',       'porcentaje', 10, 'total',     null,             80, -15, 40, false],
            ['Desayuno Madrugador',  'monto',       4, 'producto', 'Desayuno Americano', 0, -20, -1, false],
            ['Fin de Semana VIP',    'porcentaje', 25, 'total',     null,            100, -2, 12, true],
        ];
        foreach ($items as $i => $p) {
            $prodId = $p[4] ? optional(Producto::where('nombre', $p[4])->first())->id : null;
            Promocion::firstOrCreate(
                ['codigo' => 'DEMO'.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)],
                [
                    'nombre' => $p[0],
                    'tipo' => $p[1],
                    'valor' => $p[2],
                    'alcance' => $p[3],
                    'producto_id' => $p[3] === 'producto' ? $prodId : null,
                    'min_compra' => $p[5] ?: null,
                    'inicia_at' => today()->addDays($p[6]),
                    'termina_at' => today()->addDays($p[7]),
                    'activo' => $p[8],
                ]
            );
        }
    }

    private function reservas(): void
    {
        $nombres = ['Familia Quispe', 'Empresa Norte', 'Sra. Pacheco', 'Grupo Universitario', 'Sr. Linares',
            'Cumpleaños Ana', 'Reunión Ventas', 'Familia Rojas', 'Pareja Aniversario', 'Delegación Cusco'];
        foreach ($nombres as $i => $n) {
            Reserva::firstOrCreate(
                ['nombre_cliente' => $n, 'notas' => 'demo-seed'],
                [
                    'telefono' => '9'.str_pad((string) rand(0, 99999999), 8, '0', STR_PAD_LEFT),
                    'mesa_id' => Mesa::inRandomOrder()->first()?->id,
                    'fecha' => now()->addDays(rand(-3, 10))->toDateString(),
                    'hora' => sprintf('%02d:%02d:00', rand(12, 21), [0, 30][array_rand([0, 30])]),
                    'personas' => rand(2, 10),
                    'estado' => ['pendiente', 'confirmada', 'cumplida'][array_rand(['pendiente', 'confirmada', 'cumplida'])],
                ]
            );
        }
    }

    private function pedidos(Restaurante $rest): void
    {
        $productos = Producto::all();
        if ($productos->isEmpty()) return;
        $mesas = Mesa::pluck('id')->all();
        $clientes = Cliente::pluck('id')->all();
        $meseros = User::whereIn('role', ['admin', 'mesero', 'cajero'])->pluck('id')->all();
        $igv = (float) $rest->igv;

        $crear = function (Carbon $fecha, string $estado, int $n) use ($productos, $mesas, $clientes, $meseros, $igv) {
            $codigo = 'DEMO-'.$fecha->format('ymdHis').'-'.$n;
            if (Pedido::where('codigo', $codigo)->exists()) return;

            $pedido = Pedido::create([
                'codigo' => $codigo,
                'mesa_id' => $mesas ? $mesas[array_rand($mesas)] : null,
                'cliente_id' => (rand(0, 1) && $clientes) ? $clientes[array_rand($clientes)] : null,
                'user_id' => $meseros ? $meseros[array_rand($meseros)] : null,
                'tipo' => ['mesa', 'mesa', 'llevar', 'delivery'][array_rand(['mesa', 'mesa', 'llevar', 'delivery'])],
                'estado' => $estado,
                'metodo_pago' => $estado === 'pagado' ? ['efectivo', 'tarjeta', 'yape', 'plin'][array_rand(['efectivo', 'tarjeta', 'yape', 'plin'])] : null,
                'created_at' => $fecha, 'updated_at' => $fecha,
                'pagado_at' => $estado === 'pagado' ? $fecha : null,
            ]);
            $subtotal = 0;
            foreach ($productos->random(rand(1, 4)) as $prod) {
                $cant = rand(1, 3);
                $sub = $cant * (float) $prod->precio;
                $subtotal += $sub;
                PedidoItem::create([
                    'pedido_id' => $pedido->id, 'producto_id' => $prod->id, 'nombre_producto' => $prod->nombre,
                    'cantidad' => $cant, 'precio' => $prod->precio, 'subtotal' => $sub,
                ]);
            }
            $imp = round($subtotal * ($igv / 100), 2);
            $pedido->update(['subtotal' => $subtotal, 'impuesto' => $imp, 'total' => $subtotal + $imp]);
        };

        // 10 pedidos HOY a distintas horas (pagados) → curva de "Actividad por hora" + medidor "Ingreso actual"
        $horasHoy = [8, 9, 11, 12, 13, 14, 16, 19, 20, 21];
        foreach ($horasHoy as $k => $h) {
            $crear(today()->setTime($h, rand(0, 59)), 'pagado', $k + 1);
        }

        // 10 pedidos repartidos en los últimos 7 días (pagados) → tendencia semanal
        for ($i = 1; $i <= 10; $i++) {
            $f = today()->subDays(rand(1, 6))->setTime(rand(11, 22), rand(0, 59));
            $crear($f, 'pagado', 100 + $i);
        }

        // 10 pedidos repartidos en los últimos 30 días (pagados) → meta mensual / histórico
        for ($i = 1; $i <= 10; $i++) {
            $f = today()->subDays(rand(7, 29))->setTime(rand(11, 22), rand(0, 59));
            $crear($f, 'pagado', 200 + $i);
        }

        // 6 pedidos ACTIVOS de hoy → tarjeta "Pedidos activos" y tablero KDS
        $activos = ['pendiente', 'pendiente', 'preparando', 'preparando', 'servido', 'servido'];
        foreach ($activos as $k => $e) {
            $crear(today()->setTime(rand(11, 22), rand(0, 59)), $e, 300 + $k);
        }
    }

    private function recetas(): void
    {
        if (! Schema::hasTable('recetas')) {
            $this->command->warn('Tabla "recetas" no existe aún. Corre: php artisan migrate');
            return;
        }
        $mapa = [
            'Pizza Margarita'   => [['Harina', 0.25], ['Queso mozzarella', 0.15], ['Tomate', 0.1]],
            'Lasaña de Carne'   => [['Harina', 0.2], ['Queso mozzarella', 0.1], ['Tomate', 0.1]],
            'Ceviche Mixto'     => [['Pescado fresco', 0.2], ['Cebolla', 0.05], ['Camarón', 0.08]],
            'Fettuccine Alfredo'=> [['Harina', 0.2], ['Leche', 0.1], ['Queso mozzarella', 0.05]],
            'Ensalada César'    => [['Lechuga', 0.5], ['Queso mozzarella', 0.03]],
            'Capuccino'         => [['Café en grano', 0.02], ['Leche', 0.15]],
            'Pizza Americana'   => [['Harina', 0.25], ['Queso mozzarella', 0.15], ['Tomate', 0.1]],
            'Desayuno Americano'=> [['Huevos', 2], ['Leche', 0.1]],
        ];
        foreach ($mapa as $nombreProd => $ingredientes) {
            $prod = Producto::where('nombre', $nombreProd)->first();
            if (! $prod) continue;
            foreach ($ingredientes as $ing) {
                $insumo = Insumo::where('nombre', $ing[0])->first();
                if (! $insumo) continue;
                Receta::firstOrCreate(
                    ['producto_id' => $prod->id, 'insumo_id' => $insumo->id],
                    ['cantidad' => $ing[1]]
                );
            }
            // Fijar costo real del producto según su receta
            $prod->load('recetas.insumo');
            $prod->update(['costo' => $prod->costoReceta()]);
        }
    }

    private function kardex(): void
    {
        if (! Schema::hasTable('insumo_movimientos')) {
            $this->command->warn('Tabla "insumo_movimientos" no existe aún. Corre: php artisan migrate');
            return;
        }
        $admin = User::where('role', 'admin')->first();
        $insumos = Insumo::take(10)->get();
        if ($insumos->isEmpty()) return;

        $i = 0;
        foreach ($insumos as $ins) {
            $tipo = ['entrada', 'salida', 'ajuste'][$i % 3];
            $anterior = (float) $ins->stock;
            $cantidad = $tipo === 'ajuste' ? round(rand(5, 50), 2) : round(rand(1, 15), 2);
            if ($tipo === 'entrada')      $nuevo = $anterior + $cantidad;
            elseif ($tipo === 'salida')   $nuevo = max(0, $anterior - $cantidad);
            else                          $nuevo = $cantidad; // ajuste = stock absoluto

            $fecha = today()->subDays(rand(0, 25))->setTime(rand(8, 19), rand(0, 59));
            InsumoMovimiento::create([
                'insumo_id' => $ins->id,
                'user_id' => $admin?->id,
                'tipo' => $tipo,
                'cantidad' => $cantidad,
                'stock_anterior' => $anterior,
                'stock_nuevo' => $nuevo,
                'motivo' => ['Compra a proveedor', 'Merma de cocina', 'Conteo físico', 'Reposición'][array_rand(['Compra a proveedor', 'Merma de cocina', 'Conteo físico', 'Reposición'])],
                'referencia' => 'demo-seed',
                'created_at' => $fecha, 'updated_at' => $fecha,
            ]);
            $ins->update(['stock' => $nuevo]);
            $i++;
        }
    }

    private function puntos(): void
    {
        if (! Schema::hasTable('cliente_puntos')) {
            return;
        }
        $clientes = Cliente::take(10)->get();
        $pedidosPagados = Pedido::where('estado', 'pagado')->where('codigo', 'like', 'DEMO-%')->pluck('id')->all();
        $i = 0;
        foreach ($clientes as $cli) {
            $tipo = $i % 3 === 0 ? 'canjeado' : 'ganado';
            $puntos = $tipo === 'ganado' ? rand(10, 120) : rand(50, 200);
            $fecha = today()->subDays(rand(0, 28))->setTime(rand(10, 22), rand(0, 59));
            ClientePunto::create([
                'cliente_id' => $cli->id,
                'pedido_id' => $tipo === 'ganado' && $pedidosPagados ? $pedidosPagados[array_rand($pedidosPagados)] : null,
                'tipo' => $tipo,
                'puntos' => $puntos,
                'valor' => $tipo === 'canjeado' ? round($puntos / 10, 2) : 0,
                'descripcion' => $tipo === 'ganado' ? 'Puntos por consumo [demo]' : 'Canje de puntos [demo]',
                'created_at' => $fecha, 'updated_at' => $fecha,
            ]);
            $i++;
        }
    }

    private function caja(Restaurante $rest): void
    {
        if (! Schema::hasTable('cajas')) {
            $this->command->warn('Tabla "cajas" no existe aún. Corre: php artisan migrate');
            return;
        }
        $admin = User::where('role', 'admin')->first();

        // 3 cajas cerradas en días anteriores
        for ($d = 3; $d >= 1; $d--) {
            $apertura = today()->subDays($d)->setTime(8, 0);
            $cierre = today()->subDays($d)->setTime(23, 0);
            $codigo = 'cajademo-'.$apertura->format('Ymd');
            if (Caja::where('notas_apertura', $codigo)->exists()) continue;

            $caja = Caja::create([
                'user_id' => $admin?->id, 'cerrada_por' => $admin?->id, 'estado' => 'cerrada',
                'monto_inicial' => 200, 'notas_apertura' => $codigo,
                'abierta_at' => $apertura, 'cerrada_at' => $cierre,
                'created_at' => $apertura, 'updated_at' => $cierre,
            ]);
            $caja->movimientos()->create(['user_id' => $admin?->id, 'tipo' => 'ingreso', 'concepto' => 'Cambio adicional', 'monto' => 100, 'metodo_pago' => 'efectivo', 'created_at' => $apertura, 'updated_at' => $apertura]);
            $caja->movimientos()->create(['user_id' => $admin?->id, 'tipo' => 'egreso', 'concepto' => 'Compra de insumos', 'monto' => 80, 'metodo_pago' => 'efectivo', 'created_at' => $cierre, 'updated_at' => $cierre]);
            $resumen = $caja->resumen();
            $caja->update([
                'efectivo_esperado' => $resumen['efectivo_esperado'],
                'monto_contado' => $resumen['efectivo_esperado'] + (($d % 2) ? -5 : 10),
                'diferencia' => (($d % 2) ? -5 : 10),
            ]);
        }

        // 1 caja abierta hoy (si no hay ya una abierta)
        if (! Caja::where('estado', 'abierta')->exists()) {
            $caja = Caja::create([
                'user_id' => $admin?->id, 'estado' => 'abierta', 'monto_inicial' => 250,
                'notas_apertura' => 'Apertura del día', 'abierta_at' => today()->setTime(8, 0),
            ]);
            $caja->movimientos()->create(['user_id' => $admin?->id, 'tipo' => 'ingreso', 'concepto' => 'Propinas en efectivo', 'monto' => 45, 'metodo_pago' => 'efectivo']);
            $caja->movimientos()->create(['user_id' => $admin?->id, 'tipo' => 'egreso', 'concepto' => 'Delivery de bebidas', 'monto' => 60, 'metodo_pago' => 'efectivo']);
        }
    }
}
