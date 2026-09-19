# Puesta en marcha — Facturación SUNAT (Perú)

Guía para dejar operativa la emisión real contra SUNAT en el ambiente de pruebas (beta).

## 1. Instalar la librería

```bash
composer require greenter/lite:^5.3
composer dump-autoload
```

## 2. Migrar la base de datos

```bash
php artisan migrate
```

Crea `comprobantes`, `comprobante_logs`, `series_comprobantes` y la columna `pais` en `restaurantes`.

## 3. Certificado digital

- **Beta:** puedes usar el certificado de pruebas de Greenter (`certificate.pem`).
- Colócalo en `storage/app/certs/certificate.pem` (o ajusta `SUNAT_CERT_PATH`).
- **Producción:** usa el certificado digital tributario real de la empresa, en formato `.pem` (clave privada + certificado).

## 4. Variables de entorno (.env)

```env
FACTURACION_PAIS=PE
FACTURACION_ASYNC=false          # true en producción (emite en cola, no bloquea el POS)
FACTURACION_DISK=local

SUNAT_AMBIENTE=beta              # beta | produccion
SUNAT_SOL_USER=MODDATOS          # usuario secundario Clave SOL (beta: MODDATOS)
SUNAT_SOL_PASS=MODDATOS          # clave (beta: MODDATOS)
SUNAT_CERT_PATH=storage/app/certs/certificate.pem
```

En beta, el RUC de pruebas estándar es `20000000001`. Configura el `ruc` del restaurante (tenant) en su ficha.

## 5. Series y correlativos

Crea al menos una serie activa por tipo y tenant en `series_comprobantes`, por ejemplo:

| pais | tipo    | serie | correlativo_actual | activa |
|------|---------|-------|--------------------|--------|
| PE   | factura | F001  | 0                  | 1      |
| PE   | boleta  | B001  | 0                  | 1      |

El `EmisorService` reserva el siguiente correlativo con bloqueo transaccional.

## 6. Emitir

Desde el detalle de un pedido, `POST /facturacion/pedido/{pedido}` con `tipo=factura|boleta`.

- **Factura:** se envía a SUNAT y se lee el CDR de inmediato (estado `aceptado` / `observado` / `rechazado`).
- **Boleta:** se firma y guarda el XML (estado `pendiente`); se comunica a SUNAT por **resumen diario** (ver punto 8).

Los XML firmados y los CDR (`.zip`) quedan en `storage/app/facturacion/PE/{RUC}/`.

## 8. Resumen diario de boletas (implementado)

Las boletas se informan a SUNAT en lote. El comando agrupa las boletas `pendiente` del día por restaurante, arma el Resumen Diario, lo envía y actualiza su estado a `aceptado`.

```bash
php artisan sunat:resumen              # boletas de hoy
php artisan sunat:resumen 2026-07-06   # boletas de una fecha concreta
```

- **Programado:** corre automáticamente cada noche a las 23:55 (definido en `routes/console.php`). Requiere el worker de scheduler activo: `php artisan schedule:work` (o una tarea cron `* * * * * php artisan schedule:run`).
- **Manual:** botón *"Enviar resumen diario"* en la pantalla `/facturacion`, o `POST /facturacion/resumen`.

## 9. Pendientes para producción (siguientes iteraciones)

- **Anulación de boletas** vía resumen con `estado=3` (la anulación de facturas ya está por Comunicación de Baja; el servicio de resumen ya soporta el parámetro `estado`).
- **Representación impresa PDF** (`greenter/report`) y envío por correo al cliente.
- **Certificado por tenant** cifrado, si cada restaurante emite con su propio RUC.
- Homologación en beta antes de habilitar `SUNAT_AMBIENTE=produccion`.
