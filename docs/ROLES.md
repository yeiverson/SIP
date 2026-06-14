# Sistema de Roles

SIP-Postgrado UNEFA implementa 7 roles con acceso granular a módulos específicos.

---

## Administrador (ID: 1)

**Dashboard:** `vistas/admin/dashboard.php`

| Módulo | Acciones |
|--------|----------|
| Sedes/Núcleos | Crear sedes, asignar directores, ver listado |
| Solicitudes Docentes | Aprobar/rechazar solicitudes de coordinadores |
| Llaves Digitales | Reabrir actas definitivas para edición |
| Auditoría | Ver logs inmutables del sistema |
| Créditos Resguardados | Reversión técnica de saldos |
| Baremo | CRUD completo de preguntas del baremo |

**Restricciones:** No puede intervenir en notas, inscripciones o planes de estudio.

---

## Coordinador (ID: 2)

**Dashboard:** `vistas/coordinador/dashboard.php`

| Módulo | Acciones |
|--------|----------|
| Planificar Oferta | Crear secciones, asignar horarios, definir cupos |
| Solicitar Docente | Solicitar nuevos profesores a administración |

---

## Docente (ID: 3)

**Dashboard:** `vistas/docente/dashboard.php`

| Módulo | Acciones |
|--------|----------|
| Carga Académica | Ver secciones asignadas |
| Actas de Notas | Registrar notas (0-20), marcar inasistencias |
| Estado | Borrador (editable) → Definitiva (inmutable) |

**Reglas:**
- Notas en escala 0-20, enteras o con medio punto (ej: 15.5)
- Una vez cerrada el acta (Definitiva), no se puede modificar
- Solo el Admin puede reabrir un acta definitiva

---

## Secretaría (ID: 4)

**Dashboard:** `vistas/secretaria/dashboard.php`

| Módulo | Acciones |
|--------|----------|
| Admisiones | Revisar postulaciones, verificar documentos |
| Taquilla Virtual | Registrar pagos, buscar referencias |
| Maestro Estudiantes | Buscar y gestionar estudiantes |

**Visor de Documentos:** Modal con lista de documentos subidos por el aspirante y estado de verificación (✅/⬜).

---

## Aspirante (ID: 5)

**Dashboard:** `vistas/aspirante/dashboard.php`

| Módulo | Acciones |
|--------|----------|
| Postulación | Completar datos personales y académicos |
| Baremo | Responder 14 preguntas de evaluación (sí/no) |
| Documentos | Subir PDF de: Cédula, Pasaporte, Título, Notas, Currículum |
| Estado | Ver semáforo de revisión |

**Flujo:**
1. Registro → 2. Completar perfil → 3. Responder baremo → 4. Subir documentos → 5. Enviar postulación → 6. Secretaría revisa

---

## Estudiante (ID: 6)

**Dashboard:** `vistas/estudiante/dashboard.php`

| Módulo | Acciones |
|--------|----------|
| Inscripción | Ver secciones disponibles, inscribir materias |
| Mi Horario | Ver horario semanal |
| Mis Notas | Consultar calificaciones |

**Reglas:**
- No puede inscribirse en secciones con choque horario
- Una vez confirmada la inscripción, debe ir a Secretaría para cambios
- Los créditos resguardados viajan entre semestres

---

## Director (ID: 7)

**Dashboard:** `vistas/director/dashboard.php`

| Módulo | Acciones |
|--------|----------|
| Planes de Estudio | Crear y gestionar planes (Especialización/Maestría/Doctorado) |
| Control de Fases | Avanzar sedes entre Fase 1 (planificación) y Fase 2 (inscripciones) |

**Fases:**
- **Fase 1 (Planificación):** Coordinadores crean oferta académica
- **Fase 2 (Inscripciones):** Estudiantes pueden inscribirse
