# Inventario de alimentos con PHP y CodeIgniter 4

Proyecto académico para aprender a iniciar sesión y gestionar alimentos con el patrón **Modelo–Vista–Controlador (MVC)**. Permite registrar, consultar, editar y eliminar alimentos desde el navegador.

Repositorio: [inventario-mvc en GitHub](https://github.com/0995655988Paul/inventario-mvc).

Video de demostración: [Ver el proyecto en YouTube](https://youtu.be/gjPN6Cz-gHI).

Cada alimento tiene cuatro campos: `id`, `nombre`, `precio` y `stock`. El precio se expresa en dólares y la cantidad en unidades enteras. En el código se conservan el modelo `ProductoModel`, el controlador `Productos`, la tabla `productos` y las rutas `/productos`. Los datos se guardan en SQLite, en el archivo local `writable/inventario.db`; no necesitas instalar un servidor MySQL.

## Funciones

- Inicio de sesión con usuario y contraseña.
- Registro de cuentas con usuario, correo, teléfono y confirmación de contraseña.
- Acceso al inventario únicamente con una sesión autenticada.
- Listado, creación, edición y eliminación de alimentos (CRUD).
- Validación de los datos y mensajes de resultado.
- Contraseñas guardadas como hashes bcrypt.
- Protección CSRF en los formularios y cierre de sesión.

## Interfaz

El diseño académico usa rojo, blanco y azul gris oscuro, inspirado en los sitios de [UDLA](https://www.udla.edu.ec/) y [Hanaska](https://www.hanaska.com/).

El listado muestra resúmenes calculados con los datos reales: alimentos registrados, unidades disponibles y alimentos **Por reponer**. Por reponer cuenta los registros con una cantidad de 0 a 5 unidades, incluidos los agotados. Cada fila muestra su estado:

| Cantidad disponible | Estado |
| --- | --- |
| 0 unidades | Agotado |
| De 1 a 5 unidades | Pocas unidades |
| Más de 5 unidades | Disponible |

## Tecnologías y requisitos

| Herramienta | Uso |
| --- | --- |
| PHP 8.2 o superior | Ejecutar la aplicación |
| CodeIgniter 4.7.4 | Framework MVC; versión fijada en `composer.lock` |
| SQLite3 | Base de datos en un archivo |
| Composer | Instalar las dependencias de PHP |
| Git | Versionar el código y entregarlo en un repositorio |
| Visual Studio Code | Editar el proyecto y usar su terminal |

PHP debe tener habilitadas las extensiones `intl`, `mbstring` y `sqlite3`. La carpeta `writable/` debe permitir escritura para la base de datos, las sesiones y los registros.

## Instalación en Mac

### 1. Preparar PHP y Composer

Si tienes Homebrew instalado, ejecuta en Terminal:

```bash
brew install php composer
php -v
composer --version
php -m
git --version
```

Comprueba que `php -v` muestre PHP 8.2 o superior y que la lista de `php -m` incluya `intl`, `mbstring` y `sqlite3`. Si falta Git, instala las herramientas de línea de comandos de Apple con `xcode-select --install` y sigue el diálogo de instalación.

### 2. Abrir la carpeta del proyecto

Clona el repositorio con una cuenta que tenga acceso, o usa la carpeta de la entrega:

```bash
git clone https://github.com/0995655988Paul/inventario-mvc.git
cd inventario-mvc
```

Abre esa carpeta con **Archivo → Abrir carpeta** en VS Code. Luego abre **Terminal → Nueva terminal** y confirma que estás en la carpeta que contiene `spark` y `composer.json`.

Ejecuta los pasos siguientes desde esa carpeta:

```bash
composer install
cp .env.example .env
```

`composer install` usa las versiones de `composer.lock`. Copia `.env.example` solo en la primera instalación; si ya tienes un `.env`, conserva tu configuración.

### 3. Crear la base de datos y el usuario de demostración

```bash
php spark migrate
php spark db:seed UsuarioSeeder
```

Las migraciones crean las tablas `usuarios` y `productos`, y añaden correo y teléfono a los usuarios sin borrar los datos existentes. El seeder crea el usuario `admin` si todavía no existe, usando `password_hash()` para su contraseña.

### 4. Iniciar la aplicación

```bash
php spark serve
```

Deja esa terminal abierta y visita [http://localhost:8080](http://localhost:8080). Usa estos datos de demostración:

| Campo | Valor |
| --- | --- |
| Usuario | `admin` |
| Contraseña | `Admin123!` |

Para detener el servidor, presiona **Ctrl+C** en la terminal. Este usuario es para la práctica local; antes de usar la aplicación con datos reales, cambia las credenciales de demostración.

También puedes pulsar **Registrar cuenta** en el login para crear tu propio usuario. Al completar el registro, vuelves al login y entras con el usuario y la contraseña que elegiste.

## Configuración local

El archivo `.env.example` contiene la configuración necesaria:

```ini
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080/'
app.indexPage = ''
database.default.DBDriver = SQLite3
database.default.database = inventario.db
security.csrfProtection = session
```

CodeIgniter resuelve el nombre `inventario.db` dentro de `writable/`. SQLite no requiere un usuario ni una contraseña de servidor para esta práctica. Si cambias el puerto del servidor, ajusta también `app.baseURL`.

`.env` contiene tu configuración local y no debe subirse al repositorio. Tampoco subas `writable/inventario.db`, archivos de sesión ni registros. `.env.example` sí se comparte porque permite reproducir la instalación sin exponer secretos.

## Cómo funciona MVC

MVC separa las responsabilidades para que sea fácil encontrar dónde cambiar cada parte:

| Parte | Responsabilidad | Archivos principales |
| --- | --- | --- |
| Modelo | Consultar y guardar los datos; definir los campos permitidos | `app/Models/UsuarioModel.php`, `app/Models/ProductoModel.php` |
| Vista | Mostrar HTML, datos y formularios | `app/Views/login.php`, `app/Views/registro.php`, `app/Views/productos/index.php`, `app/Views/productos/formulario.php` |
| Controlador | Recibir la petición, validar y decidir qué respuesta enviar | `app/Controllers/Home.php`, `app/Controllers/Productos.php` |
| Rutas | Relacionar cada URL y método HTTP con un controlador | `app/Config/Routes.php` |
| Filtro | Comprobar la sesión antes de permitir el acceso | `app/Filters/AuthFilter.php`, alias `auth` en `app/Config/Filters.php` |
| Migración | Crear las tablas y añadir campos de contacto | `app/Database/Migrations/20261005010840_CrearTablas.php`, `app/Database/Migrations/20261008000001_AgregarContactoUsuarios.php` |
| Seeder | Crear el usuario de demostración | `app/Database/Seeds/UsuarioSeeder.php` |

**Ejemplo: guardar un alimento.** El navegador envía un formulario a `POST /productos/guardar`. CodeIgniter verifica la sesión con el filtro `auth` y comprueba el token CSRF. El controlador `Productos::guardar` valida los campos y utiliza `ProductoModel` para insertar el registro. Finalmente, redirige al listado; una nueva petición consulta los alimentos y la vista muestra el resultado.

El modelo no genera HTML, la vista no hace consultas a la base de datos y el controlador coordina el proceso.

## Rutas

Las rutas están declaradas de forma explícita; el enrutamiento automático está desactivado.

| Método | URL | Acción |
| --- | --- | --- |
| GET | `/` | Mostrar el formulario de inicio de sesión |
| POST | `/login` | Comprobar las credenciales |
| GET | `/registro` | Mostrar el formulario de registro |
| POST | `/registro` | Validar y crear la cuenta |
| GET | `/productos` | Listar los alimentos |
| GET | `/productos/nuevo` | Mostrar el formulario de creación |
| POST | `/productos/guardar` | Crear un alimento |
| GET | `/productos/{id}/editar` | Mostrar el formulario de edición |
| POST | `/productos/{id}/actualizar` | Actualizar un alimento |
| POST | `/productos/{id}/eliminar` | Eliminar un alimento |
| POST | `/salir` | Destruir la sesión y volver al inicio |

En `{id}` se usa el identificador numérico del alimento, por ejemplo `/productos/1/editar`. Las rutas del grupo `productos` requieren el filtro `auth`. Las acciones que cambian datos usan POST y un token CSRF; el cierre de sesión también requiere autenticación.

## Contraseñas, sesión y protección del CRUD

El seeder y el registro generan un hash bcrypt:

```php
password_hash('Admin123!', PASSWORD_BCRYPT);
```

Al iniciar sesión, el controlador compara la contraseña recibida con el hash almacenado:

```php
password_verify($password, $registro['password']);
```

Un **hash no es un cifrado reversible**: no se descifra para recuperar la contraseña. bcrypt incluye una sal aleatoria, por lo que la misma contraseña puede producir hashes diferentes. No se guarda la contraseña en texto plano ni se usa MD5.

Después de comprobar las credenciales, la aplicación regenera el identificador de sesión y guarda `usuario_id` y `usuario`. El filtro revisa esa sesión en cada petición al inventario. Al cerrar sesión se destruye, por lo que volver a escribir `/productos` lleva al login.

Los formularios incluyen `csrf_field()` y la configuración usa tokens asociados a la sesión. La protección CSRF comprueba que el envío incluya un token válido. Además, el servidor valida los valores recibidos, el modelo limita los campos que pueden guardarse y las vistas muestran los datos con `esc()` para evitar interpretar contenido del usuario como HTML.

| Campo | Validación en el servidor |
| --- | --- |
| `nombre` | Obligatorio, máximo 100 caracteres; se recortan los espacios de los extremos |
| `precio` | Desde `0` hasta `99999999.99`, máximo dos decimales; se usa punto, por ejemplo `2.50` |
| `stock` | Entero desde `0` hasta `999999999`; no admite negativos ni decimales |

Los controles del navegador ayudan a completar el formulario, pero la validación del servidor también se aplica cuando una petición se modifica manualmente.

## Validaciones del registro

Todos los campos del formulario de registro son obligatorios. El servidor aplica estas comprobaciones antes de guardar la cuenta:

| Campo | Reglas |
| --- | --- |
| Usuario | De 3 a 30 caracteres: letras sin tildes, números, punto, guion y guion bajo; no puede repetirse |
| Correo | Formato de correo válido, máximo 254 caracteres; no puede repetirse |
| Teléfono | De 7 a 15 dígitos, con `+` inicial opcional; acepta espacios, paréntesis, puntos y guiones como formato |
| Contraseña | Mínimo 8 caracteres y máximo 72 bytes; requiere mayúscula, minúscula, número y símbolo |
| Confirmación | Debe coincidir exactamente con la contraseña |

Usuario y correo se guardan en minúsculas y sin espacios en los extremos. El teléfono se guarda sin separadores. Los errores aparecen junto al campo correspondiente; se conservan usuario, correo y teléfono, pero las contraseñas deben escribirse de nuevo. La contraseña se guarda con `password_hash(..., PASSWORD_BCRYPT)`, nunca en texto plano.

Estas comprobaciones validan el formato del correo y del teléfono; la aplicación no envía códigos por correo o SMS para verificar su titularidad. Todos los usuarios autenticados comparten el mismo inventario.

## Pruebas manuales

Esta guía describe las comprobaciones que debes realizar; no sustituye un registro de pruebas ejecutadas.

| Prueba | Pasos | Resultado esperado |
| --- | --- | --- |
| Acceso protegido | Sin iniciar sesión, abre `/productos` | Volver al formulario de login |
| Registro correcto | Pulsa Registrar cuenta y completa datos válidos, con contraseña y confirmación iguales | Crear la cuenta y volver al login con un mensaje de éxito |
| Registro inválido | Envía un teléfono con letras o contraseñas distintas | Mostrar errores sin crear la cuenta |
| Registro repetido | Intenta registrar el mismo usuario o correo, incluso con mayúsculas | Rechazar el dato repetido |
| Login de cuenta nueva | Entra con el usuario y la contraseña elegidos al registrarte | Abrir el listado de alimentos |
| Login incorrecto | Usa `admin` con una contraseña incorrecta | Mostrar un error y mantener cerrado el acceso |
| Login correcto | Usa `admin` y `Admin123!` | Abrir el listado de alimentos |
| Crear | Registra `Yogur natural`, precio `2.50`, cantidad `10` | Mostrar el alimento con un ID asignado y estado Disponible |
| Editar | Cambia su precio a `3.00` y stock a `8` | Mostrar los valores actualizados |
| Disponibilidad | Cambia la cantidad a `5` y después a `0` | Mostrar Pocas unidades y Agotado; contar el alimento como Por reponer |
| Validar | Intenta guardar nombre vacío, precio negativo o stock decimal | Rechazar el dato inválido sin guardar cambios |
| Persistencia | Recarga el listado | Conservar los alimentos registrados |
| Eliminar | Elimina el alimento de prueba y acepta la confirmación | Quitar el alimento del listado y actualizar los resúmenes |
| Cerrar sesión | Pulsa salir y luego abre `/productos` | Volver al login |

Para comprobar CSRF, puedes inspeccionar un formulario con las herramientas del navegador y quitar su campo oculto de token antes de enviarlo. La petición debe rechazarse sin guardar cambios; vuelve a cargar el formulario para continuar. En modo `development`, el rechazo puede mostrar una excepción de seguridad.

Las pruebas automáticas del registro usan SQLite en memoria, separado del inventario local:

```bash
composer test
```

## Problemas frecuentes

- **`Could not open input file: spark`:** la terminal está en otra carpeta. Entra a `inventario-mvc` y vuelve a ejecutar el comando.
- **Falta `intl`, `mbstring` o `sqlite3`:** revisa `php -m` y qué PHP está usando la terminal con `which php`.
- **No existe la tabla `usuarios` o `productos`:** verifica la configuración de `.env` y ejecuta `php spark migrate`.
- **El usuario de demostración no existe:** ejecuta `php spark db:seed UsuarioSeeder` después de las migraciones.
- **No se puede abrir la base de datos:** comprueba que exista `writable/` y que tu usuario pueda escribir allí.
- **El puerto 8080 está ocupado:** usa `php spark serve --port 8081`, cambia `app.baseURL` a `http://localhost:8081/` y abre esa dirección.
- **El formulario rechaza el token CSRF:** recarga la página y vuelve a enviarlo; evita reutilizar un formulario antiguo después de cerrar sesión.

## Entrega con Git

El remoto `origin` corresponde a [inventario-mvc](https://github.com/0995655988Paul/inventario-mvc). Para subir cambios desde la carpeta del proyecto:

```bash
git status --short
git add README.md .env.example .gitignore LICENSE composer.json composer.lock app public writable spark phpunit.dist.xml tests
git diff --cached --name-only
git commit -m "Actualiza inventario de alimentos MVC"
git push origin main
```

Antes del commit, revisa la lista de archivos preparados: no debe incluir `.env`, bases de datos, sesiones, registros ni `vendor/`. Mantén `composer.lock` para que otra persona instale las mismas versiones. Si Git solicita tu identidad, configura tu nombre y correo en este repositorio antes de repetir el commit.

El repositorio requiere autenticación para enviar cambios. En VS Code también puedes usar **Control de código fuente → Sincronizar cambios**.

## Guion para un video de menos de 3 minutos

| Tiempo aproximado | Demostración |
| --- | --- |
| 0:00–0:20 | Presentar el objetivo, PHP, CodeIgniter, SQLite y la separación MVC |
| 0:20–0:40 | Abrir `/productos` sin sesión y comprobar que vuelve al login |
| 0:40–1:00 | Iniciar sesión con el usuario de demostración `admin` |
| 1:00–1:45 | Crear un alimento, verlo en el listado, editarlo y eliminarlo |
| 1:45–2:20 | Mostrar `UsuarioSeeder.php` y explicar `password_hash()`; mostrar `password_verify()` en `Home.php` y mencionar sesión y CSRF |
| 2:20–2:45 | Cerrar sesión, volver a abrir `/productos` y comprobar el regreso al login |
| 2:45–2:55 | Señalar que el README explica cómo instalar y reproducir la práctica |

Video de la entrega: [Ver la demostración en YouTube](https://youtu.be/gjPN6Cz-gHI).

## Licencia y atribución

La aplicación parte del [CodeIgniter 4 Application Starter](https://github.com/codeigniter4/appstarter). Conserva la licencia MIT del starter y la atribución al British Columbia Institute of Technology y a CodeIgniter Foundation en `LICENSE`.
