<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cliente;
use App\Models\Mesa;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Reserva;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Insumo;
use App\Models\Caja;
use App\Models\CajaMovimiento;
use App\Models\Restaurante;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PopulateDummyData extends Command
{
    protected $signature = 'app:populate-dummy';
    protected $description = 'Populates 10 dummy records for each main module on different dates';

    public function handle()
    {
        $faker = \Faker\Factory::create('es_PE');
        $restaurante = Restaurante::first();
        if (!$restaurante) {
            $this->error("Debe existir al menos un restaurante.");
            return;
        }

        $restaurante_id = $restaurante->id;

        $this->info("Poblando Clientes...");
        for ($i = 0; $i < 10; $i++) {
            Cliente::create([
                'restaurante_id' => $restaurante_id,
                'nombre' => $faker->name,
                'documento' => $faker->unique()->randomNumber(8),
                'telefono' => $faker->phoneNumber,
                'email' => $faker->unique()->safeEmail,
                'direccion' => $faker->address,
                'created_at' => $faker->dateTimeBetween('-1 month', 'now')
            ]);
        }

        $this->info("Poblando Categorías...");
        for ($i = 0; $i < 10; $i++) {
            Categoria::create([
                'restaurante_id' => $restaurante_id,
                'nombre' => 'Categoría ' . $faker->word . ' ' . $i,
                'descripcion' => $faker->sentence,
                'estado' => 'activo'
            ]);
        }

        $this->info("Poblando Productos...");
        $categorias = Categoria::all();
        for ($i = 0; $i < 10; $i++) {
            Producto::create([
                'restaurante_id' => $restaurante_id,
                'categoria_id' => $categorias->random()->id,
                'nombre' => 'Producto ' . $faker->word . ' ' . $i,
                'precio' => $faker->randomFloat(2, 10, 100),
                'costo' => $faker->randomFloat(2, 2, 8),
                'descripcion' => $faker->sentence,
                'estado' => 'activo',
                'tipo' => 'preparado'
            ]);
        }

        $this->info("Poblando Insumos...");
        for ($i = 0; $i < 10; $i++) {
            Insumo::create([
                'restaurante_id' => $restaurante_id,
                'nombre' => 'Insumo ' . $faker->word . ' ' . $i,
                'unidad_medida' => 'kg',
                'stock_actual' => $faker->randomFloat(2, 10, 100),
                'stock_minimo' => 5,
                'costo_unitario' => $faker->randomFloat(2, 1, 20)
            ]);
        }

        $this->info("Poblando Reservas...");
        for ($i = 0; $i < 10; $i++) {
            Reserva::create([
                'restaurante_id' => $restaurante_id,
                'nombre_cliente' => $faker->name,
                'telefono' => $faker->phoneNumber,
                'fecha_hora' => $faker->dateTimeBetween('now', '+1 month'),
                'personas' => $faker->numberBetween(2, 10),
                'estado' => 'pendiente',
                'notas' => $faker->sentence
            ]);
        }

        $this->info("Poblando Caja...");
        $caja = Caja::firstOrCreate(
            ['restaurante_id' => $restaurante_id, 'estado' => 'abierta'],
            ['fecha_apertura' => now(), 'monto_inicial' => 100]
        );
        for ($i = 0; $i < 10; $i++) {
            CajaMovimiento::create([
                'caja_id' => $caja->id,
                'tipo' => $faker->randomElement(['ingreso', 'egreso']),
                'monto' => $faker->randomFloat(2, 10, 50),
                'concepto' => 'Movimiento ' . $faker->word,
                'created_at' => $faker->dateTimeBetween('-1 month', 'now')
            ]);
        }

        $this->info("Poblando Pedidos...");
        $mesas = Mesa::all();
        if ($mesas->isEmpty()) {
            for ($i = 0; $i < 10; $i++) {
                Mesa::create([
                    'restaurante_id' => $restaurante_id,
                    'numero' => 'T' . ($i + 1),
                    'nombre' => 'Mesa ' . ($i + 1),
                    'capacidad' => 4,
                    'estado' => 'libre'
                ]);
            }
            $mesas = Mesa::all();
        }
        $productos = Producto::all();
        
        for ($i = 0; $i < 20; $i++) {
            // Asegurarse que algunos pedidos sean de HOY para que el chart de Actividad funcione
            $isToday = $i < 5; 
            $fecha = $isToday ? Carbon::today()->addHours($faker->numberBetween(8, 22)) : Carbon::instance($faker->dateTimeBetween('-1 month', 'now'));

            $pedido = Pedido::create([
                'restaurante_id' => $restaurante_id,
                'codigo' => 'P-' . strtoupper(Str::random(6)),
                'mesa_id' => $mesas->random()->id,
                'tipo' => 'mesa',
                'estado' => 'pagado',
                'subtotal' => 0,
                'total' => 0,
                'pagado_at' => $fecha,
                'created_at' => $fecha,
                'updated_at' => $fecha,
            ]);

            $total = 0;
            for ($j = 0; $j < $faker->numberBetween(1, 3); $j++) {
                $prod = $productos->random();
                $qty = $faker->numberBetween(1, 3);
                $sub = $prod->precio * $qty;
                PedidoItem::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $prod->id,
                    'nombre_producto' => $prod->nombre,
                    'cantidad' => $qty,
                    'precio_unitario' => $prod->precio,
                    'subtotal' => $sub
                ]);
                $total += $sub;
            }

            $pedido->update([
                'subtotal' => $total,
                'total' => $total,
            ]);
        }

        $this->info("Datos poblados correctamente!");
    }
}
