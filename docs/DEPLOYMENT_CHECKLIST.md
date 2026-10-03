# ✅ Checklist de Despliegue a Producción — SIP-Postgrado UNEFA

> Ejecuta este checklist **antes de hacer go-live** en cualquier servidor de producción.  
> Marca cada punto al completarlo.

---

## 1. 🔐 Entorno y Variables

- [ ] Copiar `.env.example` a `.env` en el servidor
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] Contraseña de BD fuerte (mínimo 20 caracteres, generada aleatoriamente)
- [ ] `DB_HOST` apunta a la instancia de producción
- [ ] El archivo `.env` **no está en el repositorio** (verificar `.gitignore`)

---

## 2. 🌐 Servidor Web (Apache)

- [ ] Habilitar `mod_rewrite` en Apache
- [ ] `AllowOverride All` en el `VirtualHost` del proyecto
- [ ] Verificar que los `.htaccess` de `logs/` y `uploads/rate/` funcionan:
  ```bash
  curl -I http://tudominio.com/SIP/logs/.htaccess
  # Debe devolver 403 Forbidden
  ```
- [ ] Configurar `ErrorDocument` en Apache para apuntar a `errors/404.php` y `errors/500.php`:
  ```apache
  ErrorDocument 404 /SIP/errors/404.php
  ErrorDocument 500 /SIP/errors/500.php
  ```
- [ ] Deshabilitar `ServerTokens` y `ServerSignature` en `httpd.conf`

---

## 3. 🔒 HTTPS y Cabeceras

- [ ] Certificado SSL/TLS instalado y válido
- [ ] Redireccion HTTP → HTTPS activa
- [ ] `BASE_URL` en el código usa `https://`
- [ ] Verificar cabeceras de seguridad con [securityheaders.com](https://securityheaders.com):
  - [ ] `X-Frame-Options: DENY`
  - [ ] `X-Content-Type-Options: nosniff`
  - [ ] `Content-Security-Policy` configurado
  - [ ] `Strict-Transport-Security` (HSTS)

---

## 4. 🗄️ Base de Datos

- [ ] Ejecutar `postgrado.sql` en la BD de producción (o restaurar backup)
- [ ] Verificar todos los índices creados (`CREATE INDEX IF NOT EXISTS…`)
- [ ] Usuario de BD con permisos mínimos necesarios (no usar `postgres` superuser)
- [ ] Backup automático configurado (cron diario)
- [ ] Conexión a BD **solo** desde el servidor de aplicación (firewall de PostgreSQL: `pg_hba.conf`)

---

## 5. 📂 Permisos de Archivos

```bash
# Solo PHP/Apache puede escribir en uploads y logs
chmod 750 uploads/ logs/ uploads/rate/
chmod 640 .env
chmod 644 *.php
```

- [ ] `uploads/` no permite ejecución de PHP (verificar `.htaccess` existente)
- [ ] `logs/` bloqueado al acceso web
- [ ] `uploads/rate/` bloqueado al acceso web

---

## 6. ⚙️ PHP

- [ ] PHP 8.1 o superior en producción
- [ ] Extensiones activas: `pdo_pgsql`, `mbstring`, `fileinfo`, `gd`
- [ ] En `php.ini` de producción:
  ```ini
  display_errors = Off
  log_errors = On
  error_log = /var/log/php_errors.log
  expose_php = Off
  session.cookie_secure = On
  session.cookie_httponly = On
  session.cookie_samesite = Lax
  ```

---

## 7. 🚦 Rate Limiting y Caché

- [ ] Directorio `uploads/rate/` existe y es escribible por Apache
- [ ] (Opcional) Si el servidor tiene Redis, considerar migrar `RateLimiter` a Redis

---

## 8. 📋 Funcional — Smoke Test por Rol

Ejecutar el siguiente flujo antes del go-live:

| # | Flujo | Rol | Estado |
|---|-------|-----|--------|
| 1 | Registro de nuevo aspirante + subida de documentos | Aspirante | ☐ |
| 2 | Aprobación del aspirante y cambio a Estudiante | Admin/Coordinador | ☐ |
| 3 | Creación de sección con docente asignado | Coordinador | ☐ |
| 4 | Inscripción en sección disponible | Estudiante | ☐ |
| 5 | Carga de notas en la sección | Docente | ☐ |
| 6 | Registro y verificación de pago | Secretaria | ☐ |
| 7 | Descarga de constancia de estudios (PDF/HTML) | Estudiante | ☐ |
| 8 | Generación de acta de calificaciones | Coordinador/Director | ☐ |
| 9 | Exportación CSV de usuarios | Admin | ☐ |
| 10 | Toggle de dark mode en todos los dashboards | Todos | ☐ |
| 11 | Verificar que 429 se activa al llamar una API >60 veces/min | — | ☐ |
| 12 | Verificar página 404 personalizada en URL inexistente | — | ☐ |

---

## 9. 🛡️ Revisión Final de Seguridad

- [ ] No existe `test.php` en el servidor
- [ ] No existe `config/conexion.php`
- [ ] Logs de auditoría (`logs_auditoria` en BD) funcionando
- [ ] Logger de archivo escribe correctamente en `logs/app.log`
- [ ] Intentar login con credenciales incorrectas 5 veces y verificar que no se bloquea indefinidamente (sin lockout todavía — pendiente Fase 6)

---

## 10. 📝 Post-Despliegue

- [ ] Primer commit con tag de versión: `git tag v1.0.0`
- [ ] Documentar URL de producción en el equipo
- [ ] Configurar monitoreo (UptimeRobot u otro) sobre `index.php`
- [ ] Comunicar a los usuarios el acceso al sistema

---

## ⏭️ Pendiente para Fase 6 (Post-Producción)

| Ítem | Descripción |
|------|-------------|
| **Lockout por intentos fallidos** | Bloqueo temporal de cuenta tras 5 intentos de login fallidos |
| **Recuperación de contraseña** | Flujo vía email (PHPMailer o SMTP nativo) |
| **Notificaciones internas** | Sistema de mensajes entre roles dentro de la plataforma |
| **Panel de Auditoría visual** | Vista de `logs_auditoria` con filtros para Admin |
| **2FA (opcional)** | Segundo factor de autenticación para Admin y Director |
