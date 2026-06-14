# SIP-Postgrado UNEFA

**Sistema Integral de Postgrado** de la Universidad Nacional Experimental de la Fuerza Armada (UNEFA).

Sistema web de gestión académica con 7 roles, diseñado para administrar postgrados, desde la preinscripción del aspirante hasta la emisión de actas de notas definitivas, incluyendo control de sedes, planes de estudio, secciones, horarios, pagos y créditos resguardados.

---

## Tecnologías

| Componente | Tecnología |
|------------|------------|
| Frontend | HTML5, CSS3, JavaScript (vanilla) |
| Backend | PHP 7.4+ / 8.x |
| Base de datos | PostgreSQL 15+ |
| Estilos | Glassmorphism, Montserrat, animaciones CSS/JS |
| Autenticación | bcrypt, sesiones PHP, token CSRF |
| Servidor | Apache (XAMPP) |

---

## Roles del Sistema (7)

| ID | Rol | Dashboard | Descripción |
|----|-----|-----------|-------------|
| 1 | Administrador | `vistas/admin/dashboard.php` | Gestión de sedes, solicitudes docentes, llaves digitales, auditoría, créditos, baremo |
| 2 | Coordinador | `vistas/coordinador/dashboard.php` | Planificación de oferta académica, solicitud de docentes |
| 3 | Docente | `vistas/docente/dashboard.php` | Carga académica, actas de notas (0-20), inasistencias |
| 4 | Secretaría | `vistas/secretaria/dashboard.php` | Admisiones, taquilla virtual, maestro estudiantes, visor documentos |
| 5 | Aspirante | `vistas/aspirante/dashboard.php` | Postulación, baremo, carga de documentos PDF |
| 6 | Estudiante | `vistas/estudiante/dashboard.php` | Inscripción interactiva, horario, notas |
| 7 | Director | `vistas/director/dashboard.php` | Planes de estudio, control de fases multi-sede |

---

## Estructura del Proyecto

```
SIP/
├── index.php                     # Login principal
├── registro.php                  # Registro de aspirantes
├── llenado_de_perfil.php         # Completar perfil post-registro
├── procesar_registro.php         # AJAX: registro de usuarios
├── procesar_login.php            # Procesa autenticación
├── procesar_baremo.php           # Guarda respuestas del baremo
├── procesar_perfil.php           # Actualiza perfil de usuario
├── procesar.php                  # Validadores compartidos
├── logout.php                    # Cierre de sesión
│
├── config/
│   ├── database.php              # Conexión PDO a PostgreSQL
│   └── conexion.php              # Redirección a database.php
│
├── includes/
│   ├── auth_check.php            # check_auth(), check_rol(), redirigir_por_rol()
│   ├── functions.php             # Helpers: h(), alertas(), fechas
│   ├── logs.php                  # registrar_log() para auditoría
│   ├── template_header.php       # Header HTML con CSS/JS
│   ├── template_footer.php       # Footer con partículas y animaciones JS
│   ├── queries_usuarios.php      # Consultas de usuarios
│   └── queries_baremo.php        # Consultas del baremo
│
├── controlador/
│   ├── procesar_login.php        # Login con V/E/P
│   ├── cerrar_sesion.php         # Logout alternativo
│   ├── descargar_documento.php   # Descarga con control de acceso
│   └── api/
│       ├── buscar_estudiante.php  # Buscar estudiantes (JSON)
│       ├── buscar_referencia.php  # Buscar referencia de pago (JSON)
│       ├── buscar_asignaturas.php # Asignaturas por plan (JSON)
│       └── listar_documentos.php  # Documentos del aspirante (JSON)
│
├── vistas/
│   ├── admin/dashboard.php       # Panel Administrador
│   ├── coordinador/
│   │   ├── dashboard.php         # Panel Coordinador
│   │   └── crear_seccion.php     # Crear secciones
│   ├── docente/dashboard.php     # Panel Docente
│   ├── secretaria/dashboard.php  # Panel Secretaría
│   ├── aspirante/dashboard.php   # Panel Aspirante
│   ├── estudiante/dashboard.php  # Panel Estudiante
│   └── director/
│       ├── dashboard.php         # Panel Director
│       ├── planes.php            # Gestión de planes
│       └── fases.php             # Control de fases
│
├── css/
│   ├── tu_estilo.css             # Tema principal (glassmorphism)
│   ├── dashboard.css             # Animaciones e interactividad
│   ├── style-inicio.css          # Estilo del login
│   ├── style-registro.css        # Estilo del registro
│   └── style-dashboard.css       # Estilo legacy del dashboard
│
├── js/
│   └── multi-step-form.js        # Validación del formulario multi-paso
│
├── database/
│   ├── migracion_completa.sql    # Migración completa del esquema
│   ├── adaptar_existente.sql     # Adaptación de BD legacy
│   ├── backup_completo.sql       # Backup completo con datos
│   └── postgrado.sql             # Esquema completo con datos iniciales
│
├── uploads/
│   ├── documentos/               # PDFs de aspirantes
│   └── temp/                     # Archivos temporales
│
├── imagenes/
│   ├── FACHADA AZULADA.png       # Fondo de pantalla principal
│   ├── LOGO-1-1.png              # Logo UNEFA
│   ├── gob.png                   # Logo gobierno
│   ├── 200.png                   # Logo 200 Batalla
│   └── ...                       # Otros assets
│
└── .gitignore
```

---

## Instalación

### 1. Requisitos

- XAMPP (Apache + PHP 8+)
- PostgreSQL 15+
- Git

### 2. Clonar repositorio

```bash
git clone https://github.com/yeiverson/SIP.git
cd SIP
```

### 3. Configurar base de datos

```bash
createdb -U postgres unefa_postgrados
psql -U postgres -d unefa_postgrados -f database/backup_completo.sql
```

### 4. Configurar conexión

Editar `config/database.php`:

```php
$db_host     = "localhost";
$db_port     = "5432";
$db_name     = "unefa_postgrados";
$db_user     = "postgres";
$db_password = "tu_contraseña";
```

### 5. Usuarios de prueba

| Usuario | Documento | Tipo | Contraseña | Rol |
|---------|-----------|------|------------|-----|
| Admin | 1 | V | `password` | Administrador |
| Coordinador | 87654321 | V | `password` | Coordinador |
| Docente | 11223344 | V | `password` | Docente |
| Secretaría | 55667788 | V | `password` | Secretaría |
| Aspirante | 99887766 | V | `password` | Aspirante |
| Estudiante | 44332211 | V | `password` | Estudiante |
| Director | 22334455 | V | `password` | Director |
| Pasaporte | FR98765432 | P | `password` | Aspirante |

---

## Diseño Visual

- **Fuente:** Montserrat (Google Fonts)
- **Color principal:** `#001a57` (UNEFA Blue)
- **Fondo:** `FACHADA AZULADA.png` con overlay oscuro
- **Estilo:** Glassmorphism (`backdrop-filter: blur()`) en tarjetas y paneles
- **Animaciones:** Partículas flotantes (canvas), scroll-reveal (IntersectionObserver), entrada escalonada de filas, modales con scale + slideUp, toasts animados

---

## Licencia

SIP-Postgrado UNEFA — Desarrollo interno.
