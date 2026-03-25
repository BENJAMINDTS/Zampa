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

**Progreso global: ~58%** (7 de 12 bloques completados)

| Bloque | Descripción | Estado |
| ------ | ----------- | ------ |
| 1.1 | Categorías — Crear y Ver | ✅ Completado |
| 1.2 | Ingredientes — Crear y Ver | ✅ Completado |
| 1.3 | Productos — Crear y Ver + imágenes | ✅ Completado |
| 1.4 | Productos — Editar y Eliminar | ✅ Completado |
| 1.5 | Categorías — Editar y Eliminar | ✅ Completado |
| 1.6 | Ingredientes — Editar y Eliminar | ✅ Completado |
| 2.1 | Relación Platos-Ingredientes (N:M, tabla pivote) | ✅ Completado |
| 2.2 | Sistema de Alérgenos automático | 🚧 En progreso |
| 3.1 | Carta digital pública (acceso por QR sin login) | 🔒 Pendiente |
| 3.2 | Filtros dinámicos (Vegano, Sin Gluten...) | 🔒 Pendiente |
| 3.3 | Generación de QR único por mesa | 🔒 Pendiente |
| 4.1 | Carrito de la compra | 🔒 Pendiente |
| 4.2 | Panel de Cocina (Comandas en tiempo real) | 🔒 Pendiente |

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

---

## Equipo

| Miembro | Rol |
| ------- | --- |
| **BenjaminDTS** | Arquitectura Backend y Base de Datos |
| **SebastianBCF** | Frontend y Vistas (Blade + Tailwind) |
| **Ayrton** | QA, Testing y Sistemas |

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
```

---

## Licencia

Propietaria — © BenjaminDTS/SebastianBCF/AyrtonAlania. Todos los derechos reservados.
