# Zampa

Plataforma SaaS para la digitalización de bares y restaurantes. Permite gestionar la carta digital, ingredientes, alérgenos, mesas y comandas en tiempo real mediante un panel de administración y una carta pública accesible por QR.

---

## Stack tecnológico

| Tecnología | Versión |
| ---------- | ------- |
| PHP | 8.2 |
| Laravel | 12.x |
| MySQL / MariaDB | 10.4+ |
| Tailwind CSS | 3.x |
| Vite | 7.x |
| Alpine.js | 3.x |
| Node.js | 20.x |
| Composer | 2.x |

---

## Requisitos previos

- PHP 8.2+ con extensiones: `mbstring`, `pdo_mysql`, `zip`, `curl`, `gd`
- Composer 2.x
- Node.js 20.x + npm
- MySQL 8.x o MariaDB 10.4+
- Servidor web (Apache/Nginx) o `php artisan serve` para desarrollo

---

## Instalación y despliegue

```bash
# 1. Clonar el repositorio
git clone https://github.com/BENJAMINDTS/Zampa.git
cd Zampa

# 2. Dependencias PHP
composer install

# 3. Dependencias JS y compilar assets
npm install && npm run build

# 4. Configurar entorno
cp .env.example .env
php artisan key:generate

# 5. Configurar base de datos en .env
# DB_DATABASE=zampa
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Crear base de datos y ejecutar migraciones con datos de prueba
php artisan migrate:fresh --seed

# 7. Enlace simbólico para imágenes
php artisan storage:link

# 8. Levantar servidor de desarrollo
php artisan serve        # <http://127.0.0.1:8000>
npm run dev              # Vite HMR en <http://localhost:5173> (solo desarrollo)
```

---

## Credenciales de prueba

| Campo | Valor |
| ----- | ----- |
| Email | admin@zampa.app |
| Contraseña | password |

---

## Progreso del desarrollo

**Progreso global: 60%** (35/58 sub-bloques completados)

| Sub-bloque | Descripción | Estado |
| ---------- | ----------- | ------ |
| 1.1 | Categorías — Crear y Ver | ✅ Completado |
| 1.2 | Ingredientes — Crear y Ver | ✅ Completado |
| 1.3 | Productos — Crear y Ver + imágenes | ✅ Completado |
| 1.4 | Productos — Editar y Eliminar | ✅ Completado |
| 1.5 | Categorías — Editar y Eliminar | ✅ Completado |
| 1.6 | Ingredientes — Editar y Eliminar | ✅ Completado |
| 2.1 | Relación Platos-Ingredientes (N:M, tabla pivote) | ✅ Completado |
| 2.2 | Sistema de Alérgenos automático | ✅ Completado |
| 3.1 | Carta digital pública (acceso por QR sin login) | ✅ Completado |
| 3.2 | Filtros dinámicos de alérgenos | ✅ Completado |
| 3.3 | Generación de QR único por mesa | ✅ Completado |
| 4.1 | Carrito de la compra | ✅ Completado |
| 4.2 | Panel de Cocina (Comandas en tiempo real) | ✅ Completado |
| 5.1 | Modelos Conversation y Message | ✅ Completado |
| 5.2 | Servicios ChatService y OpenAIService | ✅ Completado |
| 5.3 | ChatController y rutas públicas API `/api/v1/` | ✅ Completado |
| 5.4 | Widget chat flotante en carta digital | ✅ Completado |
| 5.5 | Cierre de conversación y control de tokens | ✅ Completado |
| 10.1 | Instalar Pest PHP y configurar entorno de tests | ✅ Completado |
| 10.2 | Factories (Plan, Category, Ingredient, Product, Table, Order…) | ✅ Completado |
| 10.3 | Feature tests Bloques 1 y 2 | ✅ Completado |
| 10.4 | Feature tests Bloques 3 y 4 | ✅ Completado |
| 10.5 | Feature tests Bloque 5 (OpenAI mockeado) | ✅ Completado |
| 10.6 | Unit tests de ChatService y OpenAIService | ✅ Completado |
| 6.1 | Enrutado de ítems por destino (kitchen / bar) | ✅ Completado |
| 6.2 | Panel de Barra para el camarero | ✅ Completado |
| 6.3 | Notificación al camarero cuando cocina completa una comanda | ✅ Completado |
| 6.4 | Sistema de Tapas configurable por el gerente | ✅ Completado |
| 6.5 | Tests del Bloque 6 | ✅ Completado |
| 7.1 | Solicitud de cuenta desde la carta pública | ✅ Completado |
| 7.2 | Pago en efectivo | ✅ Completado |
| 7.3 | Pago con tarjeta vía Stripe (modo test) | ✅ Completado |
| 7.4 | Pantalla de propina antes de pagar con tarjeta | ✅ Completado |
| 7.5 | Desglose de ingresos para el gerente | ✅ Completado |
| 7.6 | Tests del Bloque 7 | ✅ Completado |
| 12.1 | Migración `admin_id` nullable FK en `users` | 🔒 Pendiente |
| 12.2 | Helper `ownerUserId()` en User model + actualizar controllers multitenancy | 🔒 Pendiente |
| 12.3 | `StaffController` — listar, crear y eliminar staff | 🔒 Pendiente |
| 12.4 | Vistas del panel de staff (tabla + formulario de alta) | 🔒 Pendiente |
| 12.5 | Tests del Bloque 12 | 🔒 Pendiente |
| 13.1 | Migración `superadmin` + campos negocio en `users` + seeder equipo | 🔒 Pendiente |
| 13.2 | Middleware `role:superadmin` + rutas `/superadmin/` + layout propio | 🔒 Pendiente |
| 13.3 | Gestión de planes — CRUD completo | 🔒 Pendiente |
| 13.4 | Gestión de negocios — crear admins, asignar plan, ver stats | 🔒 Pendiente |
| 13.5 | Mapa de negocios — Leaflet.js con pin por negocio | 🔒 Pendiente |
| 13.6 | Tests del Bloque 13 | 🔒 Pendiente |
| 8.1 | Interfaz drag & drop para crear, mover y eliminar mesas | 🔒 Pendiente |
| 8.2 | Límite de mesas según plan del gerente | 🔒 Pendiente |
| 8.3 | Formas de mesa (cuadrada, redonda, rectangular) | 🔒 Pendiente |
| 8.4 | Generación de QR por mesa desde el mapa | 🔒 Pendiente |
| 8.5 | Tests del Bloque 8 | 🔒 Pendiente |
| 9.1 | Ingresos desglosados por método de pago y período | 🔒 Pendiente |
| 9.2 | Mesa que más ingresos genera | 🔒 Pendiente |
| 9.3 | Platos más pedidos | 🔒 Pendiente |
| 9.4 | Horas punta y ticket medio por mesa | 🔒 Pendiente |
| 9.5 | Tests del Bloque 9 | 🔒 Pendiente |
| 11.1 | Design system (colores Zampa, tipografía, tokens Tailwind) | 🔒 Pendiente |
| 11.2 | Layout principal y navegación (sidebar, topbar, dark mode) | 🔒 Pendiente |
| 11.3 | Vistas del panel admin | 🔒 Pendiente |
| 11.4 | Carta digital pública (diseño final) | 🔒 Pendiente |
| 11.5 | Panel de cocina y panel de barra | 🔒 Pendiente |
| 11.6 | Dashboard del gerente y pantallas de pago | 🔒 Pendiente |

---

## Estructura de la base de datos

| Tabla | Relación clave |
| ----- | -------------- |
| plans | 1:N con users |
| users | Centro del Multitenancy (user_id en todo) |
| categories | user_id FK — campo `destination`: kitchen / bar |
| ingredients | user_id FK — campo `is_allergen` (boolean) |
| products | user_id FK, category_id FK, image (string) |
| ingredient_product | Pivote N:M productos-ingredientes |
| tables | user_id FK, unique_hash para QR |
| orders | table_id FK, tip separado del total |
| order_items | order_id FK, product_id FK |
| order_item_modifications | order_item_id FK — action: add / remove |
| conversations | table_id FK — chatbot IA por mesa |
| messages | conversation_id FK — role: user / assistant |

---

## Equipo

| Miembro |
| ------- |
| **BenjaminDTS** |
| **SebastianBCF** |
| **Ayrton** |

---

## Variables de entorno relevantes

Copia `.env.example` a `.env` y configura al menos:

```env
APP_NAME=Zampa
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zampa
DB_USERNAME=root
DB_PASSWORD=

# Chatbot IA — Groq (Llama 3.1 8B Instant)
GROQ_API_KEY=
```

---

## Licencia

Propietaria — © BenjaminDTS, SebastianBCF, Ayrton. Todos los derechos reservados.
