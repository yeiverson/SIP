-- PostgreSQL database dump corregido

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;
SET default_tablespace = '';
SET default_table_access_method = heap;

-- Tabla: baremo_preguntas
CREATE TABLE IF NOT EXISTS public.baremo_preguntas (
    id integer NOT NULL,
    pregunta text NOT NULL,
    categoria character varying(50) NOT NULL,
    orden integer
);

CREATE SEQUENCE IF NOT EXISTS public.baremo_preguntas_id_seq
    AS integer START WITH 1 INCREMENT BY 1 NO MINVALUE NO MAXVALUE CACHE 1;

ALTER SEQUENCE public.baremo_preguntas_id_seq OWNED BY public.baremo_preguntas.id;

-- Tabla: password_reset_tokens
CREATE TABLE IF NOT EXISTS public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);

-- Tabla: personal_access_tokens
CREATE TABLE IF NOT EXISTS public.personal_access_tokens (
    id bigint NOT NULL,
    tokenable_type character varying(255) NOT NULL,
    tokenable_id bigint NOT NULL,
    name character varying(255) NOT NULL,
    token character varying(64) NOT NULL,
    abilities text,
    last_used_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);

CREATE SEQUENCE IF NOT EXISTS public.personal_access_tokens_id_seq
    START WITH 1 INCREMENT BY 1 NO MINVALUE NO MAXVALUE CACHE 1;

ALTER SEQUENCE public.personal_access_tokens_id_seq OWNED BY public.personal_access_tokens.id;

-- Tabla: respuestas_baremo
CREATE TABLE IF NOT EXISTS public.respuestas_baremo (
    id integer NOT NULL,
    id_aspirante integer,
    id_pregunta integer,
    respuesta character varying(2) NOT NULL,
    fecha_respuesta timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);

CREATE SEQUENCE IF NOT EXISTS public.respuestas_baremo_id_seq
    AS integer START WITH 1 INCREMENT BY 1 NO MINVALUE NO MAXVALUE CACHE 1;

ALTER SEQUENCE public.respuestas_baremo_id_seq OWNED BY public.respuestas_baremo.id;

-- Tabla: users
CREATE TABLE IF NOT EXISTS public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);

CREATE SEQUENCE IF NOT EXISTS public.users_id_seq
    START WITH 1 INCREMENT BY 1 NO MINVALUE NO MAXVALUE CACHE 1;

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;

-- Tabla: usuarios
CREATE TABLE IF NOT EXISTS public.usuarios (
    id integer NOT NULL,
    tipo_cedula character varying(8) NOT NULL,
    nombres character varying(100) NOT NULL,
    apellidos character varying(100) NOT NULL,
    email character varying(150) NOT NULL,
    telefono character varying(20),
    direccion text,
    password character varying(255) NOT NULL,
    fecha_registro timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    cedula integer,
    CONSTRAINT usuarios_tipo_cedula_check CHECK (((tipo_cedula)::bpchar = ANY (ARRAY['V'::bpchar, 'E'::bpchar])))
);

CREATE SEQUENCE IF NOT EXISTS public.usuarios_id_seq
    AS integer START WITH 1 INCREMENT BY 1 NO MINVALUE NO MAXVALUE CACHE 1;

ALTER SEQUENCE public.usuarios_id_seq OWNED BY public.usuarios.id;

-- Defaults
ALTER TABLE ONLY public.baremo_preguntas ALTER COLUMN id SET DEFAULT nextval('public.baremo_preguntas_id_seq'::regclass);
ALTER TABLE ONLY public.personal_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.personal_access_tokens_id_seq'::regclass);
ALTER TABLE ONLY public.respuestas_baremo ALTER COLUMN id SET DEFAULT nextval('public.respuestas_baremo_id_seq'::regclass);
ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);
ALTER TABLE ONLY public.usuarios ALTER COLUMN id SET DEFAULT nextval('public.usuarios_id_seq'::regclass);

-- Datos: baremo_preguntas
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
(14, 'Disponibilidad personal para financiar los estudios (Opción B).', 'Otros', 14);

-- Datos: usuarios
INSERT INTO public.usuarios (id, tipo_cedula, nombres, apellidos, email, telefono, direccion, password, fecha_registro, cedula) VALUES
(1, 'V', 'lola', 'perez', 'mariajose@gmail.com', '04241241299', 'caracas', '$2y$10$QqIhRwsQODNAsOVqCvQ.qO8yFnvmZ0b/aGaxYs9wRkCmhLJjauVHm', '2026-03-10 00:02:30.533759', NULL),
(2, 'V', 'martin', 'veliz', 'martin@gmail.com', '04241252377', 'caracas', '$2y$10$YWBY4iJ8y6vXA5BZNg/vPe.oi9tY3Bq3gRYvRnQmBLlvQK16iDCwy', '2026-03-10 08:49:06.484777', NULL),
(4, 'V', 'ELIAS', 'FUENTES', 'mussiomaria@gmail.com', '04126788663', 'CARACAS', '$2y$10$eZ9iq1iltsVxlpDV8Xb5Zu0ra3iI/KldWzGN0E5bUlYcpZ5mKEwyu', '2026-03-10 13:24:05.227439', NULL),
(9, 'V', 'pedro', 'veliz', 'usuario@gmail.com', '04133567845', 'caracas', '$2y$10$f2D2JlWuZZWCyQtUMQ48SO9r07bCSDvxfDFSUSZxzS1g8NcC2WdBW', '2026-03-11 11:13:20.256224', NULL),
(11, 'V', 'fernanda', 'FUENTES', 'usuarioS@gmail.com', '04241231456', 'caracas', '$2y$10$wUFZIMAumcHRprP6OKWPT.7RK4VKbY2lNFMA6oLIhaP6L/qdjw1sC', '2026-03-11 11:28:37.072154', 26152344),
(12, 'V', 'hayley', 'ortega', 'hayleey5@gmail.com', '04241240723', 'caracas', '$2y$10$OdaE7A7iZnbnnO18lBBdHOjmve.D97L8ohyXwfFbzdx6ux77Dj./m', '2026-03-20 13:05:47.482169', 6332727),
(23, 'V', 'Pendiente', 'Pendiente', 'maria@gmail.com', NULL, 'Pendiente', '$2y$10$lhAgRuJ1dsFu9huTFqnuBeY1UHrif1rKAG36iUB//JXktt9SXbFei', '2026-04-02 21:25:23.324435', 12345678),
(24, 'V', 'Pendiente', 'Pendiente', 'marco@gmail.com', NULL, 'Pendiente', '$2y$10$1EEJw9ai1avrWKw8zAqVeO1K7bvvNV/XcJmGM.eyz76WMlvKUhDgi', '2026-04-03 00:11:22.164663', 24208067),
(25, 'E', 'Pendiente', 'Pendiente', 'usuarios@gmail.com', NULL, 'Pendiente', '$2y$10$h/qndNDZy7nPeARjf9opmOvCqqGA3adl4KU8yKrMN5Zcg0/Qs1hX.', '2026-04-03 01:51:13.609624', 12345678);

-- Secuencias
SELECT pg_catalog.setval('public.baremo_preguntas_id_seq', 14, true);
SELECT pg_catalog.setval('public.personal_access_tokens_id_seq', 1, false);
SELECT pg_catalog.setval('public.respuestas_baremo_id_seq', 1, false);
SELECT pg_catalog.setval('public.users_id_seq', 1, false);
SELECT pg_catalog.setval('public.usuarios_id_seq', 25, true);

-- Primary Keys
ALTER TABLE ONLY public.baremo_preguntas ADD CONSTRAINT baremo_preguntas_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.password_reset_tokens ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);
ALTER TABLE ONLY public.personal_access_tokens ADD CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.personal_access_tokens ADD CONSTRAINT personal_access_tokens_token_unique UNIQUE (token);
ALTER TABLE ONLY public.respuestas_baremo ADD CONSTRAINT respuestas_baremo_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.usuarios ADD CONSTRAINT uq_email UNIQUE (email);
ALTER TABLE ONLY public.users ADD CONSTRAINT users_email_unique UNIQUE (email);
ALTER TABLE ONLY public.users ADD CONSTRAINT users_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.usuarios ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id);

-- Índices
CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);

-- Foreign Keys
ALTER TABLE ONLY public.respuestas_baremo ADD CONSTRAINT respuestas_baremo_id_aspirante_fkey FOREIGN KEY (id_aspirante) REFERENCES public.usuarios(id);
ALTER TABLE ONLY public.respuestas_baremo ADD CONSTRAINT respuestas_baremo_id_pregunta_fkey FOREIGN KEY (id_pregunta) REFERENCES public.baremo_preguntas(id);
