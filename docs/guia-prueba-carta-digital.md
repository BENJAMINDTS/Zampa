# Guía de prueba — Carta digital y Menú del Día

## Requisitos previos

```bash
php artisan migrate:fresh --seed
php artisan storage:link
npm run build          # o npm run dev en paralelo
```

Credenciales del seeder: `admin@zampa.app` / `password`

---

## Procesos que deben estar corriendo

Necesitas **tres terminales** abiertas en paralelo:

```bash
# Terminal 1 — Servidor web
php artisan serve

# Terminal 2 — Assets (solo en desarrollo)
npm run dev

# Terminal 3 — Cola de trabajos (OBLIGATORIO para menú del día)
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

> **Por qué es obligatorio el queue worker**
> Los pedidos de menú del día se procesan mediante el job `SendDailyMenuRound`,
> que envía cada ronda (entrantes, principales…) a cocina en el momento correcto.
> Sin el worker, el pedido se guarda en la BD pero nunca llega al panel de cocina ni de barra.

---

## Obtener la URL de una mesa

1. Inicia sesión en `http://127.0.0.1:8000`
2. Ve a **Mapa de mesas** → haz clic en el icono QR de cualquier mesa
3. Copia la URL con el hash, tiene la forma:
   ```
   http://127.0.0.1:8000/carta/{hash}
   ```
   También puedes consultar el hash directamente en la BD:
   ```sql
   SELECT name, unique_hash FROM tables LIMIT 5;
   ```

---

## Flujo completo del Menú del Día

### Paso 1 — Crear el menú (panel del gerente)

1. `http://127.0.0.1:8000/daily-menus/create`
2. Rellena título, descripción, precio y horario de disponibilidad
3. Guarda → serás redirigido al índice

### Paso 2 — Configurar secciones

1. `http://127.0.0.1:8000/daily-menus/{id}/sections`
2. Añade secciones (Primer plato, Segundo plato, Postre, etc.)
3. Para cada sección configura:
   - **Obligatoria**: si el cliente debe elegir sí o sí
   - **Incluida en precio**: si no tiene coste extra
   - **Máx. productos elegibles**: 1 = radio (solo uno), 2+ = multi-select (puede elegir varios)
4. Asigna productos a cada sección con el buscador
5. Guarda la configuración de cada sección individualmente

### Paso 3 — Hacer el pedido (carta pública)

1. Abre `http://127.0.0.1:8000/carta/{hash}` en una pestaña **sin sesión** (o ventana privada)
2. El banner azul "Menú del día" aparece en la parte superior si el menú está activo y con disponibilidad
3. Pulsa **Ver el menú completo →**
4. Sigue el stepper:
   - Selecciona productos en cada sección (respeta el límite `max_quantity`)
   - Ajusta los tiempos de entrega si hay varias rondas (usa las flechas ↑↓)
   - Revisa el resumen y confirma
5. Deberías ver la pantalla de éxito "¡Pedido enviado!"

> **Si el banner no aparece** → comprueba que el menú está activo y que la hora actual está dentro del horario configurado.

### Paso 4 — Verificar en cocina y barra

- Cocina: `http://127.0.0.1:8000/cocina`
- Barra:  `http://127.0.0.1:8000/bar`

Los ítems del menú del día aparecen con un badge **"Menú del Día"** para distinguirlos de los pedidos normales. La primera ronda llega inmediatamente; las siguientes según el tiempo configurado (el worker lo gestiona).

> **Si no aparecen los ítems** → verifica que el worker está corriendo en la terminal 3.

---

## Flujo de pedido normal (sin menú del día)

1. Abre `http://127.0.0.1:8000/carta/{hash}`
2. Añade productos al carrito desde las categorías
3. Pulsa **Enviar pedido**
4. Verifica en `http://127.0.0.1:8000/cocina` (ítems de cocina) y `http://127.0.0.1:8000/bar` (ítems de barra)

> El pedido normal **no necesita queue worker**, llega directamente a los paneles.

---

## Prueba de pago (Stripe modo test)

Activa la solicitud de cuenta desde la carta pública → selecciona pago con tarjeta → usa la tarjeta de prueba de Stripe:

```
Número:    4242 4242 4242 4242
Expiración: cualquier fecha futura
CVC:       cualquier 3 dígitos
```

---

## Comandos útiles durante las pruebas

```bash
# Reiniciar la BD y volver a sembrar datos
php artisan migrate:fresh --seed

# Ver los jobs pendientes en cola
php artisan queue:monitor

# Reintentar jobs fallidos
php artisan queue:retry all

# Listar jobs fallidos
php artisan queue:failed

# Vaciar la cola
php artisan queue:flush
```

---

## Producción / Staging — configuración del worker

En producción no se ejecuta el worker a mano. Se configura **Supervisor** para que lo mantenga vivo:

```ini
; /etc/supervisor/conf.d/zampa-worker.conf
[program:zampa-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/zampa/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/zampa-worker.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start zampa-worker:*
```

---

## Problemas frecuentes

| Síntoma | Causa probable | Solución |
|---------|----------------|----------|
| Banner del menú no aparece | Menú inactivo o fuera de horario | Revisar estado y horario en `/daily-menus` |
| Pedido enviado pero no llega a cocina | Worker no está corriendo | Ejecutar `php artisan queue:work` |
| Solo se puede elegir 1 producto aunque `max_quantity = 2` | Cache de vista antigua | `php artisan view:clear && npm run build` |
| Error 419 al enviar el pedido | Token CSRF expirado | Recargar la página de la carta |
| Jobs en cola pero no se procesan | `QUEUE_CONNECTION` no es `database` | Verificar `.env`: `QUEUE_CONNECTION=database` |
