-- ============================================================
-- ADAPTACIÓN de la BD existente al nuevo esquema SIP 7 Roles
-- Ejecutar DESPUÉS de migracion_completa.sql
-- ============================================================

-- 1. SEDES: agregar columnas faltantes
ALTER TABLE sedes ADD COLUMN IF NOT EXISTS ubicacion VARCHAR(255);
ALTER TABLE sedes ADD COLUMN IF NOT EXISTS codigo VARCHAR(20) UNIQUE;
ALTER TABLE sedes ADD COLUMN IF NOT EXISTS activa BOOLEAN DEFAULT true;
ALTER TABLE sedes ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE sedes ADD COLUMN IF NOT EXISTS fase_actual INTEGER DEFAULT 1 CHECK (fase_actual IN (1,2));
ALTER TABLE sedes ALTER COLUMN nombre TYPE VARCHAR(150);

-- 2. ASIGNATURAS: renombrar columnas para compatibilidad con nuevo código
ALTER TABLE asignaturas RENAME COLUMN nombre_materia TO nombre;
ALTER TABLE asignaturas RENAME COLUMN horas_t TO horas_teoricas;
ALTER TABLE asignaturas RENAME COLUMN horas_p TO horas_practicas;
ALTER TABLE asignaturas RENAME COLUMN unidades_credito TO uc;
ALTER TABLE asignaturas DROP COLUMN IF EXISTS horas_l;
ALTER TABLE asignaturas ADD COLUMN IF NOT EXISTS activa BOOLEAN DEFAULT true;
ALTER TABLE asignaturas ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- 3. USUARIOS: renombrar columnas
ALTER TABLE usuarios RENAME COLUMN nombre TO nombres;
ALTER TABLE usuarios RENAME COLUMN apellido TO apellidos;
ALTER TABLE usuarios RENAME COLUMN correo TO email;
ALTER TABLE usuarios RENAME COLUMN tipo_documento TO tipo_cedula;
ALTER TABLE usuarios ALTER COLUMN tipo_cedula TYPE VARCHAR(5) USING tipo_cedula::varchar;
ALTER TABLE usuarios ALTER COLUMN cedula TYPE VARCHAR(20);
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS telefono VARCHAR(20);
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS direccion TEXT;

-- Asegurar numero_documento poblado
UPDATE usuarios SET numero_documento = cedula WHERE numero_documento IS NULL AND cedula IS NOT NULL;

-- Agregar restricción de check para tipo_cedula permitiendo V/E/P
ALTER TABLE usuarios DROP CONSTRAINT IF EXISTS usuarios_tipo_cedula_check;
ALTER TABLE usuarios ADD CONSTRAINT usuarios_tipo_cedula_check CHECK (tipo_cedula IN ('V','E','P'));

-- 4. PLAN_ESTUDIOS: agregar columna tipo si no existe
ALTER TABLE plan_estudios ADD COLUMN IF NOT EXISTS tipo VARCHAR(50) CHECK (tipo IN ('Especializacion','Maestria','Doctorado'));
ALTER TABLE plan_estudios ADD COLUMN IF NOT EXISTS activo BOOLEAN DEFAULT true;
ALTER TABLE plan_estudios ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE plan_estudios ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- 5. SECCIONES: asegurar columnas
ALTER TABLE secciones ADD COLUMN IF NOT EXISTS sede_id INTEGER REFERENCES sedes(id);
ALTER TABLE secciones ADD COLUMN IF NOT EXISTS cupo_actual INTEGER DEFAULT 0;
ALTER TABLE secciones ADD COLUMN IF NOT EXISTS activa BOOLEAN DEFAULT true;
ALTER TABLE secciones ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE secciones ADD COLUMN IF NOT EXISTS periodo VARCHAR(20);

-- 6. INSCRIPCIONES: renombrar FK si es necesario
ALTER TABLE inscripciones RENAME COLUMN estudiante_id TO usuario_id;
ALTER TABLE inscripciones ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- 7. Actualizar roles de usuarios existentes (mapeo de texto a ID)
UPDATE usuarios SET rol_id = 1 WHERE UPPER(rol) = 'ADMINISTRADOR' AND rol_id IS NULL;
UPDATE usuarios SET rol_id = 2 WHERE UPPER(rol) = 'COORDINADOR' AND rol_id IS NULL;
UPDATE usuarios SET rol_id = 3 WHERE UPPER(rol) = 'DOCENTE' AND rol_id IS NULL;
UPDATE usuarios SET rol_id = 4 WHERE UPPER(rol) = 'SECRETARIA' AND rol_id IS NULL;
UPDATE usuarios SET rol_id = 5 WHERE UPPER(rol) = 'ASPIRANTE' AND rol_id IS NULL;
UPDATE usuarios SET rol_id = 6 WHERE UPPER(rol) = 'ESTUDIANTE' AND rol_id IS NULL;

-- 8. Estado aspirante desde estatus_admision
UPDATE usuarios SET estado_aspirante = 'En Revision Digital' WHERE estado_aspirante IS NULL AND estatus_admision = 'REGISTRADO';
UPDATE usuarios SET estado_aspirante = 'Admitido' WHERE estado_aspirante IS NULL AND estatus_admision = 'APROBADO';
UPDATE usuarios SET estado_aspirante = 'Con Observaciones' WHERE estado_aspirante IS NULL AND estatus_admision = 'RECHAZADO';

-- 9. Sincronizar estatus
UPDATE usuarios SET estatus = 'Activo' WHERE estatus IS NULL AND estatus_admision IN ('REGISTRADO','VALIDADO_DOCS','APROBADO');
UPDATE usuarios SET estatus = 'Inactivo' WHERE estatus IS NULL AND estatus_admision = 'RECHAZADO';

-- 10. Eliminar constraint UNIQUE de cedula que puede causar conflictos con numero_documento
ALTER TABLE usuarios DROP CONSTRAINT IF EXISTS usuarios_cedula_key;
