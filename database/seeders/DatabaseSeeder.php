<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Insumo;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Plan;
use App\Models\Producto;
use App\Models\Reserva;
use App\Models\Restaurante;
use App\Models\Suscripcion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /* ---------------- Planes ---------------- */
        $planes = [
            ['nombre' => 'Emprendedor', 'slug' => 'emprendedor', 'descripcion' => 'Ideal para empezar', 'precio' => 0,
             'max_mesas' => 8, 'max_productos' => 30, 'max_usuarios' => 3, 'orden' => 1,
             'features' => ['Punto de venta', 'Gestión de mesas', 'Soporte por correo']],
            ['nombre' => 'Profesional', 'slug' => 'profesional', 'descripcion' => 'Para restaurantes en crecimiento', 'precio' => 99,
             'max_mesas' => 30, 'max_productos' => 200, 'max_usuarios' => 15, 'orden' => 2, 'destacado' => true,
             'features' => ['Todo lo del plan Emprendedor', 'Reservas e inventario', 'Reportes avanzados', 'Soporte prioritario']],
            ['nombre' => 'Premium', 'slug' => 'premium', 'descripcion' => 'Sin límites para cadenas', 'precio' => 249,
             'max_mesas' => null, 'max_productos' => null, 'max_usuarios' => null, 'orden' => 3,
             'features' => ['Todo lo del plan Profesional', 'Mesas, productos y usuarios ilimitados', 'Soporte 24/7']],
        ];
        foreach ($planes as $p) {
            Plan::updateOrCreate(['slug' => $p['slug']], $p + ['activo' => true]);
        }
        $planPro = Plan::where('slug', 'profesional')->first();
        $planEmp = Plan::where('slug', 'emprendedor')->first();

        /* ---------------- Super Admin ---------------- */
        User::updateOrCreate(['email' => 'super@saas.test'], [
            'restaurante_id' => null,
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'activo' => true,
        ]);

        /* ---------------- Restaurante demo 1 (activo, plan Pro) ---------------- */
        $r1 = Restaurante::updateOrCreate(['slug' => 'mi-restaurante-vip'], [
            'nombre' => 'Mi Restaurante VIP',
            'email' => 'hola@mirestaurante.test',
            'telefono' => '(01) 555-1234',
            'ruc' => '20123456789',
            'direccion' => 'Av. La Gastronomía 123, Lima',
            'moneda' => 'S/', 'igv' => 18, 'meta_mensual' => 80000,
            'plan_id' => $planPro->id,
            'estado' => 'activo',
            'subscription_ends_at' => now()->addMonth(),
            'activo' => true,
        ]);
        Suscripcion::firstOrCreate(
            ['restaurante_id' => $r1->id, 'referencia' => 'SEED-R1'],
            ['plan_id' => $planPro->id, 'nombre_plan' => $planPro->nombre, 'monto' => $planPro->precio,
             'intervalo' => 'mensual', 'estado' => 'pagado', 'metodo_pago' => 'tarjeta',
             'periodo_inicio' => now()->subDays(2)->toDateString(), 'periodo_fin' => now()->addMonth()->toDateString()]
        );
        $this->seedTenant($r1, 'admin@restaurante.test', true);

        /* ---------------- Restaurante demo 2 (trial, plan Emprendedor) ---------------- */
        $r2 = Restaurante::updateOrCreate(['slug' => 'la-buena-mesa'], [
            'nombre' => 'La Buena Mesa',
            'email' => 'contacto@labuenamesa.test',
            'telefono' => '(01) 444-9876',
            'ruc' => '20987654321',
            'direccion' => 'Jr. Sabores 456, Arequipa',
            'moneda' => 'S/', 'igv' => 18, 'meta_mensual' => 40000,
            'plan_id' => $planEmp->id,
            'estado' => 'trial',
            'trial_ends_at' => now()->addDays(10),
            'activo' => true,
        ]);
        $this->seedTenant($r2, 'admin@labuenamesa.test', false);
    }

    /** Crea usuarios y datos de demostración para un restaurante (tenant). */
    private function seedTenant(Restaurante $rest, string $adminEmail, bool $conHistorial): void
    {
        // Activar contexto de tenant: los modelos asignan restaurante_id automáticamente.
        app()->instance('currentTenantId', $rest->id);

        try {
            // Usuarios
            User::updateOrCreate(['email' => $adminEmail], [
                'name' => 'Administrador', 'password' => Hash::make('password'), 'role' => 'admin', 'activo' => true,
            ]);
            $sufijo = $rest->id;
            User::updateOrCreate(['email' => "cajero{$sufijo}@restaurante.test"], [
                'name' => 'Lucía Cajera', 'password' => Hash::make('password'), 'role' => 'cajero', 'activo' => true,
            ]);
            User::updateOrCreate(['email' => "mesero{$sufijo}@restaurante.test"], [
                'name' => 'Carlos Mesero', 'password' => Hash::make('password'), 'role' => 'mesero', 'activo' => true,
            ]);
            User::updateOrCreate(['email' => "cocina{$sufijo}@restaurante.test"], [
                'name' => 'Chef Mario', 'password' => Hash::make('password'), 'role' => 'cocina', 'activo' => true,
            ]);

            // Categorías
            $categorias = [
                ['nombre' => 'Entradas', 'icono' => '🥗', 'color' => '#22c55e'],
                ['nombre' => 'Platos de Fondo', 'icono' => '🍛', 'color' => '#7257f0'],
                ['nombre' => 'Parrillas', 'icono' => '🥩', 'color' => '#ef4444'],
                ['nombre' => 'Sopas', 'icono' => '🍲', 'color' => '#f59e0b'],
                ['nombre' => 'Bebidas', 'icono' => '🥤', 'color' => '#0ea5e9'],
                ['nombre' => 'Postres', 'icono' => '🍰', 'color' => '#ec4899'],
            ];
            foreach ($categorias as $i => $c) {
                Categoria::updateOrCreate(['nombre' => $c['nombre']], $c + ['orden' => $i, 'activo' => true]);
            }

            // Productos
            $productos = [
                ['Entradas', 'Causa Limeña', 18, 6], ['Entradas', 'Tequeños (6 und)', 16, 4.5], ['Entradas', 'Anticuchos', 22, 8],
                ['Platos de Fondo', 'Lomo Saltado', 32, 12], ['Platos de Fondo', 'Ají de Gallina', 26, 9],
                ['Platos de Fondo', 'Arroz con Mariscos', 38, 15], ['Platos de Fondo', 'Tallarín Saltado', 28, 10],
                ['Parrillas', 'Bife de Chorizo', 45, 20], ['Parrillas', 'Parrilla Familiar', 89, 40], ['Parrillas', 'Pollo a la Brasa (1/4)', 18, 7],
                ['Sopas', 'Caldo de Gallina', 20, 6.5], ['Sopas', 'Sopa Criolla', 22, 7],
                ['Bebidas', 'Chicha Morada (jarra)', 14, 3], ['Bebidas', 'Limonada Frozen', 12, 2.5],
                ['Bebidas', 'Inca Kola 500ml', 6, 2], ['Bebidas', 'Cerveza Artesanal', 15, 5],
                ['Postres', 'Suspiro Limeño', 14, 4], ['Postres', 'Tres Leches', 13, 4], ['Postres', 'Picarones', 12, 3.5],
            ];
            foreach ($productos as $p) {
                $cat = Categoria::where('nombre', $p[0])->first();
                Producto::updateOrCreate(['nombre' => $p[1]], [
                    'categoria_id' => $cat->id, 'precio' => $p[2], 'costo' => $p[3],
                    'disponible' => true, 'vendidos' => rand(5, 120),
                ]);
            }

            // Mesas
            $zonas = ['Salón principal', 'Terraza', 'Privados'];
            $estados = ['libre', 'ocupada', 'reservada', 'cuenta'];
            for ($i = 1; $i <= 12; $i++) {
                Mesa::updateOrCreate(['numero' => (string) $i], [
                    'nombre' => 'Mesa '.$i,
                    'capacidad' => [2, 4, 4, 6, 8][array_rand([2, 4, 4, 6, 8])],
                    'zona' => $zonas[intdiv($i - 1, 5)] ?? 'Salón principal',
                    'estado' => $i <= 7 ? $estados[array_rand($estados)] : 'libre',
                ]);
            }

            // Clientes
            foreach ([['Juan Pérez', '45678912', '987111222'], ['María Gómez', '12345678', '987333444'],
                      ['Empresa Andina SAC', '20567891234', '015556677'], ['Pedro Ramírez', '87654321', '987555666'],
                      ['Sofía Torres', '11223344', '987777888']] as $c) {
                Cliente::updateOrCreate(['nombre' => $c[0]], ['documento' => $c[1], 'telefono' => $c[2], 'puntos' => rand(0, 500)]);
            }

            // Insumos
            foreach ([['Carne de res', 'kg', 25, 10, 28], ['Pollo', 'kg', 40, 15, 12], ['Arroz', 'kg', 80, 20, 4.5],
                      ['Papa amarilla', 'kg', 8, 15, 3.8], ['Aceite vegetal', 'lt', 30, 10, 9], ['Gas (balón)', 'und', 2, 3, 55]] as $x) {
                Insumo::updateOrCreate(['nombre' => $x[0]], ['unidad' => $x[1], 'stock' => $x[2], 'stock_minimo' => $x[3], 'costo' => $x[4]]);
            }

            // Reservas
            foreach (range(1, 5) as $i) {
                Reserva::create([
                    'nombre_cliente' => fake()->name(), 'telefono' => fake()->numerify('9########'),
                    'mesa_id' => Mesa::inRandomOrder()->first()?->id,
                    'fecha' => now()->addDays(rand(0, 5))->toDateString(),
                    'hora' => sprintf('%02d:00:00', rand(12, 21)),
                    'personas' => rand(2, 8), 'estado' => collect(['pendiente', 'confirmada'])->random(),
                ]);
            }

            // Pedidos
            if ($conHistorial) {
                $meseros = User::whereIn('role', ['mesero', 'cajero', 'admin'])->pluck('id')->all();
                $productosDb = Producto::all();
                $totalPedidos = 180;
                for ($d = 0; $d < $totalPedidos; $d++) {
                    $fecha = now()->subDays(rand(0, 29))->setTime(rand(11, 22), rand(0, 59));
                    $estado = collect(['pagado', 'pagado', 'pagado', 'pendiente', 'preparando', 'cancelado'])->random();
                    $pedido = Pedido::create([
                        'codigo' => 'PED-'.$fecha->format('ymd').'-'.str_pad((string) ($d + 1), 4, '0', STR_PAD_LEFT),
                        'mesa_id' => Mesa::inRandomOrder()->first()?->id,
                        'cliente_id' => rand(0, 1) ? Cliente::inRandomOrder()->first()?->id : null,
                        'user_id' => $meseros[array_rand($meseros)],
                        'tipo' => collect(['mesa', 'mesa', 'llevar', 'delivery'])->random(),
                        'estado' => $estado,
                        'metodo_pago' => $estado === 'pagado' ? collect(['efectivo', 'tarjeta', 'yape', 'plin'])->random() : null,
                        'created_at' => $fecha, 'updated_at' => $fecha,
                        'pagado_at' => $estado === 'pagado' ? $fecha : null,
                    ]);
                    $subtotal = 0;
                    foreach ($productosDb->random(rand(1, 5)) as $prod) {
                        $cant = rand(1, 3);
                        $sub = $cant * $prod->precio;
                        $subtotal += $sub;
                        PedidoItem::create([
                            'pedido_id' => $pedido->id, 'producto_id' => $prod->id, 'nombre_producto' => $prod->nombre,
                            'cantidad' => $cant, 'precio' => $prod->precio, 'subtotal' => $sub,
                        ]);
                    }
                    $impuesto = round($subtotal * ((float) $rest->igv / 100), 2);
                    $pedido->update(['subtotal' => $subtotal, 'impuesto' => $impuesto, 'total' => $subtotal + $impuesto]);
                }
            }
        } finally {
            // Salir del contexto de tenant.
            app()->forgetInstance('currentTenantId');
        }
    }
}
