# Normalizacion de la Base de Datos (1FN, 2FN, 3FN)

## 1FN (Primera Forma Normal)

- Cada tabla tiene una clave primaria (`usuarios.id`, `contactos.id`).
- No hay columnas multivaluadas ni listas en un solo campo.
- Cada atributo almacena valores atomicos:
  - `usuarios`: nombre, correo, password_hash.
  - `contactos`: nombre, pais_iso, pais_nombre, codigo_pais, telefono, telefono_e164, correo, direccion, favorito, notas.

## 2FN (Segunda Forma Normal)

- Ninguna tabla usa clave primaria compuesta, por lo que no existen dependencias parciales.
- Cada campo no clave depende por completo de la clave primaria de su tabla.

## 3FN (Tercera Forma Normal)

- No hay dependencias transitivas:
  - Los datos de autenticacion del usuario viven en `usuarios`.
  - Los datos de contacto viven en `contactos`.
  - `contactos.usuario_id` solo referencia al propietario (FK), sin duplicar datos de usuario.
- Se evita redundancia de datos personales del usuario en la tabla de contactos.

## Integridad referencial

- `contactos.usuario_id` es clave foranea hacia `usuarios.id`.
- Regla `ON DELETE CASCADE`: si se elimina un usuario, sus contactos tambien se eliminan.
- Restriccion `UNIQUE(usuario_id, telefono_e164)` para no repetir telefonos dentro de una misma cuenta.

## Beneficios del diseno

- Evita duplicidad y anomalias de insercion/actualizacion/eliminacion.
- Facilita mantenimiento del CRUD por usuario.
- Mantiene consistencia entre autenticacion y agenda de contactos.
