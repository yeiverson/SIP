# Guía de Instalación

---

## Requisitos

| Software | Versión | Descarga |
|----------|---------|----------|
| XAMPP | 8.x+ | https://www.apachefriends.org/ |
| PostgreSQL | 15+ | https://www.postgresql.org/download/ |
| Git | cualquier | https://git-scm.com/ |

---

## Paso 1: Clonar el repositorio

```bash
git clone https://github.com/yeiverson/SIP.git
cd SIP
```

## Paso 2: Configurar XAMPP

1. Instalar XAMPP con Apache y PHP 8+
2. Copiar la carpeta `SIP` dentro de `C:\xampp\htdocs\`
3. Iniciar Apache desde el Panel de Control de XAMPP

## Paso 3: Configurar PostgreSQL

1. Instalar PostgreSQL 15+ (incluye pgAdmin)
2. Durante la instalación, definir una contraseña para el usuario `postgres`
3. Abrir pgAdmin o psql y crear la base de datos:

```bash
psql -U postgres -c "CREATE DATABASE unefa_postgrados;"
```

4. Restaurar el backup:

```bash
psql -U postgres -d unefa_postgrados -f database/backup_completo.sql
```

> **Nota:** El backup incluye todas las tablas, índices, restricciones y datos de prueba.

## Paso 4: Configurar conexión

Editar `config/database.php`:

```php
$db_host     = "localhost";
$db_port     = "5432";
$db_name     = "unefa_postgrados";
$db_user     = "postgres";
$db_password = "tu_contraseña_aqui";
```

## Paso 5: Verificar

1. Abrir el navegador en: `http://localhost/SIP/`
2. Iniciar sesión con cualquiera de los usuarios de prueba

---

## Usuarios de Prueba

| Rol | Documento | Tipo | Contraseña |
|-----|-----------|------|------------|
| Administrador | 1 | V | `password` |
| Coordinador | 87654321 | V | `password` |
| Docente | 11223344 | V | `password` |
| Secretaría | 55667788 | V | `password` |
| Aspirante | 99887766 | V | `password` |
| Estudiante | 44332211 | V | `password` |
| Director | 22334455 | V | `password` |
| Pasaporte | FR98765432 | P | `password` |

---

## Solución de Problemas

**Error:** `SQLSTATE[08006] could not connect to server`
- Verificar que PostgreSQL esté en ejecución
- Verificar puerto (5432 por defecto)
- Verificar credenciales en `config/database.php`

**Error:** `SQLSTATE[42P01] undefined table`
- La base de datos no tiene las tablas necesarias
- Ejecutar: `psql -U postgres -d unefa_postgrados -f database/backup_completo.sql`

**Error:** página en blanco
- Activar `display_errors` en `php.ini` o revisar logs de Apache
- El sistema usa `error_log()` para registrar errores de conexión

**Error:** las imágenes no se cargan
- Verificar que las rutas en CSS usan rutas relativas correctas
- Verificar que `obtener_ruta_base()` en `includes/auth_check.php` funciona
