--
-- PostgreSQL database dump
--

\restrict 5xokmnSBQgBbAHsC8h07NT2pCgCXEFkf4cG4HqTmARdwKR7Lzdh5P5idftGy5sM

-- Dumped from database version 18.4
-- Dumped by pg_dump version 18.4

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

ALTER TABLE IF EXISTS ONLY public.usuarios DROP CONSTRAINT IF EXISTS usuarios_sede_id_fkey;
ALTER TABLE IF EXISTS ONLY public.usuarios DROP CONSTRAINT IF EXISTS usuarios_rol_id_fkey;
ALTER TABLE IF EXISTS ONLY public.solicitudes_docentes DROP CONSTRAINT IF EXISTS solicitudes_docentes_sede_id_fkey;
ALTER TABLE IF EXISTS ONLY public.solicitudes_docentes DROP CONSTRAINT IF EXISTS solicitudes_docentes_coordinador_id_fkey;
ALTER TABLE IF EXISTS ONLY public.solicitudes_docentes DROP CONSTRAINT IF EXISTS solicitudes_docentes_admin_id_fkey;
ALTER TABLE IF EXISTS ONLY public.secciones DROP CONSTRAINT IF EXISTS secciones_sede_id_fkey;
ALTER TABLE IF EXISTS ONLY public.secciones DROP CONSTRAINT IF EXISTS secciones_profesor_id_fkey;
ALTER TABLE IF EXISTS ONLY public.secciones DROP CONSTRAINT IF EXISTS secciones_plan_id_fkey;
ALTER TABLE IF EXISTS ONLY public.secciones DROP CONSTRAINT IF EXISTS secciones_asignatura_codigo_fkey;
ALTER TABLE IF EXISTS ONLY public.respuestas_baremo DROP CONSTRAINT IF EXISTS respuestas_baremo_id_pregunta_fkey;
ALTER TABLE IF EXISTS ONLY public.respuestas_baremo DROP CONSTRAINT IF EXISTS respuestas_baremo_id_aspirante_fkey;
ALTER TABLE IF EXISTS ONLY public.requisitos_preinscripcion DROP CONSTRAINT IF EXISTS requisitos_preinscripcion_aspirante_id_fkey;
ALTER TABLE IF EXISTS ONLY public.planes_estudio DROP CONSTRAINT IF EXISTS planes_estudio_programa_id_fkey;
ALTER TABLE IF EXISTS ONLY public.planes_estudio DROP CONSTRAINT IF EXISTS planes_estudio_asignatura_codigo_fkey;
ALTER TABLE IF EXISTS ONLY public.plan_sede DROP CONSTRAINT IF EXISTS plan_sede_sede_id_fkey;
ALTER TABLE IF EXISTS ONLY public.plan_sede DROP CONSTRAINT IF EXISTS plan_sede_plan_id_fkey;
ALTER TABLE IF EXISTS ONLY public.plan_asignaturas DROP CONSTRAINT IF EXISTS plan_asignaturas_plan_id_fkey;
ALTER TABLE IF EXISTS ONLY public.plan_asignaturas DROP CONSTRAINT IF EXISTS plan_asignaturas_asignatura_codigo_fkey;
ALTER TABLE IF EXISTS ONLY public.pagos DROP CONSTRAINT IF EXISTS pagos_usuario_id_fkey;
ALTER TABLE IF EXISTS ONLY public.pagos DROP CONSTRAINT IF EXISTS pagos_secretaria_id_fkey;
ALTER TABLE IF EXISTS ONLY public.pagos DROP CONSTRAINT IF EXISTS pagos_inscripcion_id_fkey;
ALTER TABLE IF EXISTS ONLY public.notas DROP CONSTRAINT IF EXISTS notas_inscripcion_id_fkey;
ALTER TABLE IF EXISTS ONLY public.notas DROP CONSTRAINT IF EXISTS notas_asignatura_codigo_fkey;
ALTER TABLE IF EXISTS ONLY public.mallas_curriculares DROP CONSTRAINT IF EXISTS mallas_curriculares_id_programa_fkey;
ALTER TABLE IF EXISTS ONLY public.logs_auditoria DROP CONSTRAINT IF EXISTS logs_auditoria_usuario_id_fkey;
ALTER TABLE IF EXISTS ONLY public.llaves_digitales DROP CONSTRAINT IF EXISTS llaves_digitales_solicitante_id_fkey;
ALTER TABLE IF EXISTS ONLY public.llaves_digitales DROP CONSTRAINT IF EXISTS llaves_digitales_admin_id_fkey;
ALTER TABLE IF EXISTS ONLY public.inscripciones DROP CONSTRAINT IF EXISTS inscripciones_seccion_id_fkey;
ALTER TABLE IF EXISTS ONLY public.inscripciones DROP CONSTRAINT IF EXISTS inscripciones_estudiante_id_fkey;
ALTER TABLE IF EXISTS ONLY public.inscripciones DROP CONSTRAINT IF EXISTS inscripciones_cohorte_id_fkey;
ALTER TABLE IF EXISTS ONLY public.horarios DROP CONSTRAINT IF EXISTS horarios_seccion_id_fkey;
ALTER TABLE IF EXISTS ONLY public.mallas_curriculares DROP CONSTRAINT IF EXISTS fk_mallas_materias;
ALTER TABLE IF EXISTS ONLY public.creditos_resguardados DROP CONSTRAINT IF EXISTS creditos_resguardados_usuario_id_fkey;
ALTER TABLE IF EXISTS ONLY public.creditos_resguardados DROP CONSTRAINT IF EXISTS creditos_resguardados_sede_origen_id_fkey;
ALTER TABLE IF EXISTS ONLY public.creditos_resguardados DROP CONSTRAINT IF EXISTS creditos_resguardados_sede_destino_id_fkey;
ALTER TABLE IF EXISTS ONLY public.cohortes DROP CONSTRAINT IF EXISTS cohortes_programa_id_fkey;
ALTER TABLE IF EXISTS ONLY public.cohortes DROP CONSTRAINT IF EXISTS cohortes_nucleo_id_fkey;
ALTER TABLE IF EXISTS ONLY public.autorizaciones_cnu DROP CONSTRAINT IF EXISTS autorizaciones_cnu_programa_id_fkey;
ALTER TABLE IF EXISTS ONLY public.autorizaciones_cnu DROP CONSTRAINT IF EXISTS autorizaciones_cnu_nucleo_id_fkey;
ALTER TABLE IF EXISTS ONLY public.aspirante_documentos DROP CONSTRAINT IF EXISTS aspirante_documentos_usuario_id_fkey;
ALTER TABLE IF EXISTS ONLY public.asignaciones_docente DROP CONSTRAINT IF EXISTS asignaciones_docente_docente_id_fkey;
ALTER TABLE IF EXISTS ONLY public.asignaciones_docente DROP CONSTRAINT IF EXISTS asignaciones_docente_cohorte_id_fkey;
ALTER TABLE IF EXISTS ONLY public.asignaciones_docente DROP CONSTRAINT IF EXISTS asignaciones_docente_asignatura_codigo_fkey;
ALTER TABLE IF EXISTS ONLY public.actas_notas DROP CONSTRAINT IF EXISTS actas_notas_usuario_id_fkey;
ALTER TABLE IF EXISTS ONLY public.actas_notas DROP CONSTRAINT IF EXISTS actas_notas_seccion_id_fkey;
ALTER TABLE IF EXISTS ONLY public.actas_log DROP CONSTRAINT IF EXISTS actas_log_usuario_id_fkey;
ALTER TABLE IF EXISTS ONLY public.actas_log DROP CONSTRAINT IF EXISTS actas_log_acta_id_fkey;
DROP TRIGGER IF EXISTS trg_validar_nota_antes_registro ON public.notas;
DROP TRIGGER IF EXISTS trg_despues_aprobacion_admision ON public.usuarios;
DROP INDEX IF EXISTS public.idx_usuarios_sede;
DROP INDEX IF EXISTS public.idx_usuarios_rol;
DROP INDEX IF EXISTS public.idx_solicitudes_estatus;
DROP INDEX IF EXISTS public.idx_secciones_sede;
DROP INDEX IF EXISTS public.idx_secciones_profesor;
DROP INDEX IF EXISTS public.idx_pagos_usuario;
DROP INDEX IF EXISTS public.idx_logs_usuario;
DROP INDEX IF EXISTS public.idx_logs_created;
DROP INDEX IF EXISTS public.idx_creditos_usuario;
DROP INDEX IF EXISTS public.idx_actas_notas_usuario;
DROP INDEX IF EXISTS public.idx_actas_notas_seccion;
ALTER TABLE IF EXISTS ONLY public.usuarios DROP CONSTRAINT IF EXISTS usuarios_pkey;
ALTER TABLE IF EXISTS ONLY public.usuarios DROP CONSTRAINT IF EXISTS usuarios_matricula_key;
ALTER TABLE IF EXISTS ONLY public.usuarios DROP CONSTRAINT IF EXISTS usuarios_correo_key;
ALTER TABLE IF EXISTS ONLY public.usuarios_acceso DROP CONSTRAINT IF EXISTS usuarios_acceso_username_key;
ALTER TABLE IF EXISTS ONLY public.usuarios_acceso DROP CONSTRAINT IF EXISTS usuarios_acceso_pkey;
ALTER TABLE IF EXISTS ONLY public.mallas_curriculares DROP CONSTRAINT IF EXISTS uq_programa_materia;
ALTER TABLE IF EXISTS ONLY public.notas DROP CONSTRAINT IF EXISTS uq_nota_por_materia_inscrita;
ALTER TABLE IF EXISTS ONLY public.asignaciones_docente DROP CONSTRAINT IF EXISTS uq_docente_materia_cohorte;
ALTER TABLE IF EXISTS ONLY public.programas DROP CONSTRAINT IF EXISTS unique_programa;
ALTER TABLE IF EXISTS ONLY public.autorizaciones_cnu DROP CONSTRAINT IF EXISTS unique_nucleo_programa;
ALTER TABLE IF EXISTS ONLY public.asignaturas DROP CONSTRAINT IF EXISTS uk_asignatura_codigo;
ALTER TABLE IF EXISTS ONLY public.solicitudes_docentes DROP CONSTRAINT IF EXISTS solicitudes_docentes_pkey;
ALTER TABLE IF EXISTS ONLY public.sedes DROP CONSTRAINT IF EXISTS sedes_pkey;
ALTER TABLE IF EXISTS ONLY public.sedes DROP CONSTRAINT IF EXISTS sedes_codigo_key;
ALTER TABLE IF EXISTS ONLY public.secciones DROP CONSTRAINT IF EXISTS secciones_pkey;
ALTER TABLE IF EXISTS ONLY public.roles DROP CONSTRAINT IF EXISTS roles_pkey;
ALTER TABLE IF EXISTS ONLY public.roles DROP CONSTRAINT IF EXISTS roles_nombre_key;
ALTER TABLE IF EXISTS ONLY public.respuestas_baremo DROP CONSTRAINT IF EXISTS respuestas_baremo_pkey;
ALTER TABLE IF EXISTS ONLY public.requisitos_preinscripcion DROP CONSTRAINT IF EXISTS requisitos_preinscripcion_pkey;
ALTER TABLE IF EXISTS ONLY public.requisitos_preinscripcion DROP CONSTRAINT IF EXISTS requisitos_preinscripcion_aspirante_id_key;
ALTER TABLE IF EXISTS ONLY public.programas_postgrado DROP CONSTRAINT IF EXISTS programas_postgrado_pkey;
ALTER TABLE IF EXISTS ONLY public.programas DROP CONSTRAINT IF EXISTS programas_pkey;
ALTER TABLE IF EXISTS ONLY public.planes_estudio DROP CONSTRAINT IF EXISTS planes_estudio_pkey;
ALTER TABLE IF EXISTS ONLY public.plan_sede DROP CONSTRAINT IF EXISTS plan_sede_plan_id_sede_id_key;
ALTER TABLE IF EXISTS ONLY public.plan_sede DROP CONSTRAINT IF EXISTS plan_sede_pkey;
ALTER TABLE IF EXISTS ONLY public.plan_estudios DROP CONSTRAINT IF EXISTS plan_estudios_pkey;
ALTER TABLE IF EXISTS ONLY public.plan_estudios DROP CONSTRAINT IF EXISTS plan_estudios_codigo_key;
ALTER TABLE IF EXISTS ONLY public.plan_asignaturas DROP CONSTRAINT IF EXISTS plan_asignaturas_plan_id_asignatura_codigo_key;
ALTER TABLE IF EXISTS ONLY public.plan_asignaturas DROP CONSTRAINT IF EXISTS plan_asignaturas_pkey;
ALTER TABLE IF EXISTS ONLY public.pagos DROP CONSTRAINT IF EXISTS pagos_referencia_bancaria_key;
ALTER TABLE IF EXISTS ONLY public.pagos DROP CONSTRAINT IF EXISTS pagos_pkey;
ALTER TABLE IF EXISTS ONLY public.nucleos DROP CONSTRAINT IF EXISTS nucleos_pkey;
ALTER TABLE IF EXISTS ONLY public.nucleos DROP CONSTRAINT IF EXISTS nucleos_nombre_key;
ALTER TABLE IF EXISTS ONLY public.notas DROP CONSTRAINT IF EXISTS notas_pkey;
ALTER TABLE IF EXISTS ONLY public.materias DROP CONSTRAINT IF EXISTS materias_pkey;
ALTER TABLE IF EXISTS ONLY public.materias DROP CONSTRAINT IF EXISTS materias_nombre_key;
ALTER TABLE IF EXISTS ONLY public.materias DROP CONSTRAINT IF EXISTS materias_codigo_key;
ALTER TABLE IF EXISTS ONLY public.mallas_curriculares DROP CONSTRAINT IF EXISTS mallas_curriculares_pkey;
ALTER TABLE IF EXISTS ONLY public.logs_auditoria DROP CONSTRAINT IF EXISTS logs_auditoria_pkey;
ALTER TABLE IF EXISTS ONLY public.llaves_digitales DROP CONSTRAINT IF EXISTS llaves_digitales_pkey;
ALTER TABLE IF EXISTS ONLY public.inscripciones DROP CONSTRAINT IF EXISTS inscripciones_pkey;
ALTER TABLE IF EXISTS ONLY public.horarios DROP CONSTRAINT IF EXISTS horarios_seccion_id_dia_semana_hora_inicio_key;
ALTER TABLE IF EXISTS ONLY public.horarios DROP CONSTRAINT IF EXISTS horarios_pkey;
ALTER TABLE IF EXISTS ONLY public.creditos_resguardados DROP CONSTRAINT IF EXISTS creditos_resguardados_pkey;
ALTER TABLE IF EXISTS ONLY public.cohortes DROP CONSTRAINT IF EXISTS cohortes_pkey;
ALTER TABLE IF EXISTS ONLY public.cohortes DROP CONSTRAINT IF EXISTS cohortes_codigo_cohorte_key;
ALTER TABLE IF EXISTS ONLY public.baremo_preguntas DROP CONSTRAINT IF EXISTS baremo_preguntas_pkey;
ALTER TABLE IF EXISTS ONLY public.autorizaciones_cnu DROP CONSTRAINT IF EXISTS autorizaciones_cnu_pkey;
ALTER TABLE IF EXISTS ONLY public.aspirante_documentos DROP CONSTRAINT IF EXISTS aspirante_documentos_pkey;
ALTER TABLE IF EXISTS ONLY public.asignaturas DROP CONSTRAINT IF EXISTS asignaturas_pkey;
ALTER TABLE IF EXISTS ONLY public.asignaciones_docente DROP CONSTRAINT IF EXISTS asignaciones_docente_pkey;
ALTER TABLE IF EXISTS ONLY public.actas_notas DROP CONSTRAINT IF EXISTS actas_notas_seccion_id_usuario_id_key;
ALTER TABLE IF EXISTS ONLY public.actas_notas DROP CONSTRAINT IF EXISTS actas_notas_pkey;
ALTER TABLE IF EXISTS ONLY public.actas_log DROP CONSTRAINT IF EXISTS actas_log_pkey;
ALTER TABLE IF EXISTS public.usuarios_acceso ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.usuarios ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.solicitudes_docentes ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.sedes ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.roles ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.respuestas_baremo ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.requisitos_preinscripcion ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.programas ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.planes_estudio ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.plan_sede ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.plan_estudios ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.plan_asignaturas ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.pagos ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.nucleos ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.notas ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.materias ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.mallas_curriculares ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.logs_auditoria ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.llaves_digitales ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.inscripciones ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.horarios ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.creditos_resguardados ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.cohortes ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.baremo_preguntas ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.autorizaciones_cnu ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.aspirante_documentos ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.asignaciones_docente ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.actas_notas ALTER COLUMN id DROP DEFAULT;
ALTER TABLE IF EXISTS public.actas_log ALTER COLUMN id DROP DEFAULT;
DROP SEQUENCE IF EXISTS public.usuarios_id_seq;
DROP SEQUENCE IF EXISTS public.usuarios_acceso_id_seq;
DROP TABLE IF EXISTS public.usuarios_acceso;
DROP TABLE IF EXISTS public.usuarios;
DROP SEQUENCE IF EXISTS public.solicitudes_docentes_id_seq;
DROP TABLE IF EXISTS public.solicitudes_docentes;
DROP SEQUENCE IF EXISTS public.seq_matricula_estudiante;
DROP SEQUENCE IF EXISTS public.sedes_id_seq;
DROP TABLE IF EXISTS public.sedes;
DROP TABLE IF EXISTS public.secciones;
DROP SEQUENCE IF EXISTS public.secciones_id_seq;
DROP SEQUENCE IF EXISTS public.roles_id_seq;
DROP TABLE IF EXISTS public.roles;
DROP SEQUENCE IF EXISTS public.respuestas_baremo_id_seq;
DROP TABLE IF EXISTS public.respuestas_baremo;
DROP SEQUENCE IF EXISTS public.requisitos_preinscripcion_id_seq;
DROP TABLE IF EXISTS public.requisitos_preinscripcion;
DROP TABLE IF EXISTS public.programas_postgrado;
DROP SEQUENCE IF EXISTS public.programas_id_seq;
DROP TABLE IF EXISTS public.programas;
DROP SEQUENCE IF EXISTS public.planes_estudio_id_seq;
DROP TABLE IF EXISTS public.planes_estudio;
DROP SEQUENCE IF EXISTS public.plan_sede_id_seq;
DROP TABLE IF EXISTS public.plan_sede;
DROP SEQUENCE IF EXISTS public.plan_estudios_id_seq;
DROP TABLE IF EXISTS public.plan_estudios;
DROP SEQUENCE IF EXISTS public.plan_asignaturas_id_seq;
DROP TABLE IF EXISTS public.plan_asignaturas;
DROP SEQUENCE IF EXISTS public.pagos_id_seq;
DROP TABLE IF EXISTS public.pagos;
DROP SEQUENCE IF EXISTS public.nucleos_id_seq;
DROP TABLE IF EXISTS public.nucleos;
DROP SEQUENCE IF EXISTS public.notas_id_seq;
DROP TABLE IF EXISTS public.notas;
DROP SEQUENCE IF EXISTS public.materias_id_seq;
DROP TABLE IF EXISTS public.materias;
DROP SEQUENCE IF EXISTS public.mallas_curriculares_id_seq;
DROP TABLE IF EXISTS public.mallas_curriculares;
DROP SEQUENCE IF EXISTS public.logs_auditoria_id_seq;
DROP TABLE IF EXISTS public.logs_auditoria;
DROP SEQUENCE IF EXISTS public.llaves_digitales_id_seq;
DROP TABLE IF EXISTS public.llaves_digitales;
DROP SEQUENCE IF EXISTS public.inscripciones_id_seq;
DROP TABLE IF EXISTS public.inscripciones;
DROP SEQUENCE IF EXISTS public.horarios_id_seq;
DROP TABLE IF EXISTS public.horarios;
DROP SEQUENCE IF EXISTS public.creditos_resguardados_id_seq;
DROP TABLE IF EXISTS public.creditos_resguardados;
DROP SEQUENCE IF EXISTS public.cohortes_id_seq;
DROP TABLE IF EXISTS public.cohortes;
DROP SEQUENCE IF EXISTS public.baremo_preguntas_id_seq;
DROP TABLE IF EXISTS public.baremo_preguntas;
DROP SEQUENCE IF EXISTS public.autorizaciones_cnu_id_seq;
DROP TABLE IF EXISTS public.autorizaciones_cnu;
DROP SEQUENCE IF EXISTS public.aspirante_documentos_id_seq;
DROP TABLE IF EXISTS public.aspirante_documentos;
DROP TABLE IF EXISTS public.asignaturas;
DROP SEQUENCE IF EXISTS public.asignaciones_docente_id_seq;
DROP TABLE IF EXISTS public.asignaciones_docente;
DROP SEQUENCE IF EXISTS public.actas_notas_id_seq;
DROP TABLE IF EXISTS public.actas_notas;
DROP SEQUENCE IF EXISTS public.actas_log_id_seq;
DROP TABLE IF EXISTS public.actas_log;
DROP FUNCTION IF EXISTS public.tg_validar_inscripcion_nota();
DROP FUNCTION IF EXISTS public.tg_automatizar_admision();
DROP FUNCTION IF EXISTS public.registrar_log();
DROP EXTENSION IF EXISTS "uuid-ossp";
--
-- Name: uuid-ossp; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA public;


--
-- Name: EXTENSION "uuid-ossp"; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION "uuid-ossp" IS 'generate universally unique identifiers (UUIDs)';


--
-- Name: registrar_log(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.registrar_log() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
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
$$;


--
-- Name: tg_automatizar_admision(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.tg_automatizar_admision() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF NEW.estatus_admision = 'APROBADO' AND (OLD.estatus_admision IS NULL OR OLD.estatus_admision != 'APROBADO') THEN
        NEW.rol := 'ESTUDIANTE';
        IF NEW.matricula IS NULL THEN
            NEW.matricula := 'PG-' || EXTRACT(YEAR FROM CURRENT_DATE)::TEXT || '-' || LPAD(NEXTVAL('seq_matricula_estudiante')::TEXT, 4, '0');
        END IF;
    END IF;
    RETURN NEW;
END;
$$;


--
-- Name: tg_validar_inscripcion_nota(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.tg_validar_inscripcion_nota() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_estatus_pago VARCHAR(30);
BEGIN
    SELECT estatus_pago INTO v_estatus_pago
    FROM inscripciones
    WHERE id = NEW.inscripcion_id;

    IF v_estatus_pago IS NULL OR v_estatus_pago != 'APROBADO' THEN
        RAISE EXCEPTION 'Restricción de Flujo: No se puede registrar la calificación debido a que la inscripción del término se encuentra PENDIENTE de conciliación por Secretaría.';
    END IF;
    RETURN NEW;
END;
$$;


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: actas_log; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.actas_log (
    id integer NOT NULL,
    acta_id integer,
    usuario_id integer,
    nota_anterior integer,
    nota_nueva integer,
    accion character varying(50),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: actas_log_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.actas_log_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: actas_log_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.actas_log_id_seq OWNED BY public.actas_log.id;


--
-- Name: actas_notas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.actas_notas (
    id integer NOT NULL,
    seccion_id integer,
    usuario_id integer,
    nota integer,
    inasistencia boolean DEFAULT false,
    estatus character varying(20) DEFAULT 'Borrador'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT actas_notas_estatus_check CHECK (((estatus)::text = ANY ((ARRAY['Borrador'::character varying, 'Definitiva'::character varying])::text[]))),
    CONSTRAINT actas_notas_nota_check CHECK (((nota >= 0) AND (nota <= 20)))
);


--
-- Name: actas_notas_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.actas_notas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: actas_notas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.actas_notas_id_seq OWNED BY public.actas_notas.id;


--
-- Name: asignaciones_docente; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.asignaciones_docente (
    id integer NOT NULL,
    docente_id integer NOT NULL,
    asignatura_codigo character varying(50) NOT NULL,
    cohorte_id integer NOT NULL,
    termino character varying(10) NOT NULL
);


--
-- Name: asignaciones_docente_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.asignaciones_docente_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: asignaciones_docente_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.asignaciones_docente_id_seq OWNED BY public.asignaciones_docente.id;


--
-- Name: asignaturas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.asignaturas (
    codigo character varying(20) NOT NULL,
    nombre character varying(200) NOT NULL,
    horas_teoricas integer DEFAULT 0,
    horas_practicas integer DEFAULT 0,
    uc integer DEFAULT 0,
    activa boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: aspirante_documentos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.aspirante_documentos (
    id integer NOT NULL,
    usuario_id integer,
    tipo character varying(50),
    archivo_ruta character varying(500),
    archivo_nombre character varying(255),
    verificado boolean DEFAULT false,
    observaciones text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT aspirante_documentos_tipo_check CHECK (((tipo)::text = ANY ((ARRAY['Cedula'::character varying, 'Pasaporte'::character varying, 'Titulo'::character varying, 'Notas'::character varying, 'Curriculum'::character varying])::text[])))
);


--
-- Name: aspirante_documentos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.aspirante_documentos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: aspirante_documentos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.aspirante_documentos_id_seq OWNED BY public.aspirante_documentos.id;


--
-- Name: autorizaciones_cnu; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.autorizaciones_cnu (
    id integer NOT NULL,
    nucleo_id integer,
    programa_id integer,
    cod_opcion character varying(20),
    codigo_cnu character varying(50),
    observacion_memorando text,
    observacion_gaceta text
);


--
-- Name: autorizaciones_cnu_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.autorizaciones_cnu_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: autorizaciones_cnu_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.autorizaciones_cnu_id_seq OWNED BY public.autorizaciones_cnu.id;


--
-- Name: baremo_preguntas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.baremo_preguntas (
    id integer NOT NULL,
    pregunta text NOT NULL,
    categoria character varying(50) NOT NULL,
    orden integer
);


--
-- Name: baremo_preguntas_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.baremo_preguntas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: baremo_preguntas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.baremo_preguntas_id_seq OWNED BY public.baremo_preguntas.id;


--
-- Name: cohortes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cohortes (
    id integer NOT NULL,
    programa_id integer NOT NULL,
    nucleo_id integer NOT NULL,
    codigo_cohorte character varying(50) NOT NULL,
    fecha_inicio date NOT NULL
);


--
-- Name: cohortes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cohortes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cohortes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cohortes_id_seq OWNED BY public.cohortes.id;


--
-- Name: creditos_resguardados; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.creditos_resguardados (
    id integer NOT NULL,
    usuario_id integer,
    sede_origen_id integer,
    sede_destino_id integer,
    uc_resguardadas integer NOT NULL,
    monto_resguardado numeric(12,2),
    motivo character varying(50),
    estatus character varying(20) DEFAULT 'Activo'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    aplicado_at timestamp without time zone,
    CONSTRAINT creditos_resguardados_estatus_check CHECK (((estatus)::text = ANY ((ARRAY['Activo'::character varying, 'Aplicado'::character varying, 'Reversado'::character varying])::text[]))),
    CONSTRAINT creditos_resguardados_motivo_check CHECK (((motivo)::text = ANY ((ARRAY['Eliminacion'::character varying, 'Traslado'::character varying, 'Reembolso'::character varying])::text[]))),
    CONSTRAINT creditos_resguardados_uc_resguardadas_check CHECK ((uc_resguardadas > 0))
);


--
-- Name: creditos_resguardados_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.creditos_resguardados_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: creditos_resguardados_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.creditos_resguardados_id_seq OWNED BY public.creditos_resguardados.id;


--
-- Name: horarios; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.horarios (
    id integer NOT NULL,
    seccion_id integer,
    dia_semana integer,
    hora_inicio time without time zone NOT NULL,
    hora_fin time without time zone NOT NULL,
    CONSTRAINT horarios_dia_semana_check CHECK (((dia_semana >= 1) AND (dia_semana <= 7)))
);


--
-- Name: horarios_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.horarios_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: horarios_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.horarios_id_seq OWNED BY public.horarios.id;


--
-- Name: inscripciones; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.inscripciones (
    id integer NOT NULL,
    usuario_id integer CONSTRAINT inscripciones_estudiante_id_not_null NOT NULL,
    cohorte_id integer,
    termino character varying(10),
    estatus_pago character varying(30) DEFAULT 'PENDIENTE'::character varying NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    seccion_id integer,
    estatus character varying(20) DEFAULT 'Por Cancelar'::character varying,
    CONSTRAINT chk_inscripcion_pago CHECK (((estatus_pago)::text = ANY ((ARRAY['PENDIENTE'::character varying, 'APROBADO'::character varying])::text[])))
);


--
-- Name: inscripciones_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.inscripciones_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: inscripciones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.inscripciones_id_seq OWNED BY public.inscripciones.id;


--
-- Name: llaves_digitales; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.llaves_digitales (
    id integer NOT NULL,
    tipo character varying(50),
    solicitante_id integer,
    admin_id integer,
    destino_id integer,
    estatus character varying(20) DEFAULT 'Activa'::character varying,
    validez_horas integer DEFAULT 24,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    expira_at timestamp without time zone,
    used_at timestamp without time zone,
    CONSTRAINT llaves_digitales_estatus_check CHECK (((estatus)::text = ANY ((ARRAY['Activa'::character varying, 'Expirada'::character varying, 'Usada'::character varying])::text[]))),
    CONSTRAINT llaves_digitales_tipo_check CHECK (((tipo)::text = ANY ((ARRAY['ModificarPlan'::character varying, 'ReabrirActa'::character varying, 'EliminarMateria'::character varying])::text[])))
);


--
-- Name: llaves_digitales_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.llaves_digitales_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: llaves_digitales_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.llaves_digitales_id_seq OWNED BY public.llaves_digitales.id;


--
-- Name: logs_auditoria; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.logs_auditoria (
    id integer NOT NULL,
    usuario_id integer,
    accion character varying(100) NOT NULL,
    entidad character varying(50),
    entidad_id integer,
    detalle text,
    direccion_ip character varying(45),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: logs_auditoria_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.logs_auditoria_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: logs_auditoria_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.logs_auditoria_id_seq OWNED BY public.logs_auditoria.id;


--
-- Name: mallas_curriculares; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.mallas_curriculares (
    id integer NOT NULL,
    id_programa integer NOT NULL,
    id_materia character varying(20) NOT NULL,
    periodo integer NOT NULL,
    unidades_credito integer NOT NULL,
    es_electiva boolean DEFAULT false NOT NULL
);


--
-- Name: mallas_curriculares_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.mallas_curriculares_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: mallas_curriculares_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.mallas_curriculares_id_seq OWNED BY public.mallas_curriculares.id;


--
-- Name: materias; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.materias (
    id integer NOT NULL,
    codigo character varying(20) NOT NULL,
    nombre character varying(150) NOT NULL,
    horas_teoricas integer DEFAULT 0 NOT NULL,
    horas_practicas integer DEFAULT 0 NOT NULL
);


--
-- Name: materias_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.materias_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: materias_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.materias_id_seq OWNED BY public.materias.id;


--
-- Name: notas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.notas (
    id integer NOT NULL,
    inscripcion_id integer NOT NULL,
    asignatura_codigo character varying(50) NOT NULL,
    nota_final numeric(4,2),
    CONSTRAINT chk_nota_rango CHECK (((nota_final >= (0)::numeric) AND (nota_final <= (20)::numeric)))
);


--
-- Name: notas_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.notas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: notas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.notas_id_seq OWNED BY public.notas.id;


--
-- Name: nucleos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.nucleos (
    id integer NOT NULL,
    nombre character varying(150) NOT NULL,
    fase_actual integer DEFAULT 1
);


--
-- Name: nucleos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.nucleos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: nucleos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.nucleos_id_seq OWNED BY public.nucleos.id;


--
-- Name: pagos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pagos (
    id integer NOT NULL,
    usuario_id integer NOT NULL,
    referencia_bancaria character varying(100) NOT NULL,
    monto numeric(12,2) NOT NULL,
    tipo_pago character varying(30) NOT NULL,
    estatus character varying(30) DEFAULT 'PENDIENTE'::character varying NOT NULL,
    inscripcion_id integer,
    banco character varying(100),
    referencia character varying(100),
    fecha_pago date,
    secretaria_id integer,
    CONSTRAINT chk_pago_estatus CHECK (((estatus)::text = ANY ((ARRAY['PENDIENTE'::character varying, 'CONCILIADO'::character varying, 'RECHAZADO'::character varying])::text[]))),
    CONSTRAINT chk_pago_monto CHECK ((monto > (0)::numeric)),
    CONSTRAINT chk_tipo_pago CHECK (((tipo_pago)::text = ANY ((ARRAY['ARANCEL_INICIAL'::character varying, 'MATRICULA'::character varying])::text[])))
);


--
-- Name: pagos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.pagos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pagos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.pagos_id_seq OWNED BY public.pagos.id;


--
-- Name: plan_asignaturas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.plan_asignaturas (
    id integer NOT NULL,
    plan_id integer,
    asignatura_codigo character varying(20),
    semestre integer,
    obligatoria boolean DEFAULT true
);


--
-- Name: plan_asignaturas_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.plan_asignaturas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: plan_asignaturas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.plan_asignaturas_id_seq OWNED BY public.plan_asignaturas.id;


--
-- Name: plan_estudios; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.plan_estudios (
    id integer NOT NULL,
    nombre character varying(255) NOT NULL,
    tipo character varying(50),
    codigo character varying(20),
    activo boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT plan_estudios_tipo_check CHECK (((tipo)::text = ANY ((ARRAY['Especializacion'::character varying, 'Maestria'::character varying, 'Doctorado'::character varying])::text[])))
);


--
-- Name: plan_estudios_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.plan_estudios_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: plan_estudios_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.plan_estudios_id_seq OWNED BY public.plan_estudios.id;


--
-- Name: plan_sede; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.plan_sede (
    id integer NOT NULL,
    plan_id integer,
    sede_id integer
);


--
-- Name: plan_sede_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.plan_sede_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: plan_sede_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.plan_sede_id_seq OWNED BY public.plan_sede.id;


--
-- Name: planes_estudio; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.planes_estudio (
    id integer NOT NULL,
    programa_id integer,
    asignatura_codigo character varying(20),
    termino character varying(20),
    tipo_materia character varying(50) DEFAULT 'OBLIGATORIA'::character varying
);


--
-- Name: planes_estudio_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.planes_estudio_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: planes_estudio_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.planes_estudio_id_seq OWNED BY public.planes_estudio.id;


--
-- Name: programas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.programas (
    id integer NOT NULL,
    nombre character varying(200) NOT NULL,
    acreditable character varying(100),
    grado_academico character varying(150),
    regimen character varying(50),
    vigencia character varying(10),
    tipo character varying(50)
);


--
-- Name: programas_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.programas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: programas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.programas_id_seq OWNED BY public.programas.id;


--
-- Name: programas_postgrado; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.programas_postgrado (
    id integer NOT NULL,
    cod_opcion character varying(10),
    nucleo_extension character varying(100),
    programa character varying(150),
    acreditable character varying(50),
    observacion_1 character varying(255),
    observacion_2 text,
    codigo_cnu character varying(20),
    grado_academico character varying(100)
);


--
-- Name: requisitos_preinscripcion; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.requisitos_preinscripcion (
    id integer NOT NULL,
    aspirante_id integer NOT NULL,
    ruta_pdf_titulo character varying(255) NOT NULL,
    ruta_pdf_notas character varying(255) NOT NULL,
    ruta_pdf_cv character varying(255) NOT NULL
);


--
-- Name: requisitos_preinscripcion_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.requisitos_preinscripcion_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: requisitos_preinscripcion_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.requisitos_preinscripcion_id_seq OWNED BY public.requisitos_preinscripcion.id;


--
-- Name: respuestas_baremo; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.respuestas_baremo (
    id integer NOT NULL,
    id_aspirante integer,
    id_pregunta integer,
    respuesta character varying(2) NOT NULL,
    fecha_respuesta timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: respuestas_baremo_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.respuestas_baremo_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: respuestas_baremo_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.respuestas_baremo_id_seq OWNED BY public.respuestas_baremo.id;


--
-- Name: roles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.roles (
    id integer NOT NULL,
    nombre character varying(50) NOT NULL,
    descripcion text
);


--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.roles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- Name: secciones_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.secciones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: secciones; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.secciones (
    id integer DEFAULT nextval('public.secciones_id_seq'::regclass) NOT NULL,
    plan_id integer,
    asignatura_codigo character varying(20),
    seccion character varying(10) NOT NULL,
    profesor_id integer,
    sede_id integer,
    cupo_maximo integer DEFAULT 25,
    cupo_actual integer DEFAULT 0,
    aula character varying(50),
    periodo character varying(20),
    activa boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: sedes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sedes (
    id integer NOT NULL,
    nombre character varying(150) NOT NULL,
    fase_actual integer DEFAULT 1,
    ubicacion character varying(255),
    codigo character varying(20),
    activa boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT sedes_fase_actual_check CHECK ((fase_actual = ANY (ARRAY[1, 2])))
);


--
-- Name: sedes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.sedes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: sedes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.sedes_id_seq OWNED BY public.sedes.id;


--
-- Name: seq_matricula_estudiante; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.seq_matricula_estudiante
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: solicitudes_docentes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.solicitudes_docentes (
    id integer NOT NULL,
    coordinador_id integer,
    sede_id integer,
    tipo_documento character varying(5),
    numero_documento character varying(20) NOT NULL,
    nombres character varying(200) NOT NULL,
    apellidos character varying(200) NOT NULL,
    email character varying(255) NOT NULL,
    nacionalidad character varying(100),
    estatus character varying(20) DEFAULT 'Pendiente'::character varying,
    admin_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    resuelto_at timestamp without time zone,
    CONSTRAINT solicitudes_docentes_estatus_check CHECK (((estatus)::text = ANY ((ARRAY['Pendiente'::character varying, 'Aprobado'::character varying, 'Rechazado'::character varying])::text[]))),
    CONSTRAINT solicitudes_docentes_tipo_documento_check CHECK (((tipo_documento)::text = ANY ((ARRAY['V'::character varying, 'E'::character varying, 'P'::character varying])::text[])))
);


--
-- Name: solicitudes_docentes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.solicitudes_docentes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: solicitudes_docentes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.solicitudes_docentes_id_seq OWNED BY public.solicitudes_docentes.id;


--
-- Name: usuarios; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.usuarios (
    id integer NOT NULL,
    cedula character varying(20) NOT NULL,
    nombres character varying(100) CONSTRAINT usuarios_nombre_not_null NOT NULL,
    apellidos character varying(100) CONSTRAINT usuarios_apellido_not_null NOT NULL,
    email character varying(150) CONSTRAINT usuarios_correo_not_null NOT NULL,
    password character varying(255) NOT NULL,
    rol character varying(30) DEFAULT 'ASPIRANTE'::character varying NOT NULL,
    estatus_admision character varying(40) DEFAULT 'REGISTRADO'::character varying NOT NULL,
    matricula character varying(50) DEFAULT NULL::character varying,
    tipo_cedula character varying(5) DEFAULT 'V'::bpchar,
    rol_id integer DEFAULT 5,
    sede_id integer,
    estatus character varying(20) DEFAULT 'Activo'::character varying,
    sexo character varying(10),
    fecha_nacimiento date,
    nacionalidad character varying(100) DEFAULT 'Venezuela'::character varying,
    numero_documento character varying(20),
    estado_aspirante character varying(30) DEFAULT 'En Revision Digital'::character varying,
    telefono character varying(20),
    direccion text,
    fecha_registro timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_usuario_estatus CHECK (((estatus_admision)::text = ANY ((ARRAY['REGISTRADO'::character varying, 'VALIDADO_DOCS'::character varying, 'APROBADO'::character varying, 'RECHAZADO'::character varying])::text[]))),
    CONSTRAINT chk_usuario_rol CHECK (((rol)::text = ANY ((ARRAY['ASPIRANTE'::character varying, 'ESTUDIANTE'::character varying, 'DOCENTE'::character varying, 'SECRETARIA'::character varying, 'COORDINADOR'::character varying])::text[]))),
    CONSTRAINT usuarios_estado_aspirante_check CHECK (((estado_aspirante)::text = ANY ((ARRAY['En Revision Digital'::character varying, 'Con Observaciones'::character varying, 'Admitido'::character varying])::text[]))),
    CONSTRAINT usuarios_estatus_check CHECK (((estatus)::text = ANY ((ARRAY['Activo'::character varying, 'Inactivo'::character varying, 'Bloqueado'::character varying])::text[]))),
    CONSTRAINT usuarios_tipo_cedula_check CHECK (((tipo_cedula)::text = ANY ((ARRAY['V'::character varying, 'E'::character varying, 'P'::character varying])::text[])))
);


--
-- Name: usuarios_acceso; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.usuarios_acceso (
    id integer NOT NULL,
    username character varying(50) NOT NULL,
    password_hash text NOT NULL
);


--
-- Name: usuarios_acceso_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.usuarios_acceso_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: usuarios_acceso_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.usuarios_acceso_id_seq OWNED BY public.usuarios_acceso.id;


--
-- Name: usuarios_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.usuarios_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: usuarios_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.usuarios_id_seq OWNED BY public.usuarios.id;


--
-- Name: actas_log id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actas_log ALTER COLUMN id SET DEFAULT nextval('public.actas_log_id_seq'::regclass);


--
-- Name: actas_notas id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actas_notas ALTER COLUMN id SET DEFAULT nextval('public.actas_notas_id_seq'::regclass);


--
-- Name: asignaciones_docente id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asignaciones_docente ALTER COLUMN id SET DEFAULT nextval('public.asignaciones_docente_id_seq'::regclass);


--
-- Name: aspirante_documentos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.aspirante_documentos ALTER COLUMN id SET DEFAULT nextval('public.aspirante_documentos_id_seq'::regclass);


--
-- Name: autorizaciones_cnu id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.autorizaciones_cnu ALTER COLUMN id SET DEFAULT nextval('public.autorizaciones_cnu_id_seq'::regclass);


--
-- Name: baremo_preguntas id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.baremo_preguntas ALTER COLUMN id SET DEFAULT nextval('public.baremo_preguntas_id_seq'::regclass);


--
-- Name: cohortes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cohortes ALTER COLUMN id SET DEFAULT nextval('public.cohortes_id_seq'::regclass);


--
-- Name: creditos_resguardados id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.creditos_resguardados ALTER COLUMN id SET DEFAULT nextval('public.creditos_resguardados_id_seq'::regclass);


--
-- Name: horarios id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.horarios ALTER COLUMN id SET DEFAULT nextval('public.horarios_id_seq'::regclass);


--
-- Name: inscripciones id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inscripciones ALTER COLUMN id SET DEFAULT nextval('public.inscripciones_id_seq'::regclass);


--
-- Name: llaves_digitales id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.llaves_digitales ALTER COLUMN id SET DEFAULT nextval('public.llaves_digitales_id_seq'::regclass);


--
-- Name: logs_auditoria id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logs_auditoria ALTER COLUMN id SET DEFAULT nextval('public.logs_auditoria_id_seq'::regclass);


--
-- Name: mallas_curriculares id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.mallas_curriculares ALTER COLUMN id SET DEFAULT nextval('public.mallas_curriculares_id_seq'::regclass);


--
-- Name: materias id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.materias ALTER COLUMN id SET DEFAULT nextval('public.materias_id_seq'::regclass);


--
-- Name: notas id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notas ALTER COLUMN id SET DEFAULT nextval('public.notas_id_seq'::regclass);


--
-- Name: nucleos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.nucleos ALTER COLUMN id SET DEFAULT nextval('public.nucleos_id_seq'::regclass);


--
-- Name: pagos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pagos ALTER COLUMN id SET DEFAULT nextval('public.pagos_id_seq'::regclass);


--
-- Name: plan_asignaturas id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plan_asignaturas ALTER COLUMN id SET DEFAULT nextval('public.plan_asignaturas_id_seq'::regclass);


--
-- Name: plan_estudios id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plan_estudios ALTER COLUMN id SET DEFAULT nextval('public.plan_estudios_id_seq'::regclass);


--
-- Name: plan_sede id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plan_sede ALTER COLUMN id SET DEFAULT nextval('public.plan_sede_id_seq'::regclass);


--
-- Name: planes_estudio id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.planes_estudio ALTER COLUMN id SET DEFAULT nextval('public.planes_estudio_id_seq'::regclass);


--
-- Name: programas id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.programas ALTER COLUMN id SET DEFAULT nextval('public.programas_id_seq'::regclass);


--
-- Name: requisitos_preinscripcion id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.requisitos_preinscripcion ALTER COLUMN id SET DEFAULT nextval('public.requisitos_preinscripcion_id_seq'::regclass);


--
-- Name: respuestas_baremo id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.respuestas_baremo ALTER COLUMN id SET DEFAULT nextval('public.respuestas_baremo_id_seq'::regclass);


--
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- Name: sedes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sedes ALTER COLUMN id SET DEFAULT nextval('public.sedes_id_seq'::regclass);


--
-- Name: solicitudes_docentes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.solicitudes_docentes ALTER COLUMN id SET DEFAULT nextval('public.solicitudes_docentes_id_seq'::regclass);


--
-- Name: usuarios id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuarios ALTER COLUMN id SET DEFAULT nextval('public.usuarios_id_seq'::regclass);


--
-- Name: usuarios_acceso id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuarios_acceso ALTER COLUMN id SET DEFAULT nextval('public.usuarios_acceso_id_seq'::regclass);


--
-- Data for Name: actas_log; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.actas_log (id, acta_id, usuario_id, nota_anterior, nota_nueva, accion, created_at) FROM stdin;
\.


--
-- Data for Name: actas_notas; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.actas_notas (id, seccion_id, usuario_id, nota, inasistencia, estatus, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: asignaciones_docente; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.asignaciones_docente (id, docente_id, asignatura_codigo, cohorte_id, termino) FROM stdin;
\.


--
-- Data for Name: asignaturas; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.asignaturas (codigo, nombre, horas_teoricas, horas_practicas, uc, activa, created_at) FROM stdin;
EDE41013	SOCIEDAD, DERECHO Y EDUCACIÓN	3	0	3	t	2026-06-13 19:30:39.600306
EDE41023	JURISPRUDENCIA EDUCATIVA VENEZOLANA	3	0	3	t	2026-06-13 19:30:39.600306
EDE41033	SISTEMA EDUCATIVO VENEZOLANO Y SU MARCO LEGAL	3	0	3	t	2026-06-13 19:30:39.600306
EDPL41013	TEORÍA GENERAL DEL DERECHO DE TRABAJO	3	0	3	t	2026-06-13 19:30:39.600306
EEU41013	LA EXTENSIÓN UNIVERSITARIA EN VENEZUELA	3	0	3	t	2026-06-13 19:30:39.600306
AGA50003	ECOLOGÍA	3	0	3	t	2026-06-13 19:30:39.600306
AGG50013	FORMACIÓN GERENCIAL	3	0	3	t	2026-06-13 19:30:39.600306
MCJ60113	FILOSOFÍA DEL DERECHO	3	0	3	t	2026-06-13 19:30:39.600306
MCJ60123	METODOLOGÍA DE LA INVESTIGACIÓN JURÍDICA	3	0	3	t	2026-06-13 19:30:39.600306
MJM60113	DERECHO PENAL MILITAR	3	0	3	t	2026-06-13 19:30:39.600306
MES60113	CURRÍCULO EN LA EDUCACIÓN SUPERIOR	3	0	3	t	2026-06-13 19:30:39.600306
MEC60113	PROPIEDADES DE LOS FLUIDOS	3	0	3	t	2026-06-13 19:30:39.600306
MGA60113	PLANIFICACIÓN, POLÍTICA Y ORDENACIÓN AMBIENTAL	3	0	3	t	2026-06-13 19:30:39.600306
TGM60006	TRABAJO DE GRADO (MAESTRÍA)	0	0	6	t	2026-06-13 19:30:39.600306
TIC60113	ARQUITECTURA DE TECNOLOGÍAS DE INFORMACIÓN	3	0	3	t	2026-06-13 19:30:39.600306
TIC60223	AUDITORÍA Y GOBERNANZA DE TI	3	0	3	t	2026-06-13 19:30:39.600306
GMN60113	INGENIERÍA DE MANTENIMIENTO AVANZADA	3	0	3	t	2026-06-13 19:30:39.600306
GMN60223	AUDITORÍA DE SISTEMAS DE MANTENIMIENTO	3	0	3	t	2026-06-13 19:30:39.600306
GRH60113	PLANIFICACIÓN ESTRATÉGICA DE RRHH	3	0	3	t	2026-06-13 19:30:39.600306
GRH60223	DESARROLLO ORGANIZACIONAL Y CAMBIO	3	0	3	t	2026-06-13 19:30:39.600306
DSO70113	EPISTEMOLOGÍA DE LAS CIENCIAS SOCIALES	3	0	3	t	2026-06-13 19:30:39.600306
SDI70113	TEORÍA DE LA SEGURIDAD Y DESARROLLO INTEGRAL	3	0	3	t	2026-06-13 19:30:39.600306
TDD70010	TESIS DOCTORAL	0	0	10	t	2026-06-13 19:30:39.600306
CGE70113	EPISTEMOLOGÍA DE LA GERENCIA	3	0	3	t	2026-06-13 19:30:39.600306
CGE70223	PROSPECTIVA Y ESTRATEGIA GERENCIAL	3	0	3	t	2026-06-13 19:30:39.600306
GAM70113	DESARROLLO SUSTENTABLE Y COMPLEJIDAD AMBIENTAL	3	0	3	t	2026-06-13 19:30:39.600306
GAM70223	VALORACIÓN ECONÓMICA DE BIENES Y SERVICIOS AMBIENTALES	3	0	3	t	2026-06-13 19:30:39.600306
IED70113	EPISTEMOLOGÍA DE LA EDUCACIÓN Y LA INNOVACIÓN	3	0	3	t	2026-06-13 19:30:39.600306
IED70223	TECNOLOGÍAS EMERGENTES EN ENTORNOS EDUCATIVOS	3	0	3	t	2026-06-13 19:30:39.600306
AGG30013	ADMINISTRACIÓN Y GERENCIA	0	0	0	t	2026-06-13 19:30:39.600306
CJU30013	LEGISLACIÓN LABORAL VENEZOLANA	0	0	0	t	2026-06-13 19:30:39.600306
GRH30013	GESTIÓN DE RECURSOS HUMANOS	0	0	0	t	2026-06-13 19:30:39.600306
ADG30013	SEGURIDAD, DEFENSA Y DESARROLLO	0	0	0	t	2026-06-13 19:30:39.600306
GRH51123	COMPORTAMIENTO ORGANIZACIONAL	0	0	0	t	2026-06-13 19:30:39.600306
GRH51143	LA SEGURIDAD SOCIAL INTEGRAL EN VENEZUELA	0	0	0	t	2026-06-13 19:30:39.600306
SYC30013	INFORMÁTICA PARA EL ÁREA DE RECURSOS HUMANOS	0	0	0	t	2026-06-13 19:30:39.600306
ADG30023	EL CAPITAL INTELECTUAL Y LA GESTIÓN DEL CONOCIMIENTO	0	0	0	t	2026-06-13 19:30:39.600306
TTP30013	PASANTIA	0	0	0	t	2026-06-13 19:30:39.600306
ETDA3001	LEGISLACIÓN AÉREA ESPACIAL	0	0	0	t	2026-06-13 19:30:39.600306
ETDA3002	SISTEMA DE LA DEFENSA AÉREA	0	0	0	t	2026-06-13 19:30:39.600306
AGD30013	SEGURIDAD, DEFENSA Y DESARROLLO	0	0	0	t	2026-06-13 19:30:39.600306
ETDA3003	DOCTRINA Y EMPLEO DE LA FUERZA AÉREA	0	0	0	t	2026-06-13 19:30:39.600306
ETDA3004	PROCESOS LOGÍSTICOS	0	0	0	t	2026-06-13 19:30:39.600306
ETDA3005	GUERRA ELECTRÓNICA	0	0	0	t	2026-06-13 19:30:39.600306
ETDA3006	CONTROL DE TRANSITO AÉREO E INTERCEPCIONES	0	0	0	t	2026-06-13 19:30:39.600306
TTP30003	PASANTÍA-INFORME FINAL	0	0	0	t	2026-06-13 19:30:39.600306
ETTF3113	SISTEMA FERROVIARIO	0	0	0	t	2026-06-13 19:30:39.600306
ETTF3123	MANTENIMIENTO DE INFRAESTRUCTURA Y SUPERESTRUCTURA FERROVIARIA	0	0	0	t	2026-06-13 19:30:39.600306
ETTF3133	COMPONENTES Y MANTENIMIENTO DEL SISTEMA INTEGRAL FERROVIARIO	0	0	0	t	2026-06-13 19:30:39.600306
ETTF3243	EXPLOTACIÓN DEL SISTEMA FERROVIARIO	0	0	0	t	2026-06-13 19:30:39.600306
ETTF3153	SEÑALIZACIÓN EN LA RED DEL TRANSPORTE FERROVIARIO	0	0	0	t	2026-06-13 19:30:39.600306
ETTF3263	SEGURIDAD FERROVIARIA	0	0	0	t	2026-06-13 19:30:39.600306
ETTF3373	PASANTIA	0	0	0	t	2026-06-13 19:30:39.600306
MAT50013	TÉCNICAS CUANTITATIVAS DE GESTIÓN	0	0	0	t	2026-06-13 19:30:39.600306
AGM51113	GERENCIA DE MANTENIMIENTO	0	0	0	t	2026-06-13 19:30:39.600306
AGM51212	GERENCIA DE CALIDAD Y NORMALIZACIÓN	0	0	0	t	2026-06-13 19:30:39.600306
ADG50013	FORMACIÓN DE COMPETENCIAS PARA LA INVESTIGACIÓN	0	0	0	t	2026-06-13 19:30:39.600306
AGL51133	GERENCIA FINANCIERA DE LA EMPRESA	0	0	0	t	2026-06-13 19:30:39.600306
AGM51133	PLANIFICACIÓN DEL MANTENIMIENTO	0	0	0	t	2026-06-13 19:30:39.600306
TEG40004	TRABAJO ESPECIAL DE GRADO	0	0	0	t	2026-06-13 19:30:39.600306
DPOL4013	DERECHO PROCESAL LABORAL I	0	0	0	t	2026-06-13 19:30:39.600306
DPOL4023	DERECHO PROCESAL LABORAL II	0	0	0	t	2026-06-13 19:30:39.600306
DPOL4033	DERECHO COLECTIVO DEL TRABAJO	0	0	0	t	2026-06-13 19:30:39.600306
DPOL4053	PRÁCTICA PROCESAL LABORAL	0	0	0	t	2026-06-13 19:30:39.600306
EEU40113	FUNDAMENTOS DE LA EXTENSIÓN UNIVERSITARIA	0	0	0	t	2026-06-13 19:30:39.600306
EEU40123	GESTIÓN DE PROYECTOS DE EXTENSIÓN	0	0	0	t	2026-06-13 19:30:39.600306
EEU40133	VINCULACIÓN COMUNITARIA Y GESTIÓN SOCIAL	0	0	0	t	2026-06-13 19:30:39.600306
EEU40153	EVALUACIÓN DE IMPACTO SOCIAL	0	0	0	t	2026-06-13 19:30:39.600306
GAM40113	LEGISLACIÓN Y GESTIÓN AMBIENTAL	0	0	0	t	2026-06-13 19:30:39.600306
GAM40123	TECNOLOGÍAS LIMPIAS	0	0	0	t	2026-06-13 19:30:39.600306
GAM40133	EVALUACIÓN DE IMPACTO AMBIENTAL	0	0	0	t	2026-06-13 19:30:39.600306
GAM40153	ECONOMÍA AMBIENTAL	0	0	0	t	2026-06-13 19:30:39.600306
MCJ60133	DERECHO CONSTITUCIONAL COMPARADO	0	0	0	t	2026-06-13 19:30:39.600306
MCJ60213	TEORÍA GENERAL DEL DELITO	0	0	0	t	2026-06-13 19:30:39.600306
MCJ60223	RÉGIMEN JURÍDICO DE LA ADMINISTRACIÓN PÚBLICA	0	0	0	t	2026-06-13 19:30:39.600306
MCJ60313	DERECHO CIVIL AVANZADO	0	0	0	t	2026-06-13 19:30:39.600306
MCJ60323	SEMINARIO DE TRABAJO DE GRADO I	0	0	0	t	2026-06-13 19:30:39.600306
MCJ60413	SEMINARIO DE TRABAJO DE GRADO II	0	0	0	t	2026-06-13 19:30:39.600306
MJM60123	DERECHO PROCESAL PENAL MILITAR	0	0	0	t	2026-06-13 19:30:39.600306
MJM60133	LEGISLACIÓN MILITAR VENEZOLANA Y ORGANIZACIÓN DE LA FANB	0	0	0	t	2026-06-13 19:30:39.600306
MJM60213	DERECHO INTERNACIONAL HUMANITARIO APLICADO	0	0	0	t	2026-06-13 19:30:39.600306
MJM60223	SEGURIDAD, DEFENSA INTEGRAL Y DESARROLLO	0	0	0	t	2026-06-13 19:30:39.600306
MJM60313	CRIMINALÍSTICA Y PRÁCTICA FORENSE MILITAR	0	0	0	t	2026-06-13 19:30:39.600306
MES60123	TEORÍAS DEL APRENDIZAJE EN LA EDUCACIÓN SUPERIOR	0	0	0	t	2026-06-13 19:30:39.600306
MES60133	METODOLOGÍA DE LA INVESTIGACIÓN EDUCATIVA	0	0	0	t	2026-06-13 19:30:39.600306
MES60213	EVALUACIÓN DE LOS APRENDIZAGES UNIVERSITARIOS	0	0	0	t	2026-06-13 19:30:39.600306
MES60223	PLANIFICACIÓN ESTRATÉGICA INSTITUCIONAL	0	0	0	t	2026-06-13 19:30:39.600306
MES60313	TECNOLOGÍAS DE LA INFORMACIÓN Y COMUNICACIÓN EN EDUCACIÓN	0	0	0	t	2026-06-13 19:30:39.600306
MEC60123	CARACTERIZACIÓN DE YACIMIENTOS DE CRUDO PESADO	0	0	0	t	2026-06-13 19:30:39.600306
MEC60133	MÉTODOS TÉRMICOS DE RECUPERACIÓN MEJORADA	0	0	0	t	2026-06-13 19:30:39.600306
MEC60213	MÉTODOS NO TÉRMICOS DE RECUPERACIÓN AVANZADA	0	0	0	t	2026-06-13 19:30:39.600306
MEC60223	LEVANTAMIENTO ARTIFICIAL DE CRUDOS PESADOS Y EXTRAPESADOS	0	0	0	t	2026-06-13 19:30:39.600306
MEC60313	TRANSPORTE, MANEJO Y REOLOGÍA DE CRUDOS PESADOS	0	0	0	t	2026-06-13 19:30:39.600306
MGA60123	GESTIÓN SOSTENIBLE DE RECURSOS NATURALES	0	0	0	t	2026-06-13 19:30:39.600306
MGA60133	ECONOMÍA AMBIENTAL, ECOLÓGICA Y SOCIAL	0	0	0	t	2026-06-13 19:30:39.600306
MGA60213	LEGISLACIÓN E INSTITUCIONES AMBIENTALES NACIONALES	0	0	0	t	2026-06-13 19:30:39.600306
MGA60223	EVALUACIÓN DEL IMPACTO AMBIENTAL AVANZADO	0	0	0	t	2026-06-13 19:30:39.600306
MGA60313	SISTEMAS DE GESTIÓN AMBIENTAL E ISO 14001	0	0	0	t	2026-06-13 19:30:39.600306
COD_MAT_1	NOMBRE_MATERIA_1	0	0	0	t	2026-06-13 19:30:39.600306
TIC60123	GERENCIA DE PROYECTOS DE TELECOMUNICACIONES	0	0	0	t	2026-06-13 19:30:39.600306
TIC60133	SEGURIDAD DE LA INFORMACIÓN Y REDES	0	0	0	t	2026-06-13 19:30:39.600306
TIC60213	PLANIFICACIÓN ESTRATÉGICA DE TIC	0	0	0	t	2026-06-13 19:30:39.600306
TIC60313	SISTEMAS DE INFORMACIÓN EMPRESARIALES	0	0	0	t	2026-06-13 19:30:39.600306
TIC60323	SEMINARIO DE TRABAJO DE GRADO I (TIC)	0	0	0	t	2026-06-13 19:30:39.600306
TIC60413	SEMINARIO DE TRABAJO DE GRADO II (TIC)	0	0	0	t	2026-06-13 19:30:39.600306
GMN60123	LOGÍSTICA DEL MANTENIMIENTO Y REPUESTOS	0	0	0	t	2026-06-13 19:30:39.600306
GMN60133	SISTEMAS DE INFORMACIÓN PARA MANTENIMIENTO	0	0	0	t	2026-06-13 19:30:39.600306
GMN60213	COSTOS Y PRESUPUESTOS DE MANTENIMIENTO	0	0	0	t	2026-06-13 19:30:39.600306
GMN60313	CONFIABILIDAD OPERACIONAL Y MANTENIBILIDAD	0	0	0	t	2026-06-13 19:30:39.600306
GMN60323	SEMINARIO DE TRABAJO DE GRADO I (MANTENIMIENTO)	0	0	0	t	2026-06-13 19:30:39.600306
GMN60413	SEMINARIO DE TRABAJO DE GRADO II (MANTENIMIENTO)	0	0	0	t	2026-06-13 19:30:39.600306
GRH60123	COMPORTAMIENTO ORGANIZACIONAL AVANZADO	0	0	0	t	2026-06-13 19:30:39.600306
GRH60133	LEGISLACIÓN LABORAL Y SINDICAL COMPRENSIVA	0	0	0	t	2026-06-13 19:30:39.600306
GRH60213	GERENCIA DEL DESEMPEÑO Y COMPENSACIÓN	0	0	0	t	2026-06-13 19:30:39.600306
GRH60313	CULTURA, ÉTICA Y RESPONSABILIDAD SOCIAL	0	0	0	t	2026-06-13 19:30:39.600306
GRH60323	SEMINARIO DE TRABAJO DE GRADO I (RRHH)	0	0	0	t	2026-06-13 19:30:39.600306
GRH60413	SEMINARIO DE TRABAJO DE GRADO II (RRHH)	0	0	0	t	2026-06-13 19:30:39.600306
DSO70123	TEORÍAS DEL DESARROLLO SOCIAL Y HUMANO	0	0	0	t	2026-06-13 19:30:39.600306
DSO70133	METODOLOGÍA AVANZADA DE INVESTIGACIÓN SOCIAL	0	0	0	t	2026-06-13 19:30:39.600306
DSO70213	POLÍTICAS PÚBLICAS Y BIENESTAR SOCIAL	0	0	0	t	2026-06-13 19:30:39.600306
DSO70223	GLOBALIZACIÓN, GEOPOLÍTICA Y EXCLUSIÓN SOCIAL	0	0	0	t	2026-06-13 19:30:39.600306
DSO70313	SEMINARIO DE TESIS DOCTORAL I	0	0	0	t	2026-06-13 19:30:39.600306
DSO70413	SEMINARIO DE TESIS DOCTORAL II	0	0	0	t	2026-06-13 19:30:39.600306
SDI70123	PENSAMIENTO ESTRATÉGICO Y GEOPOLÍTICA CRÍTICA	0	0	0	t	2026-06-13 19:30:39.600306
SDI70133	METODOLOGÍA DE LA INVESTIGACIÓN EN SEGURIDAD INTEGRAL	0	0	0	t	2026-06-13 19:30:39.600306
SDI70213	AMENAZAS, RIESGOS Y VULNERABILIDADES DE LA NACIÓN	0	0	0	t	2026-06-13 19:30:39.600306
SDI70223	SOBERANÍA, IDENTIDAD Y PODER NACIONAL	0	0	0	t	2026-06-13 19:30:39.600306
SDI70313	SEMINARIO DE TESIS DOCTORAL I (SEGURIDAD)	0	0	0	t	2026-06-13 19:30:39.600306
SDI70413	SEMINARIO DE TESIS DOCTORAL II (SEGURIDAD)	0	0	0	t	2026-06-13 19:30:39.600306
CGE70123	TEORÍAS GERENCIALES CONTEMPORÁNEAS	0	0	0	t	2026-06-13 19:30:39.600306
CGE70133	METODOLOGÍA DE LA INVESTIGACIÓN GERENCIAL	0	0	0	t	2026-06-13 19:30:39.600306
CGE70213	GERENCIA DE LAS ORGANIZACIONES EN ENTORNOS COMPLEJOS	0	0	0	t	2026-06-13 19:30:39.600306
CGE70313	SEMINARIO DE TESIS DOCTORAL I (GERENCIA)	0	0	0	t	2026-06-13 19:30:39.600306
CGE70413	SEMINARIO DE TESIS DOCTORAL II (GERENCIA)	0	0	0	t	2026-06-13 19:30:39.600306
GAM70123	EPISTEMOLOGÍA Y POLÍTICAS AMBIENTALES	0	0	0	t	2026-06-13 19:30:39.600306
GAM70133	METODOLOGÍA DE INVESTIGACIÓN AMBIENTAL AVANZADA	0	0	0	t	2026-06-13 19:30:39.600306
GAM70213	GERENCIA ESTRATÉGICA DE ECOSISTEMAS	0	0	0	t	2026-06-13 19:30:39.600306
GAM70313	SEMINARIO DE TESIS DOCTORAL I (AMBIENTAL)	0	0	0	t	2026-06-13 19:30:39.600306
GAM70413	SEMINARIO DE TESIS DOCTORAL II (AMBIENTAL)	0	0	0	t	2026-06-13 19:30:39.600306
IED70123	TEORÍAS Y MODELOS EDUCATIVOS CONTEMPORÁNEOS	0	0	0	t	2026-06-13 19:30:39.600306
IED70133	METODOLOGÍA DE LA INVESTIGACIÓN EDUCATIVA AVANZADA	0	0	0	t	2026-06-13 19:30:39.600306
IED70213	DISEÑO Y GERENCIA DE PROYECTOS EDUCATIVOS INNOVADORES	0	0	0	t	2026-06-13 19:30:39.600306
IED70313	SEMINARIO DE TESIS DOCTORAL I (EDUCACIÓN)	0	0	0	t	2026-06-13 19:30:39.600306
IED70413	SEMINARIO DE TESIS DOCTORAL II (EDUCACIÓN)	0	0	0	t	2026-06-13 19:30:39.600306
\.


--
-- Data for Name: aspirante_documentos; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.aspirante_documentos (id, usuario_id, tipo, archivo_ruta, archivo_nombre, verificado, observaciones, created_at) FROM stdin;
\.


--
-- Data for Name: autorizaciones_cnu; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.autorizaciones_cnu (id, nucleo_id, programa_id, cod_opcion, codigo_cnu, observacion_memorando, observacion_gaceta) FROM stdin;
\.


--
-- Data for Name: baremo_preguntas; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.baremo_preguntas (id, pregunta, categoria, orden) FROM stdin;
1	Participaci\\u00f3n en eventos cient\\u00edficos nacionales e internacionales.	Academico	1
2	Participaci\\u00f3n como jurado o tutor en trabajos de investigaci\\u00f3n.	Academico	2
3	Disposici\\u00f3n a participar en actividad acad\\u00e9micas, investigaci\\u00f3n e institucionales.	Academico	3
4	Tema de inter\\u00e9s espec\\u00edfica para su investigaci\\u00f3n vinculada a la naci\\u00f3n.	Investigacion	4
5	Vinculaci\\u00f3n entre el \\u00e1rea profesional con los estudios de postgrado.	Investigacion	5
6	Afiliaci\\u00f3n a grupo o red de investigadores.	Investigacion	6
7	Participaci\\u00f3n como evaluador en art\\u00edculos cient\\u00edficos.	Investigacion	7
8	Ha escrito o publicado art\\u00edculos cient\\u00edficos (Opci\\u00f3n A).	Investigacion	8
9	Ha escrito o publicado art\\u00edculos cient\\u00edficos (Opci\\u00f3n B).	Investigacion	9
10	Familiarizaci\\u00f3n con las l\\u00edneas de investigaci\\u00f3n de la Universidad.	Investigacion	10
11	La investigaci\\u00f3n satisface fines personales o institucionales.	Investigacion	11
12	Acceso y disponibilidad al manejo de equipos tecnol\\u00f3gicos.	Otros	12
13	Disponibilidad personal para financiar los estudios (Opci\\u00f3n A).	Otros	13
14	Disponibilidad personal para financiar los estudios (Opci\\u00f3n B).	Otros	14
\.


--
-- Data for Name: cohortes; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.cohortes (id, programa_id, nucleo_id, codigo_cohorte, fecha_inicio) FROM stdin;
\.


--
-- Data for Name: creditos_resguardados; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.creditos_resguardados (id, usuario_id, sede_origen_id, sede_destino_id, uc_resguardadas, monto_resguardado, motivo, estatus, created_at, aplicado_at) FROM stdin;
\.


--
-- Data for Name: horarios; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.horarios (id, seccion_id, dia_semana, hora_inicio, hora_fin) FROM stdin;
\.


--
-- Data for Name: inscripciones; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.inscripciones (id, usuario_id, cohorte_id, termino, estatus_pago, updated_at, seccion_id, estatus) FROM stdin;
\.


--
-- Data for Name: llaves_digitales; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.llaves_digitales (id, tipo, solicitante_id, admin_id, destino_id, estatus, validez_horas, created_at, expira_at, used_at) FROM stdin;
\.


--
-- Data for Name: logs_auditoria; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.logs_auditoria (id, usuario_id, accion, entidad, entidad_id, detalle, direccion_ip, created_at) FROM stdin;
1	4	Inicio de sesión	usuarios	4	Usuario Coordinador Academico inició sesión	::1	2026-06-13 19:54:03.009234
2	7	Inicio de sesión	usuarios	7	Usuario Secretaria Control Estudios inició sesión	::1	2026-06-13 20:08:08.047382
3	2	Inicio de sesión	usuarios	2	Usuario Admin Sistema inició sesión	::1	2026-06-13 20:25:04.852054
4	3	Inicio de sesión	usuarios	3	Usuario Director Principal inició sesión	::1	2026-06-13 20:27:58.016852
5	7	Inicio de sesión	usuarios	7	Usuario Secretaria Control Estudios inició sesión	::1	2026-06-13 20:29:09.790671
6	8	Inicio de sesión	usuarios	8	Usuario Docente Planta inició sesión	::1	2026-06-13 20:30:23.090848
7	9	Inicio de sesión	usuarios	9	Usuario Aspirante Nuevo inició sesión	::1	2026-06-13 20:31:24.069561
\.


--
-- Data for Name: mallas_curriculares; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.mallas_curriculares (id, id_programa, id_materia, periodo, unidades_credito, es_electiva) FROM stdin;
1	6	EDE41013	1	3	f
2	1	AGG30013	1	3	f
3	1	CJU30013	1	3	f
4	1	GRH30013	1	3	f
5	1	ADG30013	2	3	f
6	1	GRH51123	2	3	f
7	1	GRH51143	2	3	f
8	1	SYC30013	3	3	f
9	1	ADG30023	3	3	f
10	1	TTP30013	4	3	f
11	2	GRH30013	1	3	f
12	2	ETDA3001	1	3	f
13	2	ETDA3002	1	3	f
14	2	AGD30013	2	3	f
15	2	ETDA3003	2	3	f
16	2	ETDA3004	2	3	f
17	2	ETDA3005	3	3	f
18	2	ETDA3006	3	3	f
19	2	TTP30003	4	3	f
20	3	ETTF3113	1	3	f
21	3	ETTF3123	1	3	f
22	3	ETTF3133	1	3	f
23	3	ETTF3243	2	3	f
24	3	ETTF3153	2	3	f
25	3	ETTF3263	3	3	f
26	3	ETTF3373	4	3	f
49	10	AGG50013	1	3	f
50	10	MAT50013	1	3	f
51	10	AGM51113	1	3	f
52	10	AGM51212	2	3	f
53	10	ADG50013	2	3	f
54	10	AGL51133	3	3	f
55	10	AGM51133	3	3	f
56	10	TEG40004	4	4	f
57	7	DPOL4013	1	3	f
58	7	DPOL4023	1	3	f
59	7	DPOL4033	2	3	f
60	7	ADG50013	2	3	f
61	7	DPOL4053	3	3	f
62	7	TEG40004	4	4	f
63	8	EEU40113	1	3	f
64	8	EEU40123	1	3	f
65	8	EEU40133	2	3	f
66	8	ADG50013	2	3	f
67	8	EEU40153	3	3	f
68	8	TEG40004	4	4	f
69	9	GAM40113	1	3	f
70	9	GAM40123	1	3	f
71	9	GAM40133	2	3	f
72	9	ADG50013	2	3	f
73	9	GAM40153	3	3	f
74	9	TEG40004	4	4	f
101	11	MCJ60113	1	3	f
102	11	MCJ60123	1	3	f
103	11	MCJ60133	1	3	f
104	11	MCJ60213	2	3	f
105	11	MCJ60223	2	3	f
106	11	MCJ60313	3	3	f
107	11	MCJ60323	3	3	f
108	11	MCJ60413	4	3	f
109	11	TGM60006	4	6	f
110	12	MJM60113	1	3	f
111	12	MJM60123	1	3	f
112	12	MJM60133	1	3	f
113	12	MJM60213	2	3	f
114	12	MJM60223	2	3	f
115	12	MJM60313	3	3	f
116	12	TGM60006	4	6	f
117	13	MES60113	1	3	f
118	13	MES60123	1	3	f
119	13	MES60133	1	3	f
120	13	MES60213	2	3	f
121	13	MES60223	2	3	f
122	13	MES60313	3	3	f
123	13	TGM60006	4	6	f
124	14	MEC60113	1	3	f
125	14	MEC60123	1	3	f
126	14	MEC60133	1	3	f
127	14	MEC60213	2	3	f
128	14	MEC60223	2	3	f
129	14	MEC60313	3	3	f
130	14	TGM60006	4	6	f
131	15	MGA60113	1	3	f
132	15	MGA60123	1	3	f
133	15	MGA60133	1	3	f
134	15	MGA60213	2	3	f
135	15	MGA60223	2	3	f
136	15	MGA60313	3	3	f
137	15	TGM60006	4	6	f
138	16	COD_MAT_1	1	3	f
139	16	TIC60113	1	3	f
140	16	TIC60123	1	3	f
141	16	TIC60133	1	3	f
142	16	TIC60213	2	3	f
143	16	TIC60223	2	3	f
144	16	TIC60313	3	3	f
145	16	TIC60323	3	3	f
146	16	TIC60413	4	3	f
147	16	TGM60006	4	6	f
148	17	GMN60113	1	3	f
149	17	GMN60123	1	3	f
150	17	GMN60133	1	3	f
151	17	GMN60213	2	3	f
152	17	GMN60223	2	3	f
153	17	GMN60313	3	3	f
154	17	GMN60323	3	3	f
155	17	GMN60413	4	3	f
156	17	TGM60006	4	6	f
157	18	GRH60113	1	3	f
158	18	GRH60123	1	3	f
159	18	GRH60133	1	3	f
160	18	GRH60213	2	3	f
161	18	GRH60223	2	3	f
162	18	GRH60313	3	3	f
163	18	GRH60323	3	3	f
164	18	GRH60413	4	3	f
165	18	TGM60006	4	6	f
166	19	DSO70113	1	3	f
167	19	DSO70123	1	3	f
168	19	DSO70133	1	3	f
169	19	DSO70213	2	3	f
170	19	DSO70223	2	3	f
171	19	DSO70313	3	3	f
172	19	DSO70413	4	3	f
173	19	TDD70010	4	10	f
174	20	SDI70113	1	3	f
175	20	SDI70123	1	3	f
176	20	SDI70133	1	3	f
177	20	SDI70213	2	3	f
178	20	SDI70223	2	3	f
179	20	SDI70313	3	3	f
180	20	SDI70413	4	3	f
181	20	TDD70010	4	10	f
182	21	CGE70113	1	3	f
183	21	CGE70123	1	3	f
184	21	CGE70133	1	3	f
185	21	CGE70213	2	3	f
186	21	CGE70223	2	3	f
187	21	CGE70313	3	3	f
188	21	CGE70413	4	3	f
189	21	TDD70010	4	10	f
190	22	GAM70113	1	3	f
191	22	GAM70123	1	3	f
192	22	GAM70133	1	3	f
193	22	GAM70213	2	3	f
194	22	GAM70223	2	3	f
195	22	GAM70313	3	3	f
196	22	GAM70413	4	3	f
197	22	TDD70010	4	10	f
198	23	IED70113	1	3	f
199	23	IED70123	1	3	f
200	23	IED70133	1	3	f
201	23	IED70213	2	3	f
202	23	IED70223	2	3	f
203	23	IED70313	3	3	f
204	23	IED70413	4	3	f
205	23	TDD70010	4	10	f
\.


--
-- Data for Name: materias; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.materias (id, codigo, nombre, horas_teoricas, horas_practicas) FROM stdin;
\.


--
-- Data for Name: notas; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.notas (id, inscripcion_id, asignatura_codigo, nota_final) FROM stdin;
\.


--
-- Data for Name: nucleos; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.nucleos (id, nombre, fase_actual) FROM stdin;
2	ANZOÁTEGUI - SAN TOMÉ (SEDE)	1
4	ARAGUA - MARACAY (SEDE)	1
12	DISTRITO CAPITAL - CARACAS (SEDE)	1
50	FALCÓN - CORO (SEDE)	1
52	NUEVA ESPARTA - PAMPATAR (SEDE)	1
56	PTO. CABELLO  (SEDE)	1
59	SUCRE - CUMANA (SEDE)	1
60	TÁCHIRA - SAN CRISTÓBAL (SEDE)	1
65	YARACUY - SAN FELIPE (SEDE)	1
\.


--
-- Data for Name: pagos; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.pagos (id, usuario_id, referencia_bancaria, monto, tipo_pago, estatus, inscripcion_id, banco, referencia, fecha_pago, secretaria_id) FROM stdin;
\.


--
-- Data for Name: plan_asignaturas; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.plan_asignaturas (id, plan_id, asignatura_codigo, semestre, obligatoria) FROM stdin;
\.


--
-- Data for Name: plan_estudios; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.plan_estudios (id, nombre, tipo, codigo, activo, created_at, updated_at) FROM stdin;
1	Maestría en Gerencia Logística	Maestria	MGL-2026	t	2026-06-13 19:29:47.849706	2026-06-13 19:29:47.849706
2	Maestría en Educación Superior	Maestria	MES-2026	t	2026-06-13 19:29:47.849706	2026-06-13 19:29:47.849706
\.


--
-- Data for Name: plan_sede; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.plan_sede (id, plan_id, sede_id) FROM stdin;
\.


--
-- Data for Name: planes_estudio; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.planes_estudio (id, programa_id, asignatura_codigo, termino, tipo_materia) FROM stdin;
\.


--
-- Data for Name: programas; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.programas (id, nombre, acreditable, grado_academico, regimen, vigencia, tipo) FROM stdin;
6	ESPECIALIZACIÓN EN DERECHO EDUCATIVO	No	Especialización	Nocturno	2010	Especialización
7	ESPECIALIZACIÓN EN DERECHO PROCESAL LABORAL	No	Especialización	Nocturno	2010	Especialización
8	ESPECIALIZACIÓN EN EXTENSIÓN UNIVERSITARIA	No	Especialización	Nocturno	2010	Especialización
9	ESPECIALIZACIÓN EN GERENCIA AMBIENTAL	No	Especialización	Nocturno	2010	Especialización
10	ESPECIALIZACIÓN EN GERENCIA DE MANTENIMIENTO	No	Especialización	Nocturno	2010	Especialización
11	MAESTRÍA EN CIENCIAS JURÍDICAS	No	Maestría	Nocturno	2010	Maestría
12	MAESTRÍA EN CIENCIAS JURÍDICAS MILITARES	No	Maestría	Nocturno	2010	Maestría
13	MAESTRÍA EN EDUCACIÓN SUPERIOR	No	Maestría	Nocturno	2010	Maestría
14	MAESTRÍA EN EXTRACCIÓN DE CRUDOS PESADOS	No	Maestría	Nocturno	2010	Maestría
15	MAESTRÍA EN GERENCIA AMBIENTAL	No	Maestría	Nocturno	2010	Maestría
16	MAESTRÍA EN GERENCIA DE LAS TIC	No	Maestría	Nocturno	2010	Maestría
17	MAESTRÍA EN GERENCIA DE MANTENIMIENTO	No	Maestría	Nocturno	2010	Maestría
18	MAESTRÍA EN GERENCIA DE RECURSOS HUMANOS	No	Maestría	Nocturno	2010	Maestría
19	DOCTORADO EN DESARROLLO SOCIAL	No	Doctorado	Nocturno	2010	Doctorado
20	DOCTORADO EN SEGURIDAD Y DESARROLLO INTEGRAL	No	Doctorado	Nocturno	2010	Doctorado
21	DOCTORADO EN CIENCIAS GERENCIALES	No	Doctorado	Nocturno	2010	Doctorado
22	DOCTORADO EN GERENCIA AMBIENTAL	No	Doctorado	Nocturno	2013	Doctorado
23	DOCTORADO EN INNOVACIONES EDUCATIVAS	No	Doctorado	Nocturno	2010	Doctorado
1	ESPECIALIZACIÓN TÉCNICA EN ADMINISTRACIÓN DE RECURSOS HUMANOS	\N	\N	\N	\N	\N
2	ESPECIALIZACIÓN TÉCNICA EN DEFENSA AÉREA	\N	\N	\N	\N	\N
3	ESPECIALIZACIÓN TÉCNICA EN TRANSPORTE FERROVIARIO	\N	\N	\N	\N	\N
\.


--
-- Data for Name: programas_postgrado; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.programas_postgrado (id, cod_opcion, nucleo_extension, programa, acreditable, observacion_1, observacion_2, codigo_cnu, grado_academico) FROM stdin;
1	02ETP	ANZOÁTEGUI - SAN TOMÉ (SEDE)	PRODUCCIÓN DE CEMENTO	ESPECIALIZACIÓN	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 39.904 DE FECHA 17/04/2012	3160	ESPECIALISTA
2	02MDP	ANZOÁTEGUI - SAN TOMÉ (SEDE)	GERENCIA DE MANTENIMIENTO	MAESTRÍA	\N	NO EVALUADO	187	MAGISTER SCIENTIARUM
3	05EUP	ARAGUA - MARACAY (SEDE)	DEFENSA AÉREA	ESPECIALIZACIÓN TÉCNICA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 39.240 DE FECHA 12/08/2009	180	MAGISTER SCIENTIARUM
4	05EEP	ARAGUA - MARACAY (SEDE)	GERENCIA PÚBLICA	ESPECIALIZACIÓN	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 5.585 DE FECHA 28/04/2002	2845	TÉCNICO SUPERIOR ESPECIALISTA
5	05EGP	ARAGUA - MARACAY (SEDE)	GERENCIA DE TELECOMUNICACIONES	ESPECIALIZACIÓN	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 38.344 DE FECHA 27/12/2005	1760	ESPECIALISTA
6	05MAP	ARAGUA - MARACAY (SEDE)	GERENCIA AMBIENTAL	MAESTRÍA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 38.508 DE FECHA 25/08/2006	2453	ESPECIALISTA
7	05MCP	ARAGUA - MARACAY (SEDE)	GERENCIA LOGÍSTICA	MAESTRÍA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 5.585 DE FECHA 28/04/2002	2458	MAGISTER SCIENTIARUM
8	05MDP	ARAGUA - MARACAY (SEDE)	GERENCIA DE MANTENIMIENTO	MAESTRÍA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	NO EVALUADO	1762	MAGISTER SCIENTIARUM
9	05MEP	ARAGUA - MARACAY (SEDE)	GERENCIA DE RECURSOS HUMANOS	MAESTRÍA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	NO EVALUADO	185	MAGISTER SCIENTIARUM
10	05DAP	ARAGUA - MARACAY (SEDE)	INNOVACIONES EDUCATIVAS	DOCTORADO	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 40.460 DE FECHA 23/07/2014	183	MAGISTER SCIENTIARUM
11	10MBP	DISTRITO CAPITAL - CARACAS (SEDE)	CIENCIAS JURÍDICAS MILITARES	MAESTRÍA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	NO EVALUADO	3275	DOCTOR
12	10TFS	DISTRITO CAPITAL - CARACAS (SEDE)	GEOMÁTICA	ESPECIALIZACIÓN	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	Autorizado. Gaceta Oficial 40.719 de fecha 07/08/2015	1761	ESPECIALISTA
13	10ERP	DISTRITO CAPITAL - CARACAS (SEDE)	TRANSPORTE FERROVIARIO	ESPECIALIZACIÓN TÉCNICA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 39.190 DE FECHA 01/06/2009	1939	MAGISTER SCIENTIARUM
14	10EIP	DISTRITO CAPITAL - CARACAS (SEDE)	ADMINISTRACIÓN DE RECURSOS HUMANOS	ESPECIALIZACIÓN TÉCNICA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 38.508 DE FECHA 25/08/2006	2038	MAGISTER
15	10EAP	DISTRITO CAPITAL - CARACAS (SEDE)	GERENCIA DE PROYECTOS FERROVIARIOS	ESPECIALIZACIÓN	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 38.508 DE FECHA 25/08/2006	3478	Especialista
16	10EEP	DISTRITO CAPITAL - CARACAS (SEDE)	GERENCIA PÚBLICA	ESPECIALIZACIÓN	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 5.524 DE FECHA 28/03/2001	2840	TÉCNICO SUPERIOR ESPECIALISTA
17	10EFP	DISTRITO CAPITAL - CARACAS (SEDE)	GERENCIA HOSPITALARIA	ESPECIALIZACIÓN	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 38.508 DE FECHA 25/08/2006	2423	TÉCNICO SUPERIOR ESPECIALISTA
18	10EJP	DISTRITO CAPITAL - CARACAS (SEDE)	GERENCIA DE RECURSOS HUMANOS	ESPECIALIZACIÓN	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 38.741 DE FECHA 07/08/2007	2471	ESPECIALISTA
19	10ELP	DISTRITO CAPITAL - CARACAS (SEDE)	NEGOCIACIÓN INTERNACIONAL DE HIDROCARBUROS	ESPECIALIZACIÓN	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 38.833 DE FECHA 17/12/2007	179	ESPECIALISTA
20	10EMP	DISTRITO CAPITAL - CARACAS (SEDE)	DERECHO PROCESAL LABORAL	ESPECIALIZACIÓN	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 38.952 DE FECHA 13/06/2008	2457	ESPECIALISTA
21	10ENP	DISTRITO CAPITAL - CARACAS (SEDE)	DERECHO EDUCATIVO	ESPECIALIZACIÓN	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 38.833 DE FECHA 17/12/2007	2462	ESPECIALISTA
22	10EOP	DISTRITO CAPITAL - CARACAS (SEDE)	INGENIERÍA COSTA AFUERA	ESPECIALIZACIÓN	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 38.833 DE FECHA 17/12/2007	2603	ESPECIALISTA
23	10EPP	DISTRITO CAPITAL - CARACAS (SEDE)	GERENCIA SOCIAL	ESPECIALIZACIÓN	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 39.003 DE FECHA 27/08/2008	2494	ESPECIALISTA
24	10EWP	DISTRITO CAPITAL - CARACAS (SEDE)	INGENIERÍA DE PERFORACIÓN Y COMPLETACIÓNES SUBMARINAS	ESPECIALIZACIÓN	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 40.460 DE FECHA 23/07/2014	2639	ESPECIALISTA
25	10EBP	DISTRITO CAPITAL - CARACAS (SEDE)	GERENCIA LOGÍSTICA	ESPECIALIZACIÓN	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 38.344 DE FECHA 27/12/2005	2660	ESPECIALISTA
26	10ECP	DISTRITO CAPITAL - CARACAS (SEDE)	GERENCIA DE MANTENIMIENTO	ESPECIALIZACIÓN	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 38.344 DE FECHA 27/12/2005	2557	ESPECIALISTA
27	10EHP	DISTRITO CAPITAL - CARACAS (SEDE)	GERENCIA AMBIENTAL	ESPECIALIZACIÓN	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 38.508 DE FECHA 25/08/2006	3375	ESPECIALISTA
28	10EKP	DISTRITO CAPITAL - CARACAS (SEDE)	EXTENSIÓN UNIVERSITARIA	ESPECIALIZACIÓN	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 38.651 DE FECHA 23/03/2007	2160	ESPECIALISTA
29	10EQP	DISTRITO CAPITAL - CARACAS (SEDE)	DERECHO AERONÁUTICO	ESPECIALIZACIÓN	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 39.065 DE FECHA 24/11/2008	2159	ESPECIALISTA
30	10EDP	DISTRITO CAPITAL - CARACAS (SEDE)	CARTOGRAFÍA MILITAR	ESPECIALIZACIÓN	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	CONSEJO DIRECTIVO IUPFAN Resolución: 47-91 13-06-91	2452	ESPECIALISTA
31	10MAP	DISTRITO CAPITAL - CARACAS (SEDE)	GERENCIA AMBIENTAL	MAESTRÍA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	NO EVALUADO	2529	ESPECIALISTA
32	10MCP	DISTRITO CAPITAL - CARACAS (SEDE)	GERENCIA LOGÍSTICA	MAESTRÍA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	NO EVALUADO	2835	ESPECIALISTA
33	10MDP	DISTRITO CAPITAL - CARACAS (SEDE)	GERENCIA DE MANTENIMIENTO	MAESTRÍA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	NO EVALUADO	182	ESPECIALISTA
34	10MEP	DISTRITO CAPITAL - CARACAS (SEDE)	GERENCIA DE RECURSOS HUMANOS	MAESTRÍA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	NO EVALUADO	178	MAGISTER SCIENTIARUM
35	10MFP	DISTRITO CAPITAL - CARACAS (SEDE)	GOBIERNO ELECTRÓNICO	MAESTRÍA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 38.508 DE FECHA 25/08/2006	177	MAGISTER SCIENTIARUM
36	10MGP	DISTRITO CAPITAL - CARACAS (SEDE)	EXTRACCIÓN DE CRUDOS PESADOS	MAESTRÍA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 38.500 DE FECHA 15/08/2006	186	MAGISTER SCIENTIARUM
37	10MHP	DISTRITO CAPITAL - CARACAS (SEDE)	EDUCACIÓN SUPERIOR	MAESTRÍA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 38.651 DE FECHA 23/03/2007	181	MAGISTER SCIENTIARUM
38	10MIP	DISTRITO CAPITAL - CARACAS (SEDE)	TECNOLOGÍA EDUCATIVA	MAESTRÍA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 38.500 DE FECHA 15/08/2006	2455	MAGISTER
39	10MJP	DISTRITO CAPITAL - CARACAS (SEDE)	TECNOLOGÍA DE LA INFORMACIÓN Y COMUNICACIÓN	MAESTRÍA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 38.508 DE FECHA 25/08/2006	2454	MAGISTER
40	10MKP	DISTRITO CAPITAL - CARACAS (SEDE)	PERFORACIÓN PETROLERA	MAESTRÍA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 38.500 DE FECHA 15/08/2006	2527	MAGISTER
41	10DAP	DISTRITO CAPITAL - CARACAS (SEDE)	INNOVACIONES EDUCATIVAS	DOCTORADO	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 38.234 DE FECHA 22/07/2005	2459	MAGISTER
42	10DBP	DISTRITO CAPITAL - CARACAS (SEDE)	CIENCIAS GERENCIALES	DOCTORADO	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 38.234 DE FECHA 22/07/2005	2411	MAGISTER SCIENTIARUM
43	10DCP	DISTRITO CAPITAL - CARACAS (SEDE)	DESARROLLO SOCIAL	DOCTORADO	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 39.065 DE FECHA 24/11/2008	2460	MAGISTER
44	10DDP	DISTRITO CAPITAL - CARACAS (SEDE)	SEGURIDAD Y DESARROLLO INTEGRAL	DOCTORADO	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 38.833 DE FECHA 17/12/2007	2135	DOCTOR
45	10DFP	DISTRITO CAPITAL - CARACAS (SEDE)	GERENCIA AMBIENTAL	DOCTORADO	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 39.618 DE FECHA 17/02/2011	2130	DOCTOR
46	10PBP	DISTRITO CAPITAL - CARACAS (SEDE)	CIENCIAS GERENCIALES	POST DOCTORADO	\N	\N	2573	DOCTOR
47	10PAP	DISTRITO CAPITAL - CARACAS (SEDE)	CIENCIAS DE LA EDUCACIÓN	POST DOCTORADO	\N	\N	2594	DOCTOR
48	10MLP	DISTRITO CAPITAL - CARACAS (SEDE)	CIENCIAS JURÍDICAS	MAESTRÍA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 38.939 DE FECHA 27/05/2008	3084	DOCTOR
49	12SVS	FALCÓN - CORO (SEDE)	GERENCIA DE RECURSOS HUMANOS	MAESTRÍA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	40.602 del 13/02/2015	\N	\N
50	12DBP	FALCÓN - CORO (SEDE)	CIENCIAS GERENCIALES	DOCTORADO	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 40.460 DE FECHA 23/07/2014	\N	\N
51	44DAP	NUEVA ESPARTA - PAMPATAR (SEDE)	INNOVACIONES EDUCATIVAS	DOCTORADO	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 40.460 DE FECHA 23/07/2014	3344	MAGISTER SCIENTIARUM
52	\N	NUEVA ESPARTA - PAMPATAR (SEDE)	CIENCIAS GERENCIALES	DOCTORADO (OJO NO AUTORIZADO)	\N	\N	3269	DOCTOR
53	44MEP	NUEVA ESPARTA - PAMPATAR (SEDE)	GERENCIA DE RECURSOS HUMANOS	MAESTRÍA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 40.460 DE FECHA 23/07/2014	2539	MAGISTER SCIENTIARUM
54	44MAP	NUEVA ESPARTA - PAMPATAR (SEDE)	GERENCIA AMBIENTAL	MAESTRÍA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 40.527 DE FECHA 27/10/2014	3284	DOCTOR
55	08EEP	PTO. CABELLO  (SEDE)	GERENCIA PÚBLICA	ESPECIALIZACIÓN	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 5.585 DE FECHA 28/04/2002	\N	\N
56	08MCP	PTO. CABELLO  (SEDE)	GERENCIA LOGÍSTICA	MAESTRÍA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 5.687 DE FECHA 30/12/2003	3345	MAGISTER SCIENTIARUM
57	08MDP	PTO. CABELLO  (SEDE)	GERENCIA DE MANTENIMIENTO	MAESTRÍA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 5.687 DE FECHA 30/12/2003	3338	MAGISTER SCIENTIARUM
58	20MCP	SUCRE - CUMANA (SEDE)	GERENCIA LOGÍSTICA	MAESTRÍA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 40.460 DE FECHA 23/07/2014	3342	MAGISTER
59	21MEP	TÁCHIRA - SAN CRISTÓBAL (SEDE)	GERENCIA DE RECURSOS HUMANOS	MAESTRÍA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 40.460 DE FECHA 23/07/2014	3271	MAGISTER SCIENTIARUM
60	21MAP	TÁCHIRA - SAN CRISTÓBAL (SEDE)	GERENCIA AMBIENTAL	MAESTRÍA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 40.225 DE FECHA 09/08/2013	3270	MAGISTER SCIENTIARUM
61	21DAP	TÁCHIRA - SAN CRISTÓBAL (SEDE)	INNOVACIONES EDUCATIVAS	DOCTORADO	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 40.460 DE FECHA 23/07/2014	3283	DOCTOR
62	21DBP	TÁCHIRA - SAN CRISTÓBAL (SEDE)	CIENCIAS GERENCIALES	DOCTORADO	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 40.460 DE FECHA 23/07/2014	3277	DOCTOR
63	21MLP	TÁCHIRA - SAN CRISTÓBAL (SEDE)	CIENCIAS JURÍDICAS	MAESTRÍA	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 40.460 DE FECHA 23/07/2014	3276	DOCTOR
64	25DAP	YARACUY - SAN FELIPE (SEDE)	INNOVACIONES EDUCATIVAS	DOCTORADO	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 40.460 DE FECHA 23/07/2014	3280	DOCTOR
65	25DBP	YARACUY - SAN FELIPE (SEDE)	CIENCIAS GERENCIALES	DOCTORADO	AUTORIZADO SEGÚN MEMORANDO N° 760 DE FECHA 21OCT16	AUTORIZADO. GACETA OFICIAL 40.460 DE FECHA 23/07/2014	3282	MAGISTER SCIENTIARUM
\.


--
-- Data for Name: requisitos_preinscripcion; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.requisitos_preinscripcion (id, aspirante_id, ruta_pdf_titulo, ruta_pdf_notas, ruta_pdf_cv) FROM stdin;
\.


--
-- Data for Name: respuestas_baremo; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.respuestas_baremo (id, id_aspirante, id_pregunta, respuesta, fecha_respuesta) FROM stdin;
\.


--
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.roles (id, nombre, descripcion) FROM stdin;
1	Administrador	Administrador del sistema - infraestructura y soporte
2	Coordinador	Coordinador de programa - planificación académica
3	Docente	Docente / Profesor - evaluación académica
4	Secretaria	Secretaría de Control de Estudios - admisiones y pagos
5	Aspirante	Aspirante / Postulante - pre-inscripción
6	Estudiante	Estudiante regular - cursante activo
7	Director	Director de postgrado - control de fases y planes
\.


--
-- Data for Name: secciones; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.secciones (id, plan_id, asignatura_codigo, seccion, profesor_id, sede_id, cupo_maximo, cupo_actual, aula, periodo, activa, created_at) FROM stdin;
\.


--
-- Data for Name: sedes; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.sedes (id, nombre, fase_actual, ubicacion, codigo, activa, created_at) FROM stdin;
1	Sede Principal	1	Distrito Capital	CCS	t	2026-06-13 19:30:39.581287
2	Núcleo Caracas	1	Estado Aragua	MAR	t	2026-06-13 19:30:39.581287
3	San Tome	1	Estado Anzoategui	STO	t	2026-06-13 19:31:18.131995
\.


--
-- Data for Name: solicitudes_docentes; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.solicitudes_docentes (id, coordinador_id, sede_id, tipo_documento, numero_documento, nombres, apellidos, email, nacionalidad, estatus, admin_id, created_at, resuelto_at) FROM stdin;
\.


--
-- Data for Name: usuarios; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.usuarios (id, cedula, nombres, apellidos, email, password, rol, estatus_admision, matricula, tipo_cedula, rol_id, sede_id, estatus, sexo, fecha_nacimiento, nacionalidad, numero_documento, estado_aspirante, telefono, direccion, fecha_registro) FROM stdin;
2	1	Admin	Sistema	admin@sip.unefa.edu.ve	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	ASPIRANTE	REGISTRADO	\N	V	1	1	Activo	\N	\N	Venezuela	1	En Revision Digital	\N	\N	2026-06-13 20:08:39.513612
3	10000001	Director	Principal	director@unefa.edu.ve	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	ASPIRANTE	REGISTRADO	\N	V	7	1	Activo	\N	\N	Venezuela	10000001	En Revision Digital	\N	\N	2026-06-13 20:08:39.513612
4	10000002	Coordinador	Academico	coordinador@unefa.edu.ve	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	ASPIRANTE	REGISTRADO	\N	V	2	1	Activo	\N	\N	Venezuela	10000002	En Revision Digital	\N	\N	2026-06-13 20:08:39.513612
5	12345678	Estudiante	Venezolano	estudiante@unefa.edu.ve	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	ASPIRANTE	REGISTRADO	\N	V	6	1	Activo	\N	\N	Venezuela	12345678	En Revision Digital	\N	\N	2026-06-13 20:08:39.513612
6	0	Estudiante	Extranjero	pasaporte@unefa.edu.ve	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	ASPIRANTE	REGISTRADO	\N	P	6	1	Activo	\N	\N	Venezuela	FR87654321	En Revision Digital	\N	\N	2026-06-13 20:08:39.513612
7	10000003	Secretaria	Control Estudios	secretaria@unefa.edu.ve	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	ASPIRANTE	REGISTRADO	\N	V	4	1	Activo	\N	\N	Venezuela	10000003	En Revision Digital	\N	\N	2026-06-13 20:08:39.513612
8	10000004	Docente	Planta	docente@unefa.edu.ve	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	ASPIRANTE	REGISTRADO	\N	V	3	1	Activo	\N	\N	Venezuela	10000004	En Revision Digital	\N	\N	2026-06-13 20:08:39.513612
9	87654321	Aspirante	Nuevo	aspirante@unefa.edu.ve	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	ASPIRANTE	REGISTRADO	\N	V	5	1	Activo	\N	\N	Venezuela	87654321	En Revision Digital	\N	\N	2026-06-13 20:08:39.513612
1	V-99999999	Pedro	Pérez	pedro@unefa.edu.ve	hash123	ESTUDIANTE	APROBADO	PG-2026-0001	V	5	\N	Activo	\N	\N	Venezuela	99999999	En Revision Digital	\N	\N	2026-06-13 20:08:39.513612
\.


--
-- Data for Name: usuarios_acceso; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.usuarios_acceso (id, username, password_hash) FROM stdin;
\.


--
-- Name: actas_log_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.actas_log_id_seq', 1, false);


--
-- Name: actas_notas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.actas_notas_id_seq', 1, false);


--
-- Name: asignaciones_docente_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.asignaciones_docente_id_seq', 1, false);


--
-- Name: aspirante_documentos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.aspirante_documentos_id_seq', 1, false);


--
-- Name: autorizaciones_cnu_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.autorizaciones_cnu_id_seq', 130, true);


--
-- Name: baremo_preguntas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.baremo_preguntas_id_seq', 1, false);


--
-- Name: cohortes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.cohortes_id_seq', 1, false);


--
-- Name: creditos_resguardados_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.creditos_resguardados_id_seq', 1, false);


--
-- Name: horarios_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.horarios_id_seq', 1, false);


--
-- Name: inscripciones_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.inscripciones_id_seq', 1, false);


--
-- Name: llaves_digitales_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.llaves_digitales_id_seq', 1, false);


--
-- Name: logs_auditoria_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.logs_auditoria_id_seq', 7, true);


--
-- Name: mallas_curriculares_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.mallas_curriculares_id_seq', 205, true);


--
-- Name: materias_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.materias_id_seq', 1, false);


--
-- Name: notas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.notas_id_seq', 1, false);


--
-- Name: nucleos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.nucleos_id_seq', 131, true);


--
-- Name: pagos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.pagos_id_seq', 1, false);


--
-- Name: plan_asignaturas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.plan_asignaturas_id_seq', 1, false);


--
-- Name: plan_estudios_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.plan_estudios_id_seq', 2, true);


--
-- Name: plan_sede_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.plan_sede_id_seq', 1, false);


--
-- Name: planes_estudio_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.planes_estudio_id_seq', 1, false);


--
-- Name: programas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.programas_id_seq', 131, true);


--
-- Name: requisitos_preinscripcion_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.requisitos_preinscripcion_id_seq', 1, false);


--
-- Name: respuestas_baremo_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.respuestas_baremo_id_seq', 1, false);


--
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.roles_id_seq', 1, false);


--
-- Name: secciones_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.secciones_id_seq', 1, false);


--
-- Name: sedes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.sedes_id_seq', 3, true);


--
-- Name: seq_matricula_estudiante; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.seq_matricula_estudiante', 1, true);


--
-- Name: solicitudes_docentes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.solicitudes_docentes_id_seq', 1, false);


--
-- Name: usuarios_acceso_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.usuarios_acceso_id_seq', 1, false);


--
-- Name: usuarios_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.usuarios_id_seq', 9, true);


--
-- Name: actas_log actas_log_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actas_log
    ADD CONSTRAINT actas_log_pkey PRIMARY KEY (id);


--
-- Name: actas_notas actas_notas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actas_notas
    ADD CONSTRAINT actas_notas_pkey PRIMARY KEY (id);


--
-- Name: actas_notas actas_notas_seccion_id_usuario_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actas_notas
    ADD CONSTRAINT actas_notas_seccion_id_usuario_id_key UNIQUE (seccion_id, usuario_id);


--
-- Name: asignaciones_docente asignaciones_docente_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asignaciones_docente
    ADD CONSTRAINT asignaciones_docente_pkey PRIMARY KEY (id);


--
-- Name: asignaturas asignaturas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asignaturas
    ADD CONSTRAINT asignaturas_pkey PRIMARY KEY (codigo);


--
-- Name: aspirante_documentos aspirante_documentos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.aspirante_documentos
    ADD CONSTRAINT aspirante_documentos_pkey PRIMARY KEY (id);


--
-- Name: autorizaciones_cnu autorizaciones_cnu_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.autorizaciones_cnu
    ADD CONSTRAINT autorizaciones_cnu_pkey PRIMARY KEY (id);


--
-- Name: baremo_preguntas baremo_preguntas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.baremo_preguntas
    ADD CONSTRAINT baremo_preguntas_pkey PRIMARY KEY (id);


--
-- Name: cohortes cohortes_codigo_cohorte_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cohortes
    ADD CONSTRAINT cohortes_codigo_cohorte_key UNIQUE (codigo_cohorte);


--
-- Name: cohortes cohortes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cohortes
    ADD CONSTRAINT cohortes_pkey PRIMARY KEY (id);


--
-- Name: creditos_resguardados creditos_resguardados_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.creditos_resguardados
    ADD CONSTRAINT creditos_resguardados_pkey PRIMARY KEY (id);


--
-- Name: horarios horarios_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.horarios
    ADD CONSTRAINT horarios_pkey PRIMARY KEY (id);


--
-- Name: horarios horarios_seccion_id_dia_semana_hora_inicio_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.horarios
    ADD CONSTRAINT horarios_seccion_id_dia_semana_hora_inicio_key UNIQUE (seccion_id, dia_semana, hora_inicio);


--
-- Name: inscripciones inscripciones_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inscripciones
    ADD CONSTRAINT inscripciones_pkey PRIMARY KEY (id);


--
-- Name: llaves_digitales llaves_digitales_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.llaves_digitales
    ADD CONSTRAINT llaves_digitales_pkey PRIMARY KEY (id);


--
-- Name: logs_auditoria logs_auditoria_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logs_auditoria
    ADD CONSTRAINT logs_auditoria_pkey PRIMARY KEY (id);


--
-- Name: mallas_curriculares mallas_curriculares_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.mallas_curriculares
    ADD CONSTRAINT mallas_curriculares_pkey PRIMARY KEY (id);


--
-- Name: materias materias_codigo_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.materias
    ADD CONSTRAINT materias_codigo_key UNIQUE (codigo);


--
-- Name: materias materias_nombre_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.materias
    ADD CONSTRAINT materias_nombre_key UNIQUE (nombre);


--
-- Name: materias materias_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.materias
    ADD CONSTRAINT materias_pkey PRIMARY KEY (id);


--
-- Name: notas notas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notas
    ADD CONSTRAINT notas_pkey PRIMARY KEY (id);


--
-- Name: nucleos nucleos_nombre_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.nucleos
    ADD CONSTRAINT nucleos_nombre_key UNIQUE (nombre);


--
-- Name: nucleos nucleos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.nucleos
    ADD CONSTRAINT nucleos_pkey PRIMARY KEY (id);


--
-- Name: pagos pagos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pagos
    ADD CONSTRAINT pagos_pkey PRIMARY KEY (id);


--
-- Name: pagos pagos_referencia_bancaria_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pagos
    ADD CONSTRAINT pagos_referencia_bancaria_key UNIQUE (referencia_bancaria);


--
-- Name: plan_asignaturas plan_asignaturas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plan_asignaturas
    ADD CONSTRAINT plan_asignaturas_pkey PRIMARY KEY (id);


--
-- Name: plan_asignaturas plan_asignaturas_plan_id_asignatura_codigo_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plan_asignaturas
    ADD CONSTRAINT plan_asignaturas_plan_id_asignatura_codigo_key UNIQUE (plan_id, asignatura_codigo);


--
-- Name: plan_estudios plan_estudios_codigo_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plan_estudios
    ADD CONSTRAINT plan_estudios_codigo_key UNIQUE (codigo);


--
-- Name: plan_estudios plan_estudios_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plan_estudios
    ADD CONSTRAINT plan_estudios_pkey PRIMARY KEY (id);


--
-- Name: plan_sede plan_sede_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plan_sede
    ADD CONSTRAINT plan_sede_pkey PRIMARY KEY (id);


--
-- Name: plan_sede plan_sede_plan_id_sede_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plan_sede
    ADD CONSTRAINT plan_sede_plan_id_sede_id_key UNIQUE (plan_id, sede_id);


--
-- Name: planes_estudio planes_estudio_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.planes_estudio
    ADD CONSTRAINT planes_estudio_pkey PRIMARY KEY (id);


--
-- Name: programas programas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.programas
    ADD CONSTRAINT programas_pkey PRIMARY KEY (id);


--
-- Name: programas_postgrado programas_postgrado_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.programas_postgrado
    ADD CONSTRAINT programas_postgrado_pkey PRIMARY KEY (id);


--
-- Name: requisitos_preinscripcion requisitos_preinscripcion_aspirante_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.requisitos_preinscripcion
    ADD CONSTRAINT requisitos_preinscripcion_aspirante_id_key UNIQUE (aspirante_id);


--
-- Name: requisitos_preinscripcion requisitos_preinscripcion_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.requisitos_preinscripcion
    ADD CONSTRAINT requisitos_preinscripcion_pkey PRIMARY KEY (id);


--
-- Name: respuestas_baremo respuestas_baremo_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.respuestas_baremo
    ADD CONSTRAINT respuestas_baremo_pkey PRIMARY KEY (id);


--
-- Name: roles roles_nombre_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_nombre_key UNIQUE (nombre);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: secciones secciones_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.secciones
    ADD CONSTRAINT secciones_pkey PRIMARY KEY (id);


--
-- Name: sedes sedes_codigo_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sedes
    ADD CONSTRAINT sedes_codigo_key UNIQUE (codigo);


--
-- Name: sedes sedes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sedes
    ADD CONSTRAINT sedes_pkey PRIMARY KEY (id);


--
-- Name: solicitudes_docentes solicitudes_docentes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.solicitudes_docentes
    ADD CONSTRAINT solicitudes_docentes_pkey PRIMARY KEY (id);


--
-- Name: asignaturas uk_asignatura_codigo; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asignaturas
    ADD CONSTRAINT uk_asignatura_codigo UNIQUE (codigo);


--
-- Name: autorizaciones_cnu unique_nucleo_programa; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.autorizaciones_cnu
    ADD CONSTRAINT unique_nucleo_programa UNIQUE (nucleo_id, programa_id);


--
-- Name: programas unique_programa; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.programas
    ADD CONSTRAINT unique_programa UNIQUE (nombre, acreditable);


--
-- Name: asignaciones_docente uq_docente_materia_cohorte; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asignaciones_docente
    ADD CONSTRAINT uq_docente_materia_cohorte UNIQUE (docente_id, asignatura_codigo, cohorte_id, termino);


--
-- Name: notas uq_nota_por_materia_inscrita; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notas
    ADD CONSTRAINT uq_nota_por_materia_inscrita UNIQUE (inscripcion_id, asignatura_codigo);


--
-- Name: mallas_curriculares uq_programa_materia; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.mallas_curriculares
    ADD CONSTRAINT uq_programa_materia UNIQUE (id_programa, id_materia);


--
-- Name: usuarios_acceso usuarios_acceso_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuarios_acceso
    ADD CONSTRAINT usuarios_acceso_pkey PRIMARY KEY (id);


--
-- Name: usuarios_acceso usuarios_acceso_username_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuarios_acceso
    ADD CONSTRAINT usuarios_acceso_username_key UNIQUE (username);


--
-- Name: usuarios usuarios_correo_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_correo_key UNIQUE (email);


--
-- Name: usuarios usuarios_matricula_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_matricula_key UNIQUE (matricula);


--
-- Name: usuarios usuarios_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id);


--
-- Name: idx_actas_notas_seccion; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_actas_notas_seccion ON public.actas_notas USING btree (seccion_id);


--
-- Name: idx_actas_notas_usuario; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_actas_notas_usuario ON public.actas_notas USING btree (usuario_id);


--
-- Name: idx_creditos_usuario; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_creditos_usuario ON public.creditos_resguardados USING btree (usuario_id);


--
-- Name: idx_logs_created; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_logs_created ON public.logs_auditoria USING btree (created_at);


--
-- Name: idx_logs_usuario; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_logs_usuario ON public.logs_auditoria USING btree (usuario_id);


--
-- Name: idx_pagos_usuario; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_pagos_usuario ON public.pagos USING btree (usuario_id);


--
-- Name: idx_secciones_profesor; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_secciones_profesor ON public.secciones USING btree (profesor_id);


--
-- Name: idx_secciones_sede; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_secciones_sede ON public.secciones USING btree (sede_id);


--
-- Name: idx_solicitudes_estatus; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_solicitudes_estatus ON public.solicitudes_docentes USING btree (estatus);


--
-- Name: idx_usuarios_rol; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuarios_rol ON public.usuarios USING btree (rol_id);


--
-- Name: idx_usuarios_sede; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuarios_sede ON public.usuarios USING btree (sede_id);


--
-- Name: usuarios trg_despues_aprobacion_admision; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_despues_aprobacion_admision BEFORE UPDATE ON public.usuarios FOR EACH ROW EXECUTE FUNCTION public.tg_automatizar_admision();


--
-- Name: notas trg_validar_nota_antes_registro; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trg_validar_nota_antes_registro BEFORE INSERT OR UPDATE ON public.notas FOR EACH ROW EXECUTE FUNCTION public.tg_validar_inscripcion_nota();


--
-- Name: actas_log actas_log_acta_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actas_log
    ADD CONSTRAINT actas_log_acta_id_fkey FOREIGN KEY (acta_id) REFERENCES public.actas_notas(id);


--
-- Name: actas_log actas_log_usuario_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actas_log
    ADD CONSTRAINT actas_log_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id);


--
-- Name: actas_notas actas_notas_seccion_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actas_notas
    ADD CONSTRAINT actas_notas_seccion_id_fkey FOREIGN KEY (seccion_id) REFERENCES public.secciones(id);


--
-- Name: actas_notas actas_notas_usuario_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.actas_notas
    ADD CONSTRAINT actas_notas_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id);


--
-- Name: asignaciones_docente asignaciones_docente_asignatura_codigo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asignaciones_docente
    ADD CONSTRAINT asignaciones_docente_asignatura_codigo_fkey FOREIGN KEY (asignatura_codigo) REFERENCES public.asignaturas(codigo) ON DELETE RESTRICT;


--
-- Name: asignaciones_docente asignaciones_docente_cohorte_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asignaciones_docente
    ADD CONSTRAINT asignaciones_docente_cohorte_id_fkey FOREIGN KEY (cohorte_id) REFERENCES public.cohortes(id) ON DELETE RESTRICT;


--
-- Name: asignaciones_docente asignaciones_docente_docente_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.asignaciones_docente
    ADD CONSTRAINT asignaciones_docente_docente_id_fkey FOREIGN KEY (docente_id) REFERENCES public.usuarios(id) ON DELETE RESTRICT;


--
-- Name: aspirante_documentos aspirante_documentos_usuario_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.aspirante_documentos
    ADD CONSTRAINT aspirante_documentos_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id);


--
-- Name: autorizaciones_cnu autorizaciones_cnu_nucleo_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.autorizaciones_cnu
    ADD CONSTRAINT autorizaciones_cnu_nucleo_id_fkey FOREIGN KEY (nucleo_id) REFERENCES public.nucleos(id) ON DELETE CASCADE;


--
-- Name: autorizaciones_cnu autorizaciones_cnu_programa_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.autorizaciones_cnu
    ADD CONSTRAINT autorizaciones_cnu_programa_id_fkey FOREIGN KEY (programa_id) REFERENCES public.programas(id) ON DELETE CASCADE;


--
-- Name: cohortes cohortes_nucleo_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cohortes
    ADD CONSTRAINT cohortes_nucleo_id_fkey FOREIGN KEY (nucleo_id) REFERENCES public.nucleos(id) ON DELETE RESTRICT;


--
-- Name: cohortes cohortes_programa_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cohortes
    ADD CONSTRAINT cohortes_programa_id_fkey FOREIGN KEY (programa_id) REFERENCES public.programas(id) ON DELETE RESTRICT;


--
-- Name: creditos_resguardados creditos_resguardados_sede_destino_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.creditos_resguardados
    ADD CONSTRAINT creditos_resguardados_sede_destino_id_fkey FOREIGN KEY (sede_destino_id) REFERENCES public.sedes(id);


--
-- Name: creditos_resguardados creditos_resguardados_sede_origen_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.creditos_resguardados
    ADD CONSTRAINT creditos_resguardados_sede_origen_id_fkey FOREIGN KEY (sede_origen_id) REFERENCES public.sedes(id);


--
-- Name: creditos_resguardados creditos_resguardados_usuario_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.creditos_resguardados
    ADD CONSTRAINT creditos_resguardados_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id);


--
-- Name: mallas_curriculares fk_mallas_materias; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.mallas_curriculares
    ADD CONSTRAINT fk_mallas_materias FOREIGN KEY (id_materia) REFERENCES public.asignaturas(codigo);


--
-- Name: horarios horarios_seccion_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.horarios
    ADD CONSTRAINT horarios_seccion_id_fkey FOREIGN KEY (seccion_id) REFERENCES public.secciones(id) ON DELETE CASCADE;


--
-- Name: inscripciones inscripciones_cohorte_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inscripciones
    ADD CONSTRAINT inscripciones_cohorte_id_fkey FOREIGN KEY (cohorte_id) REFERENCES public.cohortes(id) ON DELETE RESTRICT;


--
-- Name: inscripciones inscripciones_estudiante_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inscripciones
    ADD CONSTRAINT inscripciones_estudiante_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE RESTRICT;


--
-- Name: inscripciones inscripciones_seccion_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inscripciones
    ADD CONSTRAINT inscripciones_seccion_id_fkey FOREIGN KEY (seccion_id) REFERENCES public.secciones(id);


--
-- Name: llaves_digitales llaves_digitales_admin_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.llaves_digitales
    ADD CONSTRAINT llaves_digitales_admin_id_fkey FOREIGN KEY (admin_id) REFERENCES public.usuarios(id);


--
-- Name: llaves_digitales llaves_digitales_solicitante_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.llaves_digitales
    ADD CONSTRAINT llaves_digitales_solicitante_id_fkey FOREIGN KEY (solicitante_id) REFERENCES public.usuarios(id);


--
-- Name: logs_auditoria logs_auditoria_usuario_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logs_auditoria
    ADD CONSTRAINT logs_auditoria_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id);


--
-- Name: mallas_curriculares mallas_curriculares_id_programa_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.mallas_curriculares
    ADD CONSTRAINT mallas_curriculares_id_programa_fkey FOREIGN KEY (id_programa) REFERENCES public.programas(id) ON DELETE CASCADE;


--
-- Name: notas notas_asignatura_codigo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notas
    ADD CONSTRAINT notas_asignatura_codigo_fkey FOREIGN KEY (asignatura_codigo) REFERENCES public.asignaturas(codigo) ON DELETE RESTRICT;


--
-- Name: notas notas_inscripcion_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notas
    ADD CONSTRAINT notas_inscripcion_id_fkey FOREIGN KEY (inscripcion_id) REFERENCES public.inscripciones(id) ON DELETE CASCADE;


--
-- Name: pagos pagos_inscripcion_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pagos
    ADD CONSTRAINT pagos_inscripcion_id_fkey FOREIGN KEY (inscripcion_id) REFERENCES public.inscripciones(id);


--
-- Name: pagos pagos_secretaria_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pagos
    ADD CONSTRAINT pagos_secretaria_id_fkey FOREIGN KEY (secretaria_id) REFERENCES public.usuarios(id);


--
-- Name: pagos pagos_usuario_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pagos
    ADD CONSTRAINT pagos_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE RESTRICT;


--
-- Name: plan_asignaturas plan_asignaturas_asignatura_codigo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plan_asignaturas
    ADD CONSTRAINT plan_asignaturas_asignatura_codigo_fkey FOREIGN KEY (asignatura_codigo) REFERENCES public.asignaturas(codigo) ON DELETE CASCADE;


--
-- Name: plan_asignaturas plan_asignaturas_plan_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plan_asignaturas
    ADD CONSTRAINT plan_asignaturas_plan_id_fkey FOREIGN KEY (plan_id) REFERENCES public.plan_estudios(id) ON DELETE CASCADE;


--
-- Name: plan_sede plan_sede_plan_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plan_sede
    ADD CONSTRAINT plan_sede_plan_id_fkey FOREIGN KEY (plan_id) REFERENCES public.plan_estudios(id) ON DELETE CASCADE;


--
-- Name: plan_sede plan_sede_sede_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.plan_sede
    ADD CONSTRAINT plan_sede_sede_id_fkey FOREIGN KEY (sede_id) REFERENCES public.sedes(id) ON DELETE CASCADE;


--
-- Name: planes_estudio planes_estudio_asignatura_codigo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.planes_estudio
    ADD CONSTRAINT planes_estudio_asignatura_codigo_fkey FOREIGN KEY (asignatura_codigo) REFERENCES public.asignaturas(codigo) ON DELETE CASCADE;


--
-- Name: planes_estudio planes_estudio_programa_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.planes_estudio
    ADD CONSTRAINT planes_estudio_programa_id_fkey FOREIGN KEY (programa_id) REFERENCES public.programas(id) ON DELETE CASCADE;


--
-- Name: requisitos_preinscripcion requisitos_preinscripcion_aspirante_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.requisitos_preinscripcion
    ADD CONSTRAINT requisitos_preinscripcion_aspirante_id_fkey FOREIGN KEY (aspirante_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;


--
-- Name: respuestas_baremo respuestas_baremo_id_aspirante_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.respuestas_baremo
    ADD CONSTRAINT respuestas_baremo_id_aspirante_fkey FOREIGN KEY (id_aspirante) REFERENCES public.usuarios(id);


--
-- Name: respuestas_baremo respuestas_baremo_id_pregunta_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.respuestas_baremo
    ADD CONSTRAINT respuestas_baremo_id_pregunta_fkey FOREIGN KEY (id_pregunta) REFERENCES public.baremo_preguntas(id);


--
-- Name: secciones secciones_asignatura_codigo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.secciones
    ADD CONSTRAINT secciones_asignatura_codigo_fkey FOREIGN KEY (asignatura_codigo) REFERENCES public.asignaturas(codigo);


--
-- Name: secciones secciones_plan_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.secciones
    ADD CONSTRAINT secciones_plan_id_fkey FOREIGN KEY (plan_id) REFERENCES public.plan_estudios(id);


--
-- Name: secciones secciones_profesor_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.secciones
    ADD CONSTRAINT secciones_profesor_id_fkey FOREIGN KEY (profesor_id) REFERENCES public.usuarios(id);


--
-- Name: secciones secciones_sede_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.secciones
    ADD CONSTRAINT secciones_sede_id_fkey FOREIGN KEY (sede_id) REFERENCES public.sedes(id);


--
-- Name: solicitudes_docentes solicitudes_docentes_admin_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.solicitudes_docentes
    ADD CONSTRAINT solicitudes_docentes_admin_id_fkey FOREIGN KEY (admin_id) REFERENCES public.usuarios(id);


--
-- Name: solicitudes_docentes solicitudes_docentes_coordinador_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.solicitudes_docentes
    ADD CONSTRAINT solicitudes_docentes_coordinador_id_fkey FOREIGN KEY (coordinador_id) REFERENCES public.usuarios(id);


--
-- Name: solicitudes_docentes solicitudes_docentes_sede_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.solicitudes_docentes
    ADD CONSTRAINT solicitudes_docentes_sede_id_fkey FOREIGN KEY (sede_id) REFERENCES public.sedes(id);


--
-- Name: usuarios usuarios_rol_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_rol_id_fkey FOREIGN KEY (rol_id) REFERENCES public.roles(id);


--
-- Name: usuarios usuarios_sede_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_sede_id_fkey FOREIGN KEY (sede_id) REFERENCES public.sedes(id);


--
-- PostgreSQL database dump complete
--

\unrestrict 5xokmnSBQgBbAHsC8h07NT2pCgCXEFkf4cG4HqTmARdwKR7Lzdh5P5idftGy5sM

