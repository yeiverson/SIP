# API REST (AJAX)

Endpoints JSON ubicados en `controlador/api/`.

---

## `buscar_estudiante.php`

**Método:** GET  
**Parámetro:** `q` (texto de búsqueda)  
**Ejemplo:** `buscar_estudiante.php?q=123`

Busca usuarios activos por:
- `numero_documento` (LIKE)
- `nombres` (LIKE)
- `apellidos` (LIKE)
- `email` (LIKE)

**Respuesta:**
```json
[
  {
    "id": 25,
    "tipo_cedula": "V",
    "numero_documento": "12345678",
    "nombres": "Juan",
    "apellidos": "Pérez",
    "email": "juan@correo.com"
  }
]
```

Límite: 20 resultados. Ordenado por `apellidos ASC`.

---

## `buscar_referencia.php`

**Método:** GET  
**Parámetro:** `ref` (número de referencia)  
**Ejemplo:** `buscar_referencia.php?ref=REF001`

Busca un pago por referencia e incluye datos del usuario y su inscripción.

**Respuesta:**
```json
{
  "encontrado": true,
  "pago": {
    "id": 1,
    "usuario_id": 25,
    "monto": "150.00",
    "banco": "Banco de Venezuela",
    "fecha_pago": "2026-06-01",
    "estatus": "Pendiente"
  },
  "usuario": {
    "nombres": "Juan",
    "apellidos": "Pérez",
    "tipo_cedula": "V",
    "numero_documento": "12345678"
  }
}
```

---

## `buscar_asignaturas.php`

**Método:** GET  
**Parámetro:** `plan_id` (ID del plan de estudios)  
**Ejemplo:** `buscar_asignaturas.php?plan_id=1`

Retorna las asignaturas asociadas a un plan, ordenadas por semestre.

**Respuesta:**
```json
[
  {
    "codigo": "MAT101",
    "nombre": "Matemáticas I",
    "uc": 4,
    "semestre": 1,
    "obligatoria": true
  }
]
```

---

## `listar_documentos.php`

**Método:** GET  
**Parámetro:** `uid` (ID del usuario/aspirante)  
**Ejemplo:** `listar_documentos.php?uid=30`

Lista los documentos subidos por un aspirante.

**Respuesta:**
```json
[
  {
    "id": 1,
    "tipo": "Cedula",
    "archivo_nombre": "cedula_juan.pdf",
    "verificado": true,
    "observaciones": null,
    "created_at": "2026-06-10 14:30:00"
  }
]
```
