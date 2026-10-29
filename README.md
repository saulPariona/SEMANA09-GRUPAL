# Laboratorio 09A - Sistema de Registro de Contacto

Implementación de un formulario equivalente en PHP y JSP, con almacenamiento temporal en sesión, validación de entradas y escape de salida para prevención de XSS.

## Estructura

```text
Pract_grupal09/
├── php-app/
│   ├── index.php
│   ├── registro.php
│   ├── db.php
│   └── styles.css
├── jsp-app/
│   ├── index.jsp
│   ├── registro.jsp
│   ├── styles.css
│   └── WEB-INF/web.xml
└── README.md
```

## Persistencia en MySQL/MariaDB

Además de conservar los contactos en la sesión (requisito de la práctica), cada registro válido se persiste en MySQL/MariaDB. El esquema está en [database.sql](./database.sql) y utiliza el puerto `3306`.

Crear la base y la tabla desde una consola de XAMPP:

```powershell
& "C:\xampp\mysql\bin\mysql.exe" -u root < database.sql
```

La configuración local utilizada es `127.0.0.1:3306`, usuario `root` y contraseña vacía, que es la configuración predeterminada de XAMPP. Si se configura una contraseña, actualizar [php-app/db.php](./php-app/db.php) y las credenciales JDBC de [jsp-app/registro.jsp](./jsp-app/registro.jsp). La aplicación PHP usa `mysqli` y consultas preparadas; JSP usa JDBC y `PreparedStatement`.

En XAMPP, el driver JDBC instalado para Tomcat es `C:\xampp\tomcat\lib\mysql-connector-j-9.4.0.jar`. Después de agregar o cambiar este archivo se debe reiniciar Tomcat.

## Despliegue en el entorno indicado

### PHP con XAMPP/Apache en 8080

1. Copiar `php-app` a `C:\xampp\htdocs\Pract_grupal09\php-app`.
2. En `httpd.conf`, confirmar `Listen 8080` y `ServerName localhost:8080`. El puerto 80 puede permanecer ocupado por PID 4; no es necesario liberarlo para esta práctica.
3. Iniciar Apache desde XAMPP y abrir `http://localhost:8080/Pract_grupal09/php-app/`.
4. MySQL en 3306 debe estar iniciado; los datos se guardan en la base y también en `$_SESSION`.

### JSP con Tomcat en 8081

1. Este equipo utiliza Apache Tomcat 8.5.99 con la API `javax.servlet`.
2. Copiar `jsp-app` como `C:\xampp\tomcat\webapps\registro-contacto` (o desplegar el ZIP/WAR con ese nombre).
3. En `conf/server.xml`, confirmar el conector `<Connector port="8081" ... />`.
4. Iniciar Tomcat y abrir `http://localhost:8081/registro-contacto/`.

Si se inicia desde PowerShell y Tomcat no está registrado como servicio:

```powershell
$env:JAVA_HOME = "C:\Program Files\Eclipse Adoptium\jdk-21.0.12.101-hotspot"
$env:JRE_HOME = $env:JAVA_HOME
$env:CATALINA_HOME = "C:\xampp\tomcat"
$env:CATALINA_BASE = "C:\xampp\tomcat"
& "C:\xampp\tomcat\bin\startup.bat"
```

> `jsp-app` usa scriptlets (`<% %>`) para recorrer la sesión, tal como permite el enunciado. Incluye una función local de escape HTML equivalente a `c:out`. Está adaptado a Tomcat 8.5 (`javax.servlet`). Para guardar en MySQL necesita el JAR `mysql-connector-j` en `TOMCAT_HOME/lib`.

## Flujo HTTP

```text
Cliente/navegador
   │ 1. DNS resuelve localhost (sin DNS externo en desarrollo)
   │ 2. TCP/IP conecta a localhost:8080 o localhost:8081
   │ 3. HTTP Request: POST /.../registro.php (o registro.jsp)
   ▼
Servidor Apache/Tomcat
   │ enruta la URL al recurso
   ▼
Backend PHP/JSP
   │ valida método y campos, sanitiza/escapa salida, actualiza sesión
   ▼
HTTP Response (303, 200, 405 o 422)
   │ navegador recibe HTML y sigue redirección cuando corresponde
   ▼
Render de la tabla de registros
```

## Seguridad, validación y errores

- Solo se acepta `POST` en el endpoint de registro; otros métodos reciben `405 Method Not Allowed`.
- Campos obligatorios, longitudes, correo y teléfono se validan en backend.
- PHP usa `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`; JSP usa su función `escapeHtml(...)` en cada valor renderizado. Nunca se imprime entrada sin escapar.
- Datos inválidos reciben `422 Unprocessable Entity`, con mensajes seguros.
- Registro válido usa `303 See Other`/`sendRedirect`, conserva datos en sesión y los inserta en `registro_contactos.contactos`.
- Si MySQL/JDBC no está disponible, se responde `500 Internal Server Error`; no se presenta un registro como exitoso.
- En producción se debe agregar HTTPS, cookies `HttpOnly`/`Secure`/`SameSite`, CSRF y persistencia en base de datos.

Pruebas rápidas:

```text
GET  http://localhost:8080/Pract_grupal09/php-app/       -> 200
POST http://localhost:8080/Pract_grupal09/php-app/registro.php -> 303
GET  http://localhost:8080/Pract_grupal09/php-app/registro.php -> 405
POST con campos inválidos -> 422

GET  http://localhost:8081/registro-contacto/             -> 200
POST http://localhost:8081/registro-contacto/registro.jsp -> 302
GET  .../registro.jsp -> 405
POST con campos inválidos -> 422
```

En Windows se pueden guardar evidencias con:

```powershell
curl.exe -i http://localhost:8080/Pract_grupal09/php-app/
curl.exe -i -X POST -d "nombre=A&email=incorrecto&telefono=123&mensaje=" http://localhost:8080/Pract_grupal09/php-app/registro.php
curl.exe -i http://localhost:8081/registro-contacto/
```

Para el informe final, incluir capturas de esas respuestas (`200`, `303/302`, `405`, `422`), logs de Apache/Tomcat, estructura de carpetas y ambas pantallas con al menos un registro exitoso.

## Matriz comparativa PHP vs JSP

| Aspecto | PHP | JSP |
|---|---|---|
| Ciclo de vida | Apache interpreta el script por solicitud (con OPcache puede reutilizar bytecode). | JSP se traduce a servlet y se compila; luego el contenedor atiende solicitudes. |
| Despliegue | Carpeta dentro de `htdocs`; Apache en este laboratorio: `8080`. | Aplicación/contexto dentro de `webapps`; Tomcat en `8081`. |
| Rendimiento | Excelente para formularios pequeños; depende de Apache, PHP y OPcache. | Buen rendimiento tras compilación; depende de JVM y configuración del contenedor. |
| Gestión de estado | `session_start()` y `$_SESSION` mantienen estado por cookie de sesión. | `HttpSession` (`session`) mantiene estado por cookie JSESSIONID. |
| Seguridad | Validación backend y `htmlspecialchars`; el runtime no sustituye controles de aplicación. | Validación backend y escape HTML; filtros, configuración web.xml y APIs Java amplían controles. |
| Ecosistema/madurez | Ecosistema web muy amplio, instalación simple y gran disponibilidad de hosting. | Ecosistema Java empresarial maduro, fuerte tipado y herramientas de servidor. |
| Manejo de errores | `http_response_code(405/422)` y páginas HTML de error. | `sendError`/`setStatus(422)` y páginas HTML de error. |

## Roles y evidencias

- **Arquitecto de Despliegue:** puertos, rutas `htdocs/webapps` y logs descritos arriba.
- **Desarrollador PHP:** `registro.php`, `$_POST`/`filter_input`, `session_start`, validación y `htmlspecialchars`.
- **Desarrollador JSP:** `request.getParameter`, `session`, scriptlets de recorrido, `web.xml`.
- **QA & Documentador:** casos HTTP, XSS, métodos no permitidos, matriz y guía de capturas.
