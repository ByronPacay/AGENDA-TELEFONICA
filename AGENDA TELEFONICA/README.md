# Agenda Telefonica con Cuentas (HTML, CSS, JS, PHP, MySQL + Bootstrap)

Proyecto web con:
- Registro e inicio de sesion por usuario.
- CRUD completo de contactos (Crear, Leer, Actualizar, Eliminar).
- Base de datos MySQL.
- Interfaz responsiva con Bootstrap 5.
- Alertas y confirmaciones con SweetAlert2.
- Validacion avanzada de telefono por pais (E.164).
- Busqueda por nombre o telefono y exportacion CSV.
- Campo favorito y observaciones por contacto.

## 1. Requisitos

- XAMPP (Apache + MySQL).
- Carpeta del proyecto en: `c:\xampp\htdocs\AGENDA TELEFONICA`.

## 2. Crear base de datos

1. Inicia Apache y MySQL desde XAMPP.
2. Abre phpMyAdmin (`http://localhost/phpmyadmin`).
3. Importa el archivo [database/schema.sql](/c:/xampp/htdocs/AGENDA%20TELEFONICA/database/schema.sql).
4. Revisa la normalizacion en [database/normalizacion.md](/c:/xampp/htdocs/AGENDA%20TELEFONICA/database/normalizacion.md).

## 3. Conexion a BD (archivo separado)

Se usa el archivo [config/conexion.php](/c:/xampp/htdocs/AGENDA%20TELEFONICA/config/conexion.php) basado en tu estructura:

```php
$Servidor = "localhost";
$Usuario = "root";
$password = "";
$BaseDeDatos = "bd_agenda_telefonica";
$conn = new mysqli($Servidor, $Usuario, $password, $BaseDeDatos);
```

## 4. Ejecutar proyecto

1. Abre en navegador: `http://localhost/AGENDA%20TELEFONICA/`
2. Crea cuenta.
3. Inicia sesion.
4. Usa el formulario para CRUD de contactos.

## 5. Estructura principal

- [index.php](/c:/xampp/htdocs/AGENDA%20TELEFONICA/index.php): login y registro.
- [agenda.php](/c:/xampp/htdocs/AGENDA%20TELEFONICA/agenda.php): panel CRUD.
- [assets/js/auth.js](/c:/xampp/htdocs/AGENDA%20TELEFONICA/assets/js/auth.js): logica de autenticacion.
- [assets/js/agenda.js](/c:/xampp/htdocs/AGENDA%20TELEFONICA/assets/js/agenda.js): logica CRUD.
- [api/](/c:/xampp/htdocs/AGENDA%20TELEFONICA/api): endpoints PHP.

## 6. Uso de GitHub

Ejemplo de flujo para subir el proyecto a GitHub:

```bash
git init
git add .
git commit -m "Agenda telefonica con cuentas y CRUD"
git branch -M main
git remote add origin https://github.com/TU-USUARIO/TU-REPOSITORIO.git
git push -u origin main
```

## 7. Endpoints API

- `POST /api/register.php`
- `POST /api/login.php`
- `POST /api/logout.php`
- `GET /api/session.php`
- `GET /api/contacts/list.php`
- `POST /api/contacts/create.php`
- `PUT /api/contacts/update.php`
- `DELETE /api/contacts/delete.php`
- `GET /api/contacts/export.php`

Todos los endpoints devuelven JSON con campos `ok` y `mensaje`.

## 8. Cumplimiento de criterios solicitados

1. **Base de datos normalizada (formas normales)**  
   Evidencia: [schema.sql](/c:/xampp/htdocs/AGENDA%20TELEFONICA/database/schema.sql) y [normalizacion.md](/c:/xampp/htdocs/AGENDA%20TELEFONICA/database/normalizacion.md).  
   El modelo cumple 1FN, 2FN y 3FN con relacion `usuarios (1) -> (N) contactos`.

2. **CRUD funcional en PHP y MySQL**  
   Evidencia: endpoints en [api/contacts](/c:/xampp/htdocs/AGENDA%20TELEFONICA/api/contacts).  
   Operaciones: crear (`create.php`), listar (`list.php`), actualizar (`update.php`), eliminar (`delete.php`).

3. **Uso de Bootstrap y diseno responsivo**  
   Evidencia: [index.php](/c:/xampp/htdocs/AGENDA%20TELEFONICA/index.php) y [agenda.php](/c:/xampp/htdocs/AGENDA%20TELEFONICA/agenda.php) con Bootstrap 5 CDN y grid responsivo.

4. **Validaciones de formularios**  
   Evidencia frontend: [auth.js](/c:/xampp/htdocs/AGENDA%20TELEFONICA/assets/js/auth.js), [agenda.js](/c:/xampp/htdocs/AGENDA%20TELEFONICA/assets/js/agenda.js).  
   Evidencia backend: [register.php](/c:/xampp/htdocs/AGENDA%20TELEFONICA/api/register.php), [login.php](/c:/xampp/htdocs/AGENDA%20TELEFONICA/api/login.php), [create.php](/c:/xampp/htdocs/AGENDA%20TELEFONICA/api/contacts/create.php), [update.php](/c:/xampp/htdocs/AGENDA%20TELEFONICA/api/contacts/update.php).

5. **Seguridad basica (SQL Injection)**  
   Evidencia: consultas preparadas (`$conn->prepare`, `bind_param`) en todos los endpoints PHP.  
   Adicional: contrasenas hasheadas con `password_hash`, validacion de sesion por usuario y escape de salida en frontend.

## 9. Reglas de agenda implementadas

- No se permite telefono duplicado para el mismo usuario.
- Guatemala acepta `2300-5000` y `+502-2300-5000` (se normaliza al formato internacional).
- Se rechazan telefonos incompletos o con caracteres no permitidos.
- Nombre obligatorio (maximo 50), direccion opcional (maximo 100), notas opcionales.
- Busqueda por nombre o telefono en tiempo real.
