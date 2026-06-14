# Base de Datos

**Motor:** PostgreSQL 15+  
**Base:** `unefa_postgrados`  
**Puerto:** `5432`

---

## Esquema General

```
roles (1) ──< usuarios >── sedes
                │
          ┌─────┼──────┬──────────┬──────────┐
          │     │      │          │          │
     plan_est.  │  secciones  inscrip.   pagos
          │     │      │          │
     plan_asig. │  horarios    creditos_resg.
          │     │      │
     asignaturas │  actas_notas
                │      │
           baremo_  actas_log
           preguntas │
                │   aspirante_
           respuestas  docs.
                baremo
```

---

## Tablas Principales

### `usuarios`
| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | SERIAL PK | Identificador único |
| `tipo_cedula` | VARCHAR(1) | V, E o P |
| `numero_documento` | VARCHAR(20) | Documento de identidad (alfanumérico para P) |
| `cedula` | INTEGER | Legacy: solo numérico |
| `nombres` | VARCHAR(100) | Nombres del usuario |
| `apellidos` | VARCHAR(100) | Apellidos del usuario |
| `email` | VARCHAR(150) UNIQUE | Correo electrónico |
| `password` | VARCHAR(255) | Hash bcrypt |
| `rol_id` | INTEGER FK → roles | 1-7 |
| `sede_id` | INTEGER FK → sedes | Sede asignada |
| `estatus` | VARCHAR(20) | Activo / Inactivo / Bloqueado |
| `estado_aspirante` | VARCHAR(50) | Estado en el proceso de admisión |
| `telefono` | VARCHAR(20) | Teléfono de contacto |
| `direccion` | TEXT | Dirección fiscal |
| `sexo` | VARCHAR(1) | M / F |
| `fecha_nacimiento` | DATE | Fecha de nacimiento |
| `nacionalidad` | VARCHAR(50) | Nacionalidad |
| `fecha_registro` | TIMESTAMP | Fecha de creación |

### `sedes`
| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | SERIAL PK | Identificador |
| `nombre` | VARCHAR(100) | Nombre de la sede |
| `ubicacion` | VARCHAR(200) | Dirección/ubicación |
| `codigo` | VARCHAR(10) UNIQUE | Código corto |
| `fase_actual` | INTEGER | 1 (planificación) / 2 (inscripciones) |
| `activa` | BOOLEAN | Sede operativa |

### `secciones`
| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | SERIAL PK | Identificador |
| `plan_id` | INTEGER FK → plan_estudios | Plan al que pertenece |
| `asignatura_codigo` | VARCHAR(20) FK → asignaturas | Materia |
| `seccion` | VARCHAR(10) | Código de sección (A, B, Única...) |
| `profesor_id` | INTEGER FK → usuarios | Docente asignado |
| `sede_id` | INTEGER FK → sedes | Sede donde se dicta |
| `cupo_maximo` | INTEGER | Cupo total |
| `cupo_actual` | INTEGER | Cupos ocupados |
| `periodo` | VARCHAR(20) | Período académico |
| `activa` | BOOLEAN | Sección disponible |

### `inscripciones`
| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | SERIAL PK | Identificador |
| `usuario_id` | INTEGER FK → usuarios | Estudiante |
| `seccion_id` | INTEGER FK → secciones | Sección inscrita |
| `estatus` | VARCHAR(20) | Por Cancelar / Formalizada / Eliminada / Resguardada |
| UNIQUE(usuario_id, seccion_id) | Evita doble inscripción |

### `actas_notas`
| Columna | Tipo | Restricciones |
|---------|------|---------------|
| `id` | SERIAL PK | |
| `seccion_id` | INTEGER FK → secciones | |
| `usuario_id` | INTEGER FK → usuarios | Estudiante calificado |
| `nota` | NUMERIC(4,1) | 0.0 — 20.0 |
| `inasistencia` | BOOLEAN | TRUE si no asistió |
| `estatus` | VARCHAR(20) | Borrador / Definitiva |
| UNIQUE(seccion_id, usuario_id) | | Una nota por estudiante por sección |

### `pagos`
| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | SERIAL PK | |
| `usuario_id` | INTEGER FK → usuarios | Pagador |
| `inscripcion_id` | INTEGER FK → inscripciones | Inscripción asociada |
| `banco` | VARCHAR(100) | Banco de origen |
| `referencia` | VARCHAR(50) UNIQUE | Número de referencia |
| `monto` | NUMERIC(12,2) | Monto pagado |
| `fecha_pago` | DATE | Fecha del pago |
| `secretaria_id` | INTEGER FK → usuarios | Secretaria que registró |

### `aspirante_documentos`
| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | SERIAL PK | |
| `usuario_id` | INTEGER FK → usuarios | Aspirante |
| `tipo` | VARCHAR(30) | Cedula / Pasaporte / Titulo / Notas / Curriculum |
| `archivo_ruta` | TEXT | Ruta en el servidor |
| `archivo_nombre` | VARCHAR(255) | Nombre original |
| `verificado` | BOOLEAN | Aprobado por secretaría |
| `observaciones` | TEXT | Notas de revisión |

### `logs_auditoria`
| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | SERIAL PK | |
| `usuario_id` | INTEGER → usuarios | Quién ejecutó la acción |
| `accion` | VARCHAR(50) | Tipo de acción |
| `entidad` | VARCHAR(50) | Tabla afectada |
| `entidad_id` | INTEGER | ID del registro afectado |
| `detalle` | TEXT | Descripción |
| `direccion_ip` | VARCHAR(45) | IP del usuario |
| `created_at` | TIMESTAMP | Fecha/hora |

---

## Tablas del Baremo

### `baremo_preguntas`
14 preguntas en 3 categorías: **Académico** (3), **Investigación** (7), **Otros** (4).

| ID | Categoría | Pregunta |
|----|-----------|----------|
| 1 | Académico | Participación en eventos científicos... |
| 2 | Académico | Participación como jurado o tutor... |
| 3 | Académico | Disposición a participar en actividad académicas... |
| 4-10 | Investigación | Tema de interés, vinculación profesional, afiliación a redes... |
| 11 | Investigación | La investigación satisface fines personales... |
| 12-14 | Otros | Acceso a equipos tecnológicos, disponibilidad financiera... |

### `respuestas_baremo`
| Columna | Descripción |
|---------|-------------|
| `id_aspirante` | FK → usuarios |
| `id_pregunta` | FK → baremo_preguntas |
| `respuesta` | 'si' o 'no' |

---

## Backup

```bash
pg_dump -U postgres -d unefa_postgrados --clean --if-exists --no-owner --no-acl > database/backup_completo.sql
```

## Restauración

```bash
psql -U postgres -d unefa_postgrados -f database/backup_completo.sql
```
