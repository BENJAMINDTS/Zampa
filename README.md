# Zampa

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square)
![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square)
![Licencia](https://img.shields.io/badge/Licencia-Propietaria-red?style=flat-square)
![Tests](https://img.shields.io/badge/Tests-Pest%20PHP-green?style=flat-square)

Plataforma SaaS para la digitalización de bares y restaurantes. Permite gestionar carta digital, pedidos en tiempo real, pagos integrados y un chatbot IA contextualizado al menú. El cliente accede mediante QR a la carta desde su móvil, sin necesidad de instalar nada.

---

## Características principales

- Gestión completa de carta digital: categorías, productos, ingredientes y alérgenos automáticos
- Carta pública accesible por QR sin login, con filtros dinámicos de alérgenos por alérgeno
- Chatbot IA contextualizado a la carta del restaurante (OpenAI GPT en producción, Grok API para pruebas y desarrollo)
- Pedidos en tiempo real con panel de cocina y panel de barra diferenciados por destino
- Sistema de tapas configurable: gratuitas o de pago, precio fijo global o por producto, horario de cocina
- Pagos en efectivo y con tarjeta vía Stripe (modo test), con propina desglosada para el gerente
- Mapa visual de mesas drag & drop con formas, QR por mesa y límite según plan SaaS
- Gestión de personal: camareros y cocineros vinculados al admin del restaurante
- Panel Superadmin con gestión de planes, activación de negocios y mapa Leaflet.js
- Suite de tests completa con Pest PHP (feature + unit, OpenAI y Stripe siempre mockeados)

---

## Stack tecnológico

| Tecnología | Versión |
| ---------- | ------- |
| PHP | 8.2 |
| Laravel | 12.x |
| MySQL / MariaDB | 8.x / 10.4+ |
| Tailwind CSS | 3.x |
| Alpine.js | 3.x |
| Vite | 7.x |
| Node.js | 20.x |
| Composer | 2.x |
| OpenAI API | GPT-4o / GPT-4o-mini (producción) |
| Grok API (xAI) | Compatible con OpenAI SDK (pruebas/desarrollo) |
| Stripe | API v3 (modo test) |
| Pest PHP | 3.x |

---

## Requisitos previos

- PHP 8.2+ con extensiones: `mbstring`, `pdo_mysql`, `zip`, `curl`, `gd`
- Composer 2.x
- Node.js 20.x + npm
- MySQL 8.x o MariaDB 10.4+
- Servidor web (Apache/Nginx) o `php artisan serve` para desarrollo

---

## Instalación

```bash
# 1. Clonar el repositorio
git clone https://github.com/BENJAMINDTS/Zampa.git
cd Zampa

# 2. Configurar hooks de Git (bloquea commits directos a main)
git config core.hooksPath .githooks

# 3. Dependencias PHP
composer install

# 4. Dependencias JS y compilar assets
npm install && npm run build

# 5. Configurar entorno
cp .env.example .env
php artisan key:generate

# 6. Ajustar DB, STRIPE_KEY y la API key del chatbot en .env
#    - Desarrollo: usa GROK_API_KEY (Grok xAI, gratuito)
#    - Producción:  usa OPENAI_API_KEY (OpenAI, requiere créditos por cliente)

# 7. Crear base de datos y ejecutar migraciones con datos de prueba
php artisan migrate:fresh --seed

# 8. Enlace simbólico para imágenes de productos
php artisan storage:link

# 9. Levantar servidor
php artisan serve        # http://127.0.0.1:8000
npm run dev              # Vite HMR (solo en desarrollo)
```

---

## Variables de entorno

Copia `.env.example` a `.env` y configura al menos las siguientes:

| Variable | Descripción |
| -------- | ----------- |
| `APP_NAME` | Nombre de la aplicación (`Zampa`) |
| `APP_ENV` | Entorno (`local` / `production`) |
| `APP_URL` | URL base de la aplicación |
| `APP_KEY` | Clave de cifrado (generada con `php artisan key:generate`) |
| `APP_DEBUG` | `true` en desarrollo, `false` en producción |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | `127.0.0.1` |
| `DB_PORT` | `3306` |
| `DB_DATABASE` | `zampa` |
| `DB_USERNAME` | Usuario de MySQL |
| `DB_PASSWORD` | Contraseña de MySQL |
| `FILESYSTEM_DISK` | `public` (para imágenes de productos) |
| `OPENAI_API_KEY` | API key del proveedor de IA para el chatbot (ver nota abajo) |
| `GROK_API_KEY` | API key de Grok (xAI) — alternativa gratuita para pruebas y desarrollo |
| `STRIPE_KEY` | Clave pública de Stripe (modo test) |
| `STRIPE_SECRET` | Clave secreta de Stripe (modo test) |

### Chatbot IA — OpenAI vs Grok

El chatbot usa la API de OpenAI como proveedor oficial en producción. Para entornos de desarrollo y pruebas se puede utilizar **Grok (xAI)**, cuya API es compatible con el SDK de OpenAI, lo que permite sustituirla sin cambiar código.

| Escenario | Proveedor recomendado | Variable a configurar |
| --------- | --------------------- | --------------------- |
| Desarrollo y pruebas | **Grok (xAI)** — gratuito con límite generoso | `GROK_API_KEY` |
| Producción (clientes reales) | **OpenAI** — requiere créditos propios por cliente | `OPENAI_API_KEY` |

> **Importante:** En un entorno real cada restaurante debe operar con sus propias credenciales de OpenAI y créditos suficientes. No compartir una única API key entre todos los clientes de la plataforma.

---

## Credenciales de prueba

### Panel de administración

| Campo | Valor |
| ----- | ----- |
| Email | `admin@zampa.app` |
| Contraseña | `password` |

### Tarjeta Stripe (modo test)

| Campo | Valor |
| ----- | ----- |
| Número | `4242 4242 4242 4242` |
| Caducidad | Cualquier fecha futura |
| CVC | Cualquier 3 dígitos |

---

## Arquitectura y multitenancy

La tabla `users` es el centro del sistema multitenancy. Cada usuario con rol `admin` es el propietario de un restaurante y tiene sus propios recursos aislados (categorías, productos, mesas, pedidos). El personal del restaurante (camareros y cocineros) se vincula al admin mediante el campo `admin_id` en `users`.

Los roles disponibles son: `admin`, `waiter`, `kitchen` y `superadmin`. El superadmin gestiona toda la plataforma (planes, negocios y mapa de restaurantes).

---

## Comandos de desarrollo

```bash
# Servidor y assets
php artisan serve
npm run dev

# Base de datos
php artisan migrate:fresh --seed
php artisan storage:link

# Tests
php artisan test
php artisan test --coverage
php artisan test --parallel
php artisan test tests/Feature/Bloque1/CategoryTest.php
php artisan test --filter "it allows admin"

# Generadores
php artisan make:controller NombreController --resource
php artisan make:model Nombre -m
php artisan make:request NombreRequest
php artisan make:policy NombrePolicy --model=Nombre
php artisan make:factory NombreFactory --model=Nombre
```

---

## Progreso del desarrollo

**Progreso global: 93%** (57/63 sub-bloques completados)

| Sub-bloque | Descripción | Estado |
| ---------- | ----------- | ------ |
| 1.1 | Categorías — Crear y Ver | Completado |
| 1.2 | Ingredientes — Crear y Ver | Completado |
| 1.3 | Productos — Crear y Ver + imágenes | Completado |
| 1.4 | Productos — Editar y Eliminar | Completado |
| 1.5 | Categorías — Editar y Eliminar | Completado |
| 1.6 | Ingredientes — Editar y Eliminar | Completado |
| 2.1 | Relación Platos-Ingredientes (N:M, tabla pivote) | Completado |
| 2.2 | Sistema de Alérgenos automático | Completado |
| 3.1 | Carta digital pública (acceso por QR sin login) | Completado |
| 3.2 | Filtros dinámicos de alérgenos | Completado |
| 3.3 | Generación de QR único por mesa | Completado |
| 4.1 | Carrito de la compra | Completado |
| 4.2 | Panel de Cocina (Comandas en tiempo real) | Completado |
| 5.1 | Modelos Conversation y Message | Completado |
| 5.2 | Servicios ChatService y OpenAIService | Completado |
| 5.3 | ChatController y rutas públicas API `/api/v1/` | Completado |
| 5.4 | Widget chat flotante en carta digital | Completado |
| 5.5 | Cierre de conversación y control de tokens | Completado |
| 6.1 | Enrutado de ítems por destino (kitchen / bar) | Completado |
| 6.2 | Panel de Barra para el camarero | Completado |
| 6.3 | Notificación al camarero cuando cocina completa una comanda | Completado |
| 6.4 | Sistema de Tapas configurable por el gerente | Completado |
| 6.5 | Tests del Bloque 6 | Completado |
| 7.1 | Solicitud de cuenta desde la carta pública | Completado |
| 7.2 | Pago en efectivo | Completado |
| 7.3 | Pago con tarjeta vía Stripe (modo test) | Completado |
| 7.4 | Pantalla de propina antes de pagar con tarjeta | Completado |
| 7.5 | Desglose de ingresos para el gerente | Completado |
| 7.6 | Tests del Bloque 7 | Completado |
| 8.1 | Interfaz drag & drop para crear, mover y eliminar mesas | Completado |
| 8.2 | Límite de mesas según plan del gerente | Completado |
| 8.3 | Formas de mesa (cuadrada, redonda, rectangular) | Completado |
| 8.4 | Generación de QR por mesa desde el mapa | Completado |
| 8.5 | Tests del Bloque 8 | Completado |
| 10.1 | Instalar Pest PHP y configurar entorno de tests | Completado |
| 10.2 | Factories (Plan, Category, Ingredient, Product, Table, Order…) | Completado |
| 10.3 | Feature tests Bloques 1 y 2 | Completado |
| 10.4 | Feature tests Bloques 3 y 4 | Completado |
| 10.5 | Feature tests Bloque 5 (OpenAI mockeado) | Completado |
| 10.6 | Unit tests de ChatService y OpenAIService | Completado |
| 12.1 | Migración `admin_id` nullable FK en `users` | Completado |
| 12.2 | Helper `ownerUserId()` en User model | Completado |
| 12.3 | `StaffController` — listar, crear y eliminar staff | Completado |
| 12.4 | Vistas del panel de staff | Completado |
| 12.5 | Tests del Bloque 12 | Completado |
| 13.1 | Migración `superadmin` + campos negocio en `users` + seeder equipo | Completado |
| 13.2 | Middleware `role:superadmin` + rutas `/superadmin/` + layout propio | Completado |
| 13.3 | Gestión de planes — CRUD completo | Completado |
| 13.4 | Gestión de negocios — crear admins, asignar plan, ver stats | Completado |
| 13.5 | Mapa de negocios — Leaflet.js con pin por negocio | Completado |
| 13.6 | Tests del Bloque 13 | Completado |
| 9.1 | Ingresos desglosados por método de pago y período | Completado |
| 9.2 | Mesa que más ingresos genera | Completado |
| 9.3 | Platos más pedidos | Completado |
| 9.4 | Horas punta y ticket medio por mesa | Completado |
| 9.5 | Tests del Bloque 9 | Completado |
| 16.1 | Campos `split_payment_enabled` y `split_payment_max_parts` en `users` | Completado |
| 16.2 | Panel del gerente — activar/desactivar cobro partido y configurar partes | Completado |
| 16.3 | Carta pública — Modo A (por ítems) y Modo B (equitativo) | Completado |
| 16.4 | `SplitPaymentController` + tabla `order_item_payments` + PaymentIntents independientes | Completado |
| 16.5 | Tests completos del Bloque 16 (62 Feature + 6 Unit) | Completado |
| 11.1 | Design system (colores Zampa, tipografía, tokens Tailwind) | Pendiente |
| 11.2 | Layout principal y navegación (sidebar, topbar, dark mode) | Pendiente |
| 11.3 | Vistas del panel admin | Pendiente |
| 11.4 | Carta digital pública (diseño final) | Pendiente |
| 11.5 | Panel de cocina y panel de barra | Pendiente |
| 11.6 | Dashboard del gerente y pantallas de pago | Pendiente |

---

## Estructura de la base de datos

| Tabla | Descripción |
| ----- | ----------- |
| `plans` | Planes SaaS — `name`, `price`, `max_tables` |
| `users` | Centro del multitenancy — roles: admin / waiter / kitchen / superadmin |
| `categories` | Categorías del menú — `destination`: kitchen / bar |
| `ingredients` | Ingredientes — `is_allergen` (boolean) |
| `products` | Productos — `image` (string), `is_active` (boolean) |
| `ingredient_product` | Pivote N:M productos-ingredientes — `quantity_base`, `is_removable`, `is_extra`, `extra_price` |
| `tables` | Mesas — `unique_hash` para QR, posición y forma para el editor gráfico |
| `orders` | Pedidos — `tip` separado del `total`, `payment_method`, `payment_status` |
| `order_items` | Líneas de pedido — precio snapshot en el momento del pedido |
| `order_item_modifications` | Modificaciones por ítem — `action`: add / remove ingrediente |
| `conversations` | Conversaciones del chatbot por mesa |
| `messages` | Mensajes individuales — `role`: user / assistant |
| `tapa_configs` | Configuración de tapas por restaurante (1:1 con users) |
| `kitchen_schedules` | Tramos horarios de apertura de cocina por tapa_config |
| `order_item_payments` | Pagos parciales del cobro partido — `mode`: items / equitative, `status`: pending / paid / failed |

---

## Equipo

| Miembro |
| ------- |
| **BenjaminDTS** |
| **SebastianBCF** |
| **Ayrton** |

---

## Licencia

Propietaria — © BenjaminDTS, SebastianBCF, Ayrton. Todos los derechos reservados.
