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
| Planificar Oferta | Crear secciones, asignar horarios, aulas y cupos |
| Solicitar Docente | Solicitar nuevos profesores a administración |

---

## Docente (ID: 3)

**Dashboard:** `vistas/docente/dashboard.php`

| Módulo | Acciones |
|--------|----------|
| Mis Asignaturas | Ver secciones asignadas, número de inscritos, aulas y horarios |
| Mi Horario de Clases | Grilla interactiva semanal (Lunes-Sábado), cálculo de horas académicas, bloques y botón de impresión |
| Actas de Notas | Registrar notas (0-20), marcar inasistencias (N/S), veredicto automático (Aprobado/Reprobado) |
| Estado de Acta | Guardar Borrador (editable) → Cierre Definitivo (inmutable) |
| Reportes | Generación e impresión de Actas Oficiales de Evaluación |

**Reglas:**
- Notas en escala 0-20, enteras o con medio punto (ej: 15.5)
- Una vez cerrada el acta (Definitiva), no se puede modificar
- Solo el Administrador puede reabrir un acta definitiva mediante solicitud de gobernanza
- Los horarios asignados aparecen tanto en la cabecera de la asignatura como en la vista de cronograma

---

## Secretaría (ID: 4)

**Dashboard:** `vistas/secretaria/dashboard.php`

| Módulo | Acciones |
|--------|----------|
| Admisiones | Revisar postulaciones, verificar documentos |
| Taquilla Virtual | Registrar pagos, validar referencias bancarias |
| Maestro Estudiantes | Buscar y gestionar estudiantes |

**Visor de Documentos:** Modal con lista de documentos subidos por el aspirante y estado de verificación (✅/⬜).

---

## Aspirante (ID: 5)

**Dashboard:** `vistas/aspirante/dashboard.php`

| Módulo | Acciones |
|--------|----------|
| Postulación | Completar datos personales y académicos |
| Baremo | Responder preguntas de ponderación del baremo institucional |
| Documentos | Subir PDF de: Cédula, Pasaporte, Título, Notas, Currículum |
| Estado | Ver semáforo de revisión |

**Flujo:**
1. Registro → 2. Completar perfil → 3. Responder baremo → 4. Subir documentos → 5. Enviar postulación → 6. Secretaría revisa

---

## Estudiante (ID: 6)

**Dashboard:** `vistas/estudiante/dashboard.php`

| Módulo | Acciones |
|--------|----------|
| Mi Expediente | Vista general de materias inscritas con horarios por materia y docente |
| Mi Horario de Clases | Cronograma semanal interactivo (Lunes a Sábado), cómputo de horas académicas, días de asistencia y reporte imprimible |
| Inscripción de Materias | Selección de oferta académica con validación automática de choques horarios y cupos |
| Registro de Pagos | Reportar transferencias / Pago Móvil para formalización en Taquilla |
| Historial Académico | Kardex oficial con promedio acumulado, UC aprobadas y reprobadas |
| Constancias | Descarga de Constancia de Estudio y Comprobante Oficial de Inscripción |

**Reglas:**
- No puede inscribirse en secciones con choque horario
- Una vez seleccionada la materia, pasa a estatus 'Por Cancelar' hasta validación de pago en Secretaría ('Formalizada')
- Los créditos resguardados viajan entre semestres automáticamente

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
