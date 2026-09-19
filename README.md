# 🍽️ Mi Restaurante VIP — SaaS multi-tenant para restaurantes

Plataforma **SaaS multi-inquilino (multi-tenant)** para la gestión integral de restaurantes, construida con **Laravel 12 + MySQL** y frontend **Blade + Tailwind CSS** (Alpine.js + ApexCharts).

Cada restaurante (tenant) tiene sus datos **aislados automáticamente** mediante un `restaurante_id` y un *global scope*. Incluye landing de ventas, registro con prueba gratis, planes y suscripciones, panel Super-Admin global y la app completa de gestión.

---

## ✅ Requisitos

- PHP **8.2+** (extensiones: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`)
- Composer · Node.js 18+ y npm · MySQL 5.7+ / MariaDB

---

## 🚀 Instalación

```bash
composer install
php artisan key:generate
```

Crea la base de datos en MySQL:
```sql
CREATE DATABASE saas_restaurante CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Ajusta `DB_PASSWORD` en `.env` si tu `root` tiene contraseña. Luego:
```bash
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

Abre 👉 **http://localhost:8000**

> En desarrollo puedes usar `npm run dev` en lugar de `npm run build`.

---

## 🔐 Accesos de demostración

**Super Admin del SaaS** (gestiona todos los restaurantes y planes):

| Email             | Contraseña |
|-------------------|------------|
| `super@saas.test` | `password` |

→ Inicia sesión y entra automáticamente al panel **/superadmin**.

**Restaurante demo 1 — "Mi Restaurante VIP"** (plan Profesional, activo, con historial de ventas):

| Rol   | Email                      | Contraseña |
|-------|----------------------------|------------|
| Admin | `admin@restaurante.test`   | `password` |
| Cajero| `cajero1@restaurante.test` | `password` |
| Mesero| `mesero1@restaurante.test` | `password` |
| Cocina| `cocina1@restaurante.test` | `password` |

**Restaurante demo 2 — "La Buena Mesa"** (plan Emprendedor, en periodo de prueba):

| Rol   | Email                       | Contraseña |
|-------|-----------------------------|------------|
| Admin | `admin@labuenamesa.test`    | `password` |

> También puedes crear un restaurante nuevo desde la landing (**/**) → "Empezar gratis", que inicia con 14 días de prueba.

---

## 🏗️ Arquitectura SaaS

**Multi-tenancy (una BD + `restaurante_id`).** El trait `App\Models\Concerns\BelongsToTenant` aplica un *global scope* que filtra cada consulta por el restaurante activo y asigna el tenant al crear registros. El middleware `IdentifyTenant` detecta el restaurante del usuario autenticado en cada petición.

**Roles y accesos:**
- `superadmin` → panel global `/superadmin` (sin tenant).
- `admin` → dueño del restaurante (acceso total + usuarios, configuración y suscripción).
- `cajero`, `mesero`, `cocina` → personal operativo.

**Planes y suscripciones.** Los planes (Emprendedor / Profesional / Premium) definen límites de mesas, productos y usuarios. Cada restaurante tiene un estado (`trial`, `activo`, `suspendido`, `cancelado`). El middleware `EnsureSubscriptionActive` bloquea la app si la suscripción no está vigente y redirige a la página de **Suscripción**, donde se renueva con un **pago simulado** (listo para conectar Culqi/MercadoPago/Stripe después).

**Onboarding.** El registro público crea el restaurante + su usuario administrador e inicia el periodo de prueba.

---

## 🧭 Mapa de rutas

| Zona | Ruta | Descripción |
|------|------|-------------|
| Pública | `/` | Landing de ventas con precios |
| Pública | `/register` | Registro de nuevo restaurante (trial) |
| Pública | `/login` | Inicio de sesión |
| Tenant | `/dashboard`, `/pos`, `/mesas`, `/reservas`, `/pedidos`, `/productos`, `/categorias`, `/clientes`, `/insumos`, `/reportes` | App del restaurante |
| Tenant (admin) | `/usuarios`, `/configuracion`, `/suscripcion` | Administración del restaurante |
| Super Admin | `/superadmin`, `/superadmin/restaurantes`, `/superadmin/planes` | Gestión global del SaaS |

---

## 🧩 Módulos del restaurante

Dashboard · Punto de Venta · Mesas · Reservas · Pedidos · Carta (categorías y productos) · Clientes · Inventario · Reportes · Usuarios · Configuración · Suscripción.

---

## 🛠️ Comandos útiles

```bash
php artisan migrate:fresh --seed   # Reiniciar BD con datos demo
php artisan optimize:clear         # Limpiar cachés
npm run build                      # Compilar assets
```

## ❗ Solución de problemas

- **`vite manifest not found`** → `npm install && npm run build` (o `npm run dev`).
- **`Access denied for user 'root'`** → ajusta `DB_PASSWORD` en `.env`.
- **Página 419 / token** → `php artisan key:generate` y vuelve a iniciar sesión.
- **No ves datos / "se mezclan" restaurantes** → cada usuario solo ve los datos de SU restaurante; usa el Super Admin para la vista global.

---

## 🎨 Stack

Laravel 12 · MySQL · Blade + Tailwind CSS · Alpine.js · ApexCharts · Vite.

© Mi Restaurante VIP — Plataforma SaaS.
