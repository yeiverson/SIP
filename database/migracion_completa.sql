-- ============================================================
-- MIGRACIÓN COMPLETA SIP-POSTGRADO (7 ROLES)
-- ============================================================

-- 1. EXTENSIONES
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 2. TABLAS BASE DEL SISTEMA

-- Roles del sistema (1-7)
CREATE TABLE IF NOT EXISTS roles (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT
);

INSERT INTO roles (id, nombre, descripcion) VALUES
    (1, 'Administrador', 'Administrador del sistema - infraestructura y soporte'),
    (2, 'Coordinador', 'Coordinador de programa - planificación académica'),
    (3, 'Docente', 'Docente / Profesor - evaluación académica'),
    (4, 'Secretaria', 'Secretaría de Control de Estudios - admisiones y pagos'),
    (5, 'Aspirante', 'Aspirante / Postulante - pre-inscripción'),
    (6, 'Estudiante', 'Estudiante regular - cursante activo'),
    (7, 'Director', 'Director de postgrado - control de fases y planes')
ON CONFLICT (id) DO NOTHING;

-- Sedes / Núcleos
CREATE TABLE IF NOT EXISTS sedes (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    ubicacion VARCHAR(255),
    codigo VARCHAR(20) UNIQUE,
    fase_actual INTEGER DEFAULT 1 CHECK (fase_actual IN (1,2)),
    activa BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO sedes (nombre, ubicacion, codigo) VALUES
    ('Caracas', 'Distrito Capital', 'CCS'),
    ('Maracay', 'Estado Aragua', 'MAR'),
    ('San Tomé', 'Estado Anzoátegui', 'STO')
ON CONFLICT (codigo) DO NOTHING;

-- 3. EXTENSIÓN DE LA TABLA usuarios EXISTENTE
-- Agregamos columnas necesarias si no existen
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='usuarios' AND column_name='rol_id') THEN
        ALTER TABLE usuarios ADD COLUMN rol_id INTEGER DEFAULT 5 REFERENCES roles(id);
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='usuarios' AND column_name='sede_id') THEN
        ALTER TABLE usuarios ADD COLUMN sede_id INTEGER REFERENCES sedes(id);
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='usuarios' AND column_name='estatus') THEN
        ALTER TABLE usuarios ADD COLUMN estatus VARCHAR(20) DEFAULT 'Activo' CHECK (estatus IN ('Activo','Inactivo','Bloqueado'));
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='usuarios' AND column_name='sexo') THEN
        ALTER TABLE usuarios ADD COLUMN sexo VARCHAR(10);
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='usuarios' AND column_name='fecha_nacimiento') THEN
        ALTER TABLE usuarios ADD COLUMN fecha_nacimiento DATE;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='usuarios' AND column_name='nacionalidad') THEN
        ALTER TABLE usuarios ADD COLUMN nacionalidad VARCHAR(100) DEFAULT 'Venezuela';
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='usuarios' AND column_name='numero_documento') THEN
        ALTER TABLE usuarios ADD COLUMN numero_documento VARCHAR(20);
    END IF;
END $$;

-- Actualizar numero_documento desde cedula existente
UPDATE usuarios SET numero_documento = cedula::varchar WHERE numero_documento IS NULL AND cedula IS NOT NULL;

-- 4. NUEVAS TABLAS ACADÉMICAS

-- Planes de Estudio
CREATE TABLE IF NOT EXISTS plan_estudios (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    tipo VARCHAR(50) CHECK (tipo IN ('Especializacion','Maestria','Doctorado')),
    codigo VARCHAR(20) UNIQUE,
    activo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Asignaturas (ya referenciada en código existente)
CREATE TABLE IF NOT EXISTS asignaturas (
    codigo VARCHAR(20) PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    uc INTEGER NOT NULL CHECK (uc > 0),
    horas_teoricas INTEGER DEFAULT 0,
    horas_practicas INTEGER DEFAULT 0,
    activa BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Relación Plan <-> Asignatura
CREATE TABLE IF NOT EXISTS plan_asignaturas (
    id SERIAL PRIMARY KEY,
    plan_id INTEGER REFERENCES plan_estudios(id) ON DELETE CASCADE,
    asignatura_codigo VARCHAR(20) REFERENCES asignaturas(codigo) ON DELETE CASCADE,
    semestre INTEGER,
    obligatoria BOOLEAN DEFAULT true,
    UNIQUE(plan_id, asignatura_codigo)
);

-- Planes por sede (multi-sede)
CREATE TABLE IF NOT EXISTS plan_sede (
    id SERIAL PRIMARY KEY,
    plan_id INTEGER REFERENCES plan_estudios(id) ON DELETE CASCADE,
    sede_id INTEGER REFERENCES sedes(id) ON DELETE CASCADE,
    UNIQUE(plan_id, sede_id)
);

-- Secciones
CREATE SEQUENCE IF NOT EXISTS secciones_id_seq;
CREATE TABLE IF NOT EXISTS secciones (
    id INTEGER DEFAULT nextval('secciones_id_seq') PRIMARY KEY,
    plan_id INTEGER REFERENCES plan_estudios(id),
    asignatura_codigo VARCHAR(20) REFERENCES asignaturas(codigo),
    seccion VARCHAR(10) NOT NULL,
    profesor_id INTEGER REFERENCES usuarios(id),
    sede_id INTEGER REFERENCES sedes(id),
    cupo_maximo INTEGER DEFAULT 25,
    cupo_actual INTEGER DEFAULT 0,
    aula VARCHAR(50),
    periodo VARCHAR(20),
    activa BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Horarios
CREATE TABLE IF NOT EXISTS horarios (
    id SERIAL PRIMARY KEY,
    seccion_id INTEGER REFERENCES secciones(id) ON DELETE CASCADE,
    dia_semana INTEGER CHECK (dia_semana BETWEEN 1 AND 7), -- 1=Lunes..7=Domingo
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    UNIQUE(seccion_id, dia_semana, hora_inicio)
);

-- 5. TABLAS DE PROCESOS

-- Solicitudes urgentes de docentes
CREATE TABLE IF NOT EXISTS solicitudes_docentes (
    id SERIAL PRIMARY KEY,
    coordinador_id INTEGER REFERENCES usuarios(id),
    sede_id INTEGER REFERENCES sedes(id),
    tipo_documento VARCHAR(5) CHECK (tipo_documento IN ('V','E','P')),
    numero_documento VARCHAR(20) NOT NULL,
    nombres VARCHAR(200) NOT NULL,
    apellidos VARCHAR(200) NOT NULL,
    email VARCHAR(255) NOT NULL,
    nacionalidad VARCHAR(100),
    estatus VARCHAR(20) DEFAULT 'Pendiente' CHECK (estatus IN ('Pendiente','Aprobado','Rechazado')),
    admin_id INTEGER REFERENCES usuarios(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resuelto_at TIMESTAMP
);

-- Inscripciones
CREATE TABLE IF NOT EXISTS inscripciones (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER REFERENCES usuarios(id),
    seccion_id INTEGER REFERENCES secciones(id),
    estatus VARCHAR(20) DEFAULT 'Por Cancelar' CHECK (estatus IN ('Por Cancelar','Formalizada','Eliminada','Resguardada')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(usuario_id, seccion_id)
);

-- Pagos
CREATE TABLE IF NOT EXISTS pagos (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER REFERENCES usuarios(id),
    inscripcion_id INTEGER REFERENCES inscripciones(id),
    banco VARCHAR(100),
    referencia VARCHAR(100) UNIQUE,
    monto DECIMAL(12,2) NOT NULL,
    fecha_pago DATE,
    secretaria_id INTEGER REFERENCES usuarios(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Créditos resguardados
CREATE TABLE IF NOT EXISTS creditos_resguardados (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER REFERENCES usuarios(id),
    sede_origen_id INTEGER REFERENCES sedes(id),
    sede_destino_id INTEGER REFERENCES sedes(id),
    uc_resguardadas INTEGER NOT NULL CHECK (uc_resguardadas > 0),
    monto_resguardado DECIMAL(12,2),
    motivo VARCHAR(50) CHECK (motivo IN ('Eliminacion','Traslado','Reembolso')),
    estatus VARCHAR(20) DEFAULT 'Activo' CHECK (estatus IN ('Activo','Aplicado','Reversado')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    aplicado_at TIMESTAMP
);

-- Actas de notas
CREATE TABLE IF NOT EXISTS actas_notas (
    id SERIAL PRIMARY KEY,
    seccion_id INTEGER REFERENCES secciones(id),
    usuario_id INTEGER REFERENCES usuarios(id),
    nota INTEGER CHECK (nota BETWEEN 0 AND 20),
    inasistencia BOOLEAN DEFAULT false,
    estatus VARCHAR(20) DEFAULT 'Borrador' CHECK (estatus IN ('Borrador','Definitiva')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(seccion_id, usuario_id)
);

-- Historial de cambios en actas (log)
CREATE TABLE IF NOT EXISTS actas_log (
    id SERIAL PRIMARY KEY,
    acta_id INTEGER REFERENCES actas_notas(id),
    usuario_id INTEGER REFERENCES usuarios(id),
    nota_anterior INTEGER,
    nota_nueva INTEGER,
    accion VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6. TABLAS DEL ADMINISTRADOR

-- Llaves digitales (permisos especiales)
CREATE TABLE IF NOT EXISTS llaves_digitales (
    id SERIAL PRIMARY KEY,
    tipo VARCHAR(50) CHECK (tipo IN ('ModificarPlan','ReabrirActa','EliminarMateria')),
    solicitante_id INTEGER REFERENCES usuarios(id),
    admin_id INTEGER REFERENCES usuarios(id),
    destino_id INTEGER, -- ID del recurso afectado
    estatus VARCHAR(20) DEFAULT 'Activa' CHECK (estatus IN ('Activa','Expirada','Usada')),
    validez_horas INTEGER DEFAULT 24,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expira_at TIMESTAMP,
    used_at TIMESTAMP
);

-- Logs de auditoría
CREATE TABLE IF NOT EXISTS logs_auditoria (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER REFERENCES usuarios(id),
    accion VARCHAR(100) NOT NULL,
    entidad VARCHAR(50),
    entidad_id INTEGER,
    detalle TEXT,
    direccion_ip VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 7. TABLAS DEL ASPIRANTE

-- Documentos del aspirante
CREATE TABLE IF NOT EXISTS aspirante_documentos (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER REFERENCES usuarios(id),
    tipo VARCHAR(50) CHECK (tipo IN ('Cedula','Pasaporte','Titulo','Notas','Curriculum')),
    archivo_ruta VARCHAR(500),
    archivo_nombre VARCHAR(255),
    verificado BOOLEAN DEFAULT false,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Estados del aspirante
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS estado_aspirante VARCHAR(30) DEFAULT 'En Revision Digital'
    CHECK (estado_aspirante IN ('En Revision Digital','Con Observaciones','Admitido'));

-- 8. ÍNDICES
CREATE INDEX IF NOT EXISTS idx_usuarios_rol ON usuarios(rol_id);
CREATE INDEX IF NOT EXISTS idx_usuarios_sede ON usuarios(sede_id);
CREATE INDEX IF NOT EXISTS idx_usuarios_documento ON usuarios(tipo_cedula, numero_documento);
CREATE INDEX IF NOT EXISTS idx_secciones_profesor ON secciones(profesor_id);
CREATE INDEX IF NOT EXISTS idx_secciones_sede ON secciones(sede_id);
CREATE INDEX IF NOT EXISTS idx_inscripciones_usuario ON inscripciones(usuario_id);
CREATE INDEX IF NOT EXISTS idx_inscripciones_seccion ON inscripciones(seccion_id);
CREATE INDEX IF NOT EXISTS idx_inscripciones_estatus ON inscripciones(estatus);
CREATE INDEX IF NOT EXISTS idx_pagos_usuario ON pagos(usuario_id);
CREATE INDEX IF NOT EXISTS idx_actas_notas_seccion ON actas_notas(seccion_id);
CREATE INDEX IF NOT EXISTS idx_actas_notas_usuario ON actas_notas(usuario_id);
CREATE INDEX IF NOT EXISTS idx_logs_usuario ON logs_auditoria(usuario_id);
CREATE INDEX IF NOT EXISTS idx_logs_created ON logs_auditoria(created_at);
CREATE INDEX IF NOT EXISTS idx_solicitudes_estatus ON solicitudes_docentes(estatus);
CREATE INDEX IF NOT EXISTS idx_creditos_usuario ON creditos_resguardados(usuario_id);

-- 9. FUNCIÓN PARA LOG AUTOMÁTICO
CREATE OR REPLACE FUNCTION registrar_log()
RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO logs_auditoria (usuario_id, accion, entidad, entidad_id, detalle)
    VALUES (
        COALESCE(current_setting('app.usuario_id', true)::integer, 0),
        TG_ARGV[0],
        TG_TABLE_NAME,
        NEW.id,
        row_to_json(NEW)::text
    );
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- 10. DATOS INICIALES

-- Asignaturas de ejemplo
INSERT INTO asignaturas (codigo, nombre, uc, horas_teoricas, horas_practicas) VALUES
    ('MET551', 'Metodología de la Investigación', 3, 2, 1),
    ('GER401', 'Gerencia Estratégica', 3, 2, 1),
    ('EST301', 'Estadística Aplicada', 3, 2, 1),
    ('DER201', 'Derecho Administrativo', 2, 2, 0),
    ('INF601', 'Gerencia de la Informática', 3, 2, 1),
    ('FIN501', 'Finanzas Públicas', 3, 2, 1)
ON CONFLICT (codigo) DO NOTHING;

-- Plan de estudios ejemplo
INSERT INTO plan_estudios (nombre, tipo, codigo) VALUES
    ('Maestría en Gerencia Logística', 'Maestria', 'MGL-2026'),
    ('Maestría en Educación Superior', 'Maestria', 'MES-2026')
ON CONFLICT (codigo) DO NOTHING;

-- Vincular asignaturas al plan MGL
INSERT INTO plan_asignaturas (plan_id, asignatura_codigo, semestre)
SELECT p.id, a.codigo, 1
FROM plan_estudios p, asignaturas a
WHERE p.codigo = 'MGL-2026'
  AND a.codigo IN ('MET551','GER401','EST301','DER201','INF601','FIN501')
ON CONFLICT DO NOTHING;

-- Planes por sede
INSERT INTO plan_sede (plan_id, sede_id)
SELECT p.id, s.id
FROM plan_estudios p, sedes s
WHERE p.codigo = 'MGL-2026' AND s.codigo IN ('CCS','MAR','STO')
ON CONFLICT DO NOTHING;

-- Usuario administrador por defecto (password: Admin2026!)
INSERT INTO usuarios (tipo_cedula, cedula, numero_documento, nombres, apellidos, email, password, rol_id, estatus)
VALUES ('V', 1, '1', 'Admin', 'Sistema', 'admin@sip.unefa.edu.ve',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Activo')
ON CONFLICT (email) DO NOTHING;
