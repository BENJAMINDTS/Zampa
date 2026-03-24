## 📝 Descripción
<!-- Resumen claro de los cambios introducidos en este PR -->


## 🔗 Issue Relacionado
<!-- Enlaza el issue o tarea que resuelve este PR -->
Closes #...

## 🔢 Bloque del Roadmap
<!-- Indica el bloque al que pertenece, ej: Bloque 1.6 -->
**Bloque:** X.X

## 🧩 Tipo de Cambio
- [ ] `feat` — Nueva funcionalidad
- [ ] `fix` — Corrección de bug
- [ ] `refactor` — Refactorización sin cambio de comportamiento
- [ ] `style` — Cambios visuales sin lógica
- [ ] `docs` — Solo documentación
- [ ] `test` — Pruebas

---

## ✅ Checklist — Backend (BenjaminDTS)
- [ ] La rama parte de `main` actualizado
- [ ] Un commit por acción concreta (rutas, controlador, vista por separado)
- [ ] Todos los métodos tienen PHPDoc completo (`@param`, `@return`)
- [ ] `@author` solo en la cabecera de la clase, nunca en los métodos
- [ ] `abort_if($model->user_id !== Auth::id(), 403)` en `edit`, `update` y `destroy`
- [ ] Validaciones con `$request->validate([...])` en `store` y `update`
- [ ] Mensajes flash implementados (`->with('success', '...')`)
- [ ] Si hay imágenes: se borra la vieja antes de guardar la nueva
- [ ] Migraciones documentadas si hay cambios en BBDD
- [ ] La aplicación arranca sin errores (`php artisan serve`)

## ✅ Checklist — Frontend (SebastianBCF)
- [ ] `npm run build` ejecutado con los cambios de Tailwind
- [ ] Vistas muestran correctamente los mensajes flash (`@if(session('success'))`)
- [ ] HTML5 semántico y Mobile-First
- [ ] Sin `console.log` en el código

## ✅ Checklist — QA (Ayrton)
- [ ] Happy path probado y funciona correctamente
- [ ] Validaciones del formulario verificadas (campos requeridos, formatos)
- [ ] Multitenancy verificado: un usuario no accede a datos de otro
- [ ] Mensajes flash de éxito y error aparecen correctamente
- [ ] No hay regresiones en funcionalidades existentes

---

## 📸 Capturas de Pantalla
<!-- Si aplica, añade capturas antes/después de los cambios visuales -->

## 🔗 Contexto Adicional
<!-- Cualquier información relevante para el revisor -->
