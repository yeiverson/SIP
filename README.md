# SIP — Sistema Integral de Postgrado UNEFA

[![PHP](https://img.shields.io/badge/PHP-8.x-blue.svg)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15+-316192.svg)](https://postgresql.org)
[![License](https://img.shields.io/badge/licencia-Uso_Institucional-001a57.svg)](#)

> **Sistema de gestión académica** para el Vicerrectorado de Investigación, Postgrado y Recreación de la UNEFA. Administra usuarios, inscripciones, notas, pagos, documentos y reportes para 7 perfiles de rol.

---

## 📋 Tabla de Contenidos

- [Tecnologías](#tecnologías)
- [Roles del Sistema](#roles-del-sistema)
- [Estructura del Proyecto](#estructura-del-proyecto)
- [Instalación y Configuración](#instalación-y-configuración)
- [Variables de Entorno](#variables-de-entorno)
- [Funcionalidades por Rol](#funcionalidades-por-rol)
- [API Endpoints](#api-endpoints)
- [Seguridad](#seguridad)
- [Despliegue a Producción](#despliegue-a-producción)

---

## Tecnologías

| Capa | Tecnología |
|------|-----------|
| Backend | PHP 8.x (sin frameworks) |
| Base de datos | PostgreSQL 15+ (PDO) |
| Frontend | HTML5 · CSS3 · JavaScript ES6 |
| Servidor local | XAMPP (Apache) |
| Control de versiones | Git |

---

## Roles del Sistema

| rol_id | Nombre | Descripción |
|--------|--------|-------------|
| 1 | Administrador | Control total del sistema |
| 2 | Coordinador | Gestión de secciones y estudiantes por sede |
| 3 | Docente | Carga de notas, consulta de secciones asignadas |
| 4 | Secretaria/Tesorería | Validación de pagos y documentos |
| 5 | Director | Reportes y estadísticas globales |
| 6 | Estudiante | Inscripción, kardex y constancias |
| 7 | Aspirante | Registro e inicio de proceso de admisión |

---

## Estructura del Proyecto

```
SIP/
├── config/
│   └── database.php          # Conexión PDO a PostgreSQL
├── controlador/
│   ├── api/                  # Endpoints JSON (rate limited)
│   │   ├── buscar_asignaturas.php
│   │   ├── buscar_estudiante.php
│   │   ├── buscar_referencia.php
│   │   └── listar_documentos.php
│   ├── procesar_login.php
│   ├── descargar_documento.php
│   └── reportes.php          # Constancias, actas, CSV
├── css/
│   ├── dashboard.css         # Componentes de dashboard
│   └── tu_estilo.css         # Sistema de diseño + dark mode
├── errors/
│   ├── 404.php               # Página 404 personalizada
│   └── 500.php               # Página 500 personalizada
├── imagenes/                 # Assets estáticos (WebP)
├── includes/
│   ├── auth_check.php        # Verificación de sesión y rol
│   ├── autoloader.php        # PSR-4 nativo
│   ├── functions.php         # Helpers: h(), flash(), csrf_token()…
│   ├── Logger.php            # Logger estructurado (logs/app.log)
│   ├── logs.php              # Auditoría en BD (logs_auditoria)
│   ├── queries_usuarios.php  # Consultas de usuario
│   ├── RateLimiter.php       # Rate limiting por IP (archivos)
│   └── template_header.php   # Header compartido + dark mode toggle
├── logs/                     # Logs de archivo (protegido .htaccess)
├── modelos/
│   ├── Baremo.php            # Modelo de Baremo de admisión
│   ├── Seccion.php           # Modelo de Sección académica
│   └── Usuario.php           # Modelo de Usuario
├── uploads/                  # Documentos subidos por aspirantes
│   └── rate/                 # Contadores de rate limit (privado)
├── vistas/
│   ├── admin/dashboard.php
│   ├── aspirante/dashboard.php
│   ├── coordinador/dashboard.php
│   ├── director/dashboard.php
│   ├── docente/dashboard.php
│   ├── estudiante/dashboard.php
│   └── secretaria/dashboard.php
├── .env                      # Variables de entorno (NO versionar)
├── .env.example              # Plantilla de variables
├── config.php                # Autoloader + timezone + BD
├── index.php                 # Punto de entrada (login)
├── procesar_registro.php     # Flujo de registro de aspirantes
└── postgrado.sql             # Esquema SQL completo
```

---

## Instalación y Configuración

### Requisitos previos
- XAMPP con PHP 8.1+ y Apache
- PostgreSQL 15+
- Extensión PHP: `pdo_pgsql`, `mbstring`, `fileinfo`

### Pasos

1. **Clonar el repositorio** en `c:\xampp\htdocs\SIP`

2. **Crear la base de datos** en PostgreSQL:
   ```sql
   CREATE DATABASE unefa_postgrado ENCODING 'UTF8';
   \c unefa_postgrado
   \i postgrado.sql
   ```

3. **Configurar variables de entorno:**
   ```bash
   cp .env.example .env
   ```
   Edita `.env` con tus credenciales (ver sección siguiente).

4. **Permisos de directorios:**
   ```bash
   # En Linux/Mac
   chmod 750 logs/ uploads/rate/
   ```

5. **Acceder al sistema:**
   ```
   http://localhost/SIP/
   ```

---

## Variables de Entorno

Copia `.env.example` a `.env` y configura:

```env
DB_HOST=localhost
DB_PORT=5432
DB_NAME=unefa_postgrado
DB_USER=postgres
DB_PAS=tu_contraseña_segura

APP_ENV=development   # development | production
APP_DEBUG=true        # false en producción
```

> ⚠️ **Nunca versiones el archivo `.env`** — ya está en `.gitignore`.

---

## Funcionalidades por Rol

| Funcionalidad | Admin | Coord. | Docente | Secretaria | Director | Estudiante | Aspirante |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| Gestión de usuarios | ✅ | — | — | — | — | — | — |
| Crear secciones y aulas | ✅ | ✅ | — | — | — | — | — |
| Mi Horario Semanal Interactivo | — | — | ✅ | — | — | ✅ | — |
| Carga de notas y actas | ✅ | — | ✅ | — | — | — | — |
| Inscripción de materias | ✅ | ✅ | — | — | — | ✅ | — |
| Validar pagos y taquilla | ✅ | — | — | ✅ | — | — | — |
| Reportar pago de matrícula | — | — | — | — | — | ✅ | — |
| Revisar documentos | ✅ | ✅ | — | ✅ | — | — | — |
| Kardex / Constancias oficiales | ✅ | ✅ | — | ✅ | — | ✅ | — |
| Baremo Digital (CRUD y Ponderación) | ✅ | ✅ | — | — | — | — | ✅ |
| Reportes globales y fases | ✅ | — | — | — | ✅ | — | — |
| Exportar CSV de auditoría | ✅ | ✅* | — | — | — | — | — |

> *Coordinador exporta secciones de su sede solamente.

---

## Módulos Destacados

### 📅 Horarios de Clases Interactivos (Docentes y Estudiantes)
* **Vista Semanal Gráfica (Lunes a Sábado):** Grilla responsiva que desglosa cada bloque académico con hora de inicio/fin, materia, código, sección, aula y profesor/alumnos.
* **Métricas en Tiempo Real:** Cómputo automático de horas académicas semanales, total de días lectivos y número de bloques.
* **Integración en Tablas Principales:** Badges contextuales de día y hora incrustados directamente en el listado de asignaturas.
* **Reporte Imprimible:** Botón de impresión limpia optimizada (`window.print()`) para formato físico o PDF.

### 📋 Baremo Digital Interactivo (Admin)
* Gestión integral de preguntas de ponderación para el proceso de admisión.
* Creación, edición en vivo y activación/desactivación dinámica de preguntas por categoría (*Académico*, *Investigación*, *Otros*).

### 🌓 Sistema de Diseño y Modo Oscuro UNEFA
* Interfaz con estética institucional (*Azul Navy UNEFA*, *Dorado Institucional* y sutil *Glassmorphism*).
* **Modo Oscuro con Alto Contraste:** Tipografía nítida en blanco (`#ffffff`), tarjetas con bordes legibles (`#334155`), badges contrastados y tablas accesibles. Persistencia automática en `localStorage`.

---

## API Endpoints

Todos los endpoints requieren autenticación de sesión y están limitados a **60 peticiones/minuto por IP**.

| Método | Endpoint | Descripción | Roles |
|--------|----------|-------------|-------|
| GET | `/controlador/api/buscar_estudiante.php?q=` | Búsqueda de estudiantes | 1,2,4,7 |
| GET | `/controlador/api/buscar_asignaturas.php?plan_id=` | Asignaturas de un plan | Todos |
| GET | `/controlador/api/buscar_referencia.php?ref=` | Verificar referencia de pago | 1,2,4,7 |
| GET | `/controlador/api/listar_documentos.php?uid=` | Documentos de un aspirante | Propietario + 1,2,4,7 |
| GET | `/controlador/reportes.php?tipo=csv_usuarios` | Exportar usuarios CSV | Solo Admin (1) |
| GET | `/controlador/reportes.php?tipo=csv_secciones` | Exportar secciones CSV | Admin + Coordinador (1,2) |

**Respuesta de rate limit:**
```json
{
  "error": "Demasiadas solicitudes. Por favor espere antes de intentarlo de nuevo.",
  "retry_after": 60
}
```

---

## Seguridad

| Medida | Implementación |
|--------|---------------|
| Contraseñas | `password_hash()` con `PASSWORD_BCRYPT` |
| Sesiones | HttpOnly · SameSite=Lax · Secure (en HTTPS) |
| CSRF | Token por sesión en todos los formularios POST |
| XSS | `h()` = `htmlspecialchars()` en todas las salidas |
| SQL Injection | PDO con prepared statements en todo el código |
| Rate Limiting | 60 req/min por IP en endpoints de API |
| Headers HTTP | `X-Frame-Options`, `X-Content-Type-Options`, `Content-Security-Policy` |
| Archivos sensibles | `logs/` y `uploads/rate/` bloqueados con `.htaccess` |
| Credenciales | Variables de entorno vía `.env` |

---

## Despliegue a Producción

Consulta el **[Checklist de Producción](docs/DEPLOYMENT_CHECKLIST.md)** para los pasos completos.

### Puntos críticos antes de desplegar:

1. `APP_ENV=production` y `APP_DEBUG=false` en `.env`
2. Verificar que `error_reporting(0)` esté activo o configurado en `php.ini`
3. Habilitar HTTPS y actualizar `BASE_URL` en `.env`
4. Permisos restrictivos en `uploads/` (no ejecutable, solo lectura/escritura)
5. Backup de BD antes de migrar

---

## Contribución

Este proyecto es de uso institucional exclusivo para la UNEFA.  
Para reportar issues o proponer mejoras, comunicarse con el equipo de desarrollo de la Coordinación de Postgrado.

---

*Desarrollado para el Vicerrectorado de Investigación, Postgrado y Recreación — UNEFA, Venezuela.*
