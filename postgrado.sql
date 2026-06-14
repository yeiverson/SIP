-- PostgreSQL database dump actualizado — SIP-Postgrado 7 Roles
SET statement_timeout = 0; SET lock_timeout = 0; SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8'; SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false; SET xmloption = content;
SET client_min_messages = warning; SET row_security = off;
SET default_tablespace = ''; SET default_table_access_method = heap;

-- ============================================================
-- 1. EXTENSIONES
-- ============================================================
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- ============================================================
-- 2. TABLAS BASE
-- ============================================================
CREATE TABLE IF NOT EXISTS public.roles (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT
);
INSERT INTO public.roles (id, nombre, descripcion) VALUES
    (1, 'Administrador', 'Administrador del sistema'),
    (2, 'Coordinador', 'Coordinador de programa'),
    (3, 'Docente', 'Docente / Profesor'),
    (4, 'Secretaria', 'Secretaría de Control de Estudios'),
    (5, 'Aspirante', 'Aspirante / Postulante'),
    (6, 'Estudiante', 'Estudiante regular'),
    (7, 'Director', 'Director de postgrado')
ON CONFLICT (id) DO NOTHING;

CREATE TABLE IF NOT EXISTS public.sedes (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    ubicacion VARCHAR(255),
    codigo VARCHAR(20) UNIQUE,
    fase_actual INTEGER DEFAULT 1 CHECK (fase_actual IN (1,2)),
    activa BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
INSERT INTO public.sedes (nombre, ubicacion, codigo) VALUES
    ('Caracas', 'Distrito Capital', 'CCS'),
    ('Maracay', 'Estado Aragua', 'MAR'),
    ('San Tomé', 'Estado Anzoátegui', 'STO')
ON CONFLICT (codigo) DO NOTHING;

-- ============================================================
-- 3. TABLA usuarios (extendida)
-- ============================================================
CREATE TABLE IF NOT EXISTS public.usuarios (
    id SERIAL PRIMARY KEY,
    tipo_cedula VARCHAR(8) NOT NULL CHECK (tipo_cedula IN ('V','E','P')),
    numero_documento VARCHAR(20),
    cedula INTEGER,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    direccion TEXT,
    password VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    rol_id INTEGER DEFAULT 5 REFERENCES public.roles(id),
    sede_id INTEGER REFERENCES public.sedes(id),
    estatus VARCHAR(20) DEFAULT 'Activo' CHECK (estatus IN ('Activo','Inactivo','Bloqueado')),
    sexo VARCHAR(10),
    fecha_nacimiento DATE,
    nacionalidad VARCHAR(100) DEFAULT 'Venezuela',
    estado_aspirante VARCHAR(30) DEFAULT 'En Revision Digital' CHECK (estado_aspirante IN ('En Revision Digital','Con Observaciones','Admitido'))
);
UPDATE public.usuarios SET numero_documento = cedula::varchar WHERE numero_documento IS NULL AND cedula IS NOT NULL;

-- ============================================================
-- 4. TABLAS ACADÉMICAS
-- ============================================================
CREATE TABLE IF NOT EXISTS public.plan_estudios (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    tipo VARCHAR(50) CHECK (tipo IN ('Especializacion','Maestria','Doctorado')),
    codigo VARCHAR(20) UNIQUE,
    activo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.asignaturas (
    codigo VARCHAR(20) PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    uc INTEGER NOT NULL CHECK (uc > 0),
    horas_teoricas INTEGER DEFAULT 0,
    horas_practicas INTEGER DEFAULT 0,
    activa BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.plan_asignaturas (
    id SERIAL PRIMARY KEY,
    plan_id INTEGER REFERENCES public.plan_estudios(id) ON DELETE CASCADE,
    asignatura_codigo VARCHAR(20) REFERENCES public.asignaturas(codigo) ON DELETE CASCADE,
    semestre INTEGER,
    obligatoria BOOLEAN DEFAULT true,
    UNIQUE(plan_id, asignatura_codigo)
);

CREATE TABLE IF NOT EXISTS public.plan_sede (
    id SERIAL PRIMARY KEY,
    plan_id INTEGER REFERENCES public.plan_estudios(id) ON DELETE CASCADE,
    sede_id INTEGER REFERENCES public.sedes(id) ON DELETE CASCADE,
    UNIQUE(plan_id, sede_id)
);

CREATE SEQUENCE IF NOT EXISTS public.secciones_id_seq;
CREATE TABLE IF NOT EXISTS public.secciones (
    id INTEGER DEFAULT nextval('public.secciones_id_seq') PRIMARY KEY,
    plan_id INTEGER REFERENCES public.plan_estudios(id),
    asignatura_codigo VARCHAR(20) REFERENCES public.asignaturas(codigo),
    seccion VARCHAR(10) NOT NULL,
    profesor_id INTEGER REFERENCES public.usuarios(id),
    sede_id INTEGER REFERENCES public.sedes(id),
    cupo_maximo INTEGER DEFAULT 25,
    cupo_actual INTEGER DEFAULT 0,
    aula VARCHAR(50),
    periodo VARCHAR(20),
    activa BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.horarios (
    id SERIAL PRIMARY KEY,
    seccion_id INTEGER REFERENCES public.secciones(id) ON DELETE CASCADE,
    dia_semana INTEGER CHECK (dia_semana BETWEEN 1 AND 7),
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    UNIQUE(seccion_id, dia_semana, hora_inicio)
);

-- ============================================================
-- 5. TABLAS DE PROCESOS
-- ============================================================
CREATE TABLE IF NOT EXISTS public.solicitudes_docentes (
    id SERIAL PRIMARY KEY,
    coordinador_id INTEGER REFERENCES public.usuarios(id),
    sede_id INTEGER REFERENCES public.sedes(id),
    tipo_documento VARCHAR(5) CHECK (tipo_documento IN ('V','E','P')),
    numero_documento VARCHAR(20) NOT NULL,
    nombres VARCHAR(200) NOT NULL,
    apellidos VARCHAR(200) NOT NULL,
    email VARCHAR(255) NOT NULL,
    nacionalidad VARCHAR(100),
    estatus VARCHAR(20) DEFAULT 'Pendiente' CHECK (estatus IN ('Pendiente','Aprobado','Rechazado')),
    admin_id INTEGER REFERENCES public.usuarios(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resuelto_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.inscripciones (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER REFERENCES public.usuarios(id),
    seccion_id INTEGER REFERENCES public.secciones(id),
    estatus VARCHAR(20) DEFAULT 'Por Cancelar' CHECK (estatus IN ('Por Cancelar','Formalizada','Eliminada','Resguardada')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(usuario_id, seccion_id)
);

CREATE TABLE IF NOT EXISTS public.pagos (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER REFERENCES public.usuarios(id),
    inscripcion_id INTEGER REFERENCES public.inscripciones(id),
    banco VARCHAR(100),
    referencia VARCHAR(100) UNIQUE,
    monto DECIMAL(12,2) NOT NULL,
    fecha_pago DATE,
    secretaria_id INTEGER REFERENCES public.usuarios(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.creditos_resguardados (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER REFERENCES public.usuarios(id),
    sede_origen_id INTEGER REFERENCES public.sedes(id),
    sede_destino_id INTEGER REFERENCES public.sedes(id),
    uc_resguardadas INTEGER NOT NULL CHECK (uc_resguardadas > 0),
    monto_resguardado DECIMAL(12,2),
    motivo VARCHAR(50) CHECK (motivo IN ('Eliminacion','Traslado','Reembolso')),
    estatus VARCHAR(20) DEFAULT 'Activo' CHECK (estatus IN ('Activo','Aplicado','Reversado')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    aplicado_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.actas_notas (
    id SERIAL PRIMARY KEY,
    seccion_id INTEGER REFERENCES public.secciones(id),
    usuario_id INTEGER REFERENCES public.usuarios(id),
    nota INTEGER CHECK (nota BETWEEN 0 AND 20),
    inasistencia BOOLEAN DEFAULT false,
    estatus VARCHAR(20) DEFAULT 'Borrador' CHECK (estatus IN ('Borrador','Definitiva')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(seccion_id, usuario_id)
);

CREATE TABLE IF NOT EXISTS public.actas_log (
    id SERIAL PRIMARY KEY,
    acta_id INTEGER REFERENCES public.actas_notas(id),
    usuario_id INTEGER REFERENCES public.usuarios(id),
    nota_anterior INTEGER,
    nota_nueva INTEGER,
    accion VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 6. TABLAS DEL ADMINISTRADOR
-- ============================================================
CREATE TABLE IF NOT EXISTS public.llaves_digitales (
    id SERIAL PRIMARY KEY,
    tipo VARCHAR(50) CHECK (tipo IN ('ModificarPlan','ReabrirActa','EliminarMateria')),
    solicitante_id INTEGER REFERENCES public.usuarios(id),
    admin_id INTEGER REFERENCES public.usuarios(id),
    destino_id INTEGER,
    estatus VARCHAR(20) DEFAULT 'Activa' CHECK (estatus IN ('Activa','Expirada','Usada')),
    validez_horas INTEGER DEFAULT 24,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expira_at TIMESTAMP,
    used_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS public.logs_auditoria (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER REFERENCES public.usuarios(id),
    accion VARCHAR(100) NOT NULL,
    entidad VARCHAR(50),
    entidad_id INTEGER,
    detalle TEXT,
    direccion_ip VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 7. TABLAS DEL ASPIRANTE
-- ============================================================
CREATE TABLE IF NOT EXISTS public.aspirante_documentos (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER REFERENCES public.usuarios(id),
    tipo VARCHAR(50) CHECK (tipo IN ('Cedula','Pasaporte','Titulo','Notas','Curriculum')),
    archivo_ruta VARCHAR(500),
    archivo_nombre VARCHAR(255),
    verificado BOOLEAN DEFAULT false,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Baremo (legacy)
CREATE TABLE IF NOT EXISTS public.baremo_preguntas (
    id SERIAL PRIMARY KEY,
    pregunta TEXT NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    orden INTEGER
);
CREATE TABLE IF NOT EXISTS public.respuestas_baremo (
    id SERIAL PRIMARY KEY,
    id_aspirante INTEGER REFERENCES public.usuarios(id),
    id_pregunta INTEGER REFERENCES public.baremo_preguntas(id),
    respuesta VARCHAR(2) NOT NULL,
    fecha_respuesta TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 8. ÍNDICES
-- ============================================================
CREATE INDEX IF NOT EXISTS idx_usuarios_rol ON public.usuarios(rol_id);
CREATE INDEX IF NOT EXISTS idx_usuarios_sede ON public.usuarios(sede_id);
CREATE INDEX IF NOT EXISTS idx_usuarios_documento ON public.usuarios(tipo_cedula, numero_documento);
CREATE INDEX IF NOT EXISTS idx_secciones_profesor ON public.secciones(profesor_id);
CREATE INDEX IF NOT EXISTS idx_secciones_sede ON public.secciones(sede_id);
CREATE INDEX IF NOT EXISTS idx_inscripciones_usuario ON public.inscripciones(usuario_id);
CREATE INDEX IF NOT EXISTS idx_inscripciones_seccion ON public.inscripciones(seccion_id);
CREATE INDEX IF NOT EXISTS idx_inscripciones_estatus ON public.inscripciones(estatus);
CREATE INDEX IF NOT EXISTS idx_pagos_usuario ON public.pagos(usuario_id);
CREATE INDEX IF NOT EXISTS idx_actas_notas_seccion ON public.actas_notas(seccion_id);
CREATE INDEX IF NOT EXISTS idx_actas_notas_usuario ON public.actas_notas(usuario_id);
CREATE INDEX IF NOT EXISTS idx_logs_usuario ON public.logs_auditoria(usuario_id);
CREATE INDEX IF NOT EXISTS idx_logs_created ON public.logs_auditoria(created_at);
CREATE INDEX IF NOT EXISTS idx_solicitudes_estatus ON public.solicitudes_docentes(estatus);
CREATE INDEX IF NOT EXISTS idx_creditos_usuario ON public.creditos_resguardados(usuario_id);

-- ============================================================
-- 9. DATOS INICIALES
-- ============================================================
INSERT INTO public.asignaturas (codigo, nombre, uc, horas_teoricas, horas_practicas) VALUES
    ('MET551', 'Metodología de la Investigación', 3, 2, 1),
    ('GER401', 'Gerencia Estratégica', 3, 2, 1),
    ('EST301', 'Estadística Aplicada', 3, 2, 1),
    ('DER201', 'Derecho Administrativo', 2, 2, 0),
    ('INF601', 'Gerencia de la Informática', 3, 2, 1),
    ('FIN501', 'Finanzas Públicas', 3, 2, 1)
ON CONFLICT (codigo) DO NOTHING;

INSERT INTO public.plan_estudios (nombre, tipo, codigo) VALUES
    ('Maestría en Gerencia Logística', 'Maestria', 'MGL-2026'),
    ('Maestría en Educación Superior', 'Maestria', 'MES-2026')
ON CONFLICT (codigo) DO NOTHING;

INSERT INTO public.plan_asignaturas (plan_id, asignatura_codigo, semestre)
SELECT p.id, a.codigo, 1 FROM public.plan_estudios p, public.asignaturas a
WHERE p.codigo = 'MGL-2026' AND a.codigo IN ('MET551','GER401','EST301','DER201','INF601','FIN501')
ON CONFLICT DO NOTHING;

INSERT INTO public.plan_sede (plan_id, sede_id)
SELECT p.id, s.id FROM public.plan_estudios p, public.sedes s
WHERE p.codigo = 'MGL-2026' AND s.codigo IN ('CCS','MAR','STO')
ON CONFLICT DO NOTHING;

-- Usuario administrador por defecto (password: Admin2026!)
INSERT INTO public.usuarios (tipo_cedula, cedula, numero_documento, nombres, apellidos, email, password, rol_id, estatus)
VALUES ('V', 1, '1', 'Admin', 'Sistema', 'admin@sip.unefa.edu.ve',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Activo')
ON CONFLICT (email) DO NOTHING;

-- Baremo preguntas (legacy)
INSERT INTO public.baremo_preguntas (id, pregunta, categoria, orden) VALUES
(1, 'Participación en eventos científicos nacionales e internacionales.', 'Academico', 1),
(2, 'Participación como jurado o tutor en trabajos de investigación.', 'Academico', 2),
(3, 'Disposición a participar en actividad académicas, investigación e institucionales.', 'Academico', 3),
(4, 'Tema de interés específica para su investigación vinculada a la nación.', 'Investigacion', 4),
(5, 'Vinculación entre el área profesional con los estudios de postgrado.', 'Investigacion', 5),
(6, 'Afiliación a grupo o red de investigadores.', 'Investigacion', 6),
(7, 'Participación como evaluador en artículos científicos.', 'Investigacion', 7),
(8, 'Ha escrito o publicado artículos científicos (Opción A).', 'Investigacion', 8),
(9, 'Ha escrito o publicado artículos científicos (Opción B).', 'Investigacion', 9),
(10, 'Familiarización con las líneas de investigación de la Universidad.', 'Investigacion', 10),
(11, 'La investigación satisface fines personales o institucionales.', 'Investigacion', 11),
(12, 'Acceso y disponibilidad al manejo de equipos tecnológicos.', 'Otros', 12),
(13, 'Disponibilidad personal para financiar los estudios (Opción A).', 'Otros', 13),
(14, 'Disponibilidad personal para financiar los estudios (Opción B).', 'Otros', 14)
ON CONFLICT (id) DO NOTHING;

-- Secuencias
SELECT pg_catalog.setval('public.baremo_preguntas_id_seq', 14, true);
SELECT pg_catalog.setval('public.usuarios_id_seq', COALESCE((SELECT MAX(id) FROM public.usuarios), 25), true);
