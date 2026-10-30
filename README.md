# Ciph3r — API REST de productos, precios y divisas

API REST desarrollada con **Laravel 13** para administrar productos, precios asociados a distintas divisas y un catálogo de monedas.

El proyecto incorpora autenticación mediante **Laravel Sanctum**, validación de solicitudes, relaciones con Eloquent, campos de auditoría y eliminación lógica.

## Tecnologías

- PHP 8.3 y Laravel 13.
- MySQL 8.4.
- Docker y Docker Compose.
- Laravel Sanctum para autenticación mediante tokens.
- Eloquent ORM.
- Pruebas automatizadas de Laravel.
- Postman para probar los endpoints.

## Requisitos previos

- Docker con Docker Compose.
- Git.
- Postman (opcional).

## Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/arojast/ciph3r.git
cd ciph3r
```

La estructura principal del proyecto es:

```text
ciph3r/
├── README.md
├── Dockerfile
├── compose.yaml
├── src/
│   ├── app/
│   ├── database/
│   ├── routes/
│   ├── artisan
│   └── composer.json
└── postman/
```

### 2. Configurar las variables de entorno

Crear el archivo de configuración de Laravel:

```bash
cp src/.env.example src/.env
```

Editar `src/.env` y configurar:

```dotenv
APP_NAME=Ciph3r
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_KEY=

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=backend_test
DB_USERNAME=laravel
DB_PASSWORD=CONTRASENA_MYSQL

SEED_USER_EMAIL=usuario@ejemplo.com
SEED_USER_PASSWORD=CONTRASENA_DE_PRUEBA
```

Las variables `SEED_USER_EMAIL` y `SEED_USER_PASSWORD` permiten configurar el usuario inicial utilizado para iniciar sesión.

**Configuración de Docker Compose:** si `compose.yaml` utiliza variables de entorno para las credenciales de MySQL, crear también un archivo `.env` en la raíz del repositorio:

```dotenv
MYSQL_PASSWORD=CONTRASENA_MYSQL
MYSQL_ROOT_PASSWORD=CONTRASENA_ROOT
```

La contraseña de `MYSQL_PASSWORD` debe coincidir con `DB_PASSWORD` en `src/.env`.

No subir ninguno de estos archivos `.env` al repositorio.

### 3. Construir la imagen Docker

```bash
docker compose build
```

### 4. Instalar las dependencias de Laravel

```bash
docker compose run --rm --no-deps app composer install --no-interaction
```

Para instalaciones de despliegue donde no se requieren dependencias de desarrollo, se puede utilizar:

```bash
docker compose run --rm --no-deps app composer install --no-dev --optimize-autoloader --no-interaction
```

### 5. Generar la clave de aplicación

```bash
docker compose run --rm --no-deps app php artisan key:generate
```

Este comando configura `APP_KEY` en `src/.env`.

### 6. Levantar los contenedores

```bash
docker compose up -d
```

Verificar su estado:

```bash
docker compose ps
```

Los servicios `app` y `mysql` deben encontrarse en ejecución y MySQL debe mostrar un estado saludable.

### 7. Ejecutar las migraciones

```bash
docker compose exec app php artisan migrate
```

En un entorno configurado como producción:

```bash
docker compose exec app php artisan migrate --force
```

### 8. Ejecutar los seeders

Crear el usuario inicial:

```bash
docker compose exec app php artisan db:seed --class=UserSeeder
```

Crear las divisas iniciales:

```bash
docker compose exec app php artisan db:seed --class=CurrencySeeder
```

Los datos iniciales contemplan las divisas bolívar venezolano (VES), dólar estadounidense (USD) y euro (EUR).

En producción, agregar `--force` a los comandos de los seeders.

### 9. Verificar la API

La API estará disponible en:

```text
http://localhost:8000/api
```

Para consultar las rutas registradas:

```bash
docker compose exec app php artisan route:list --path=api
```

## Autenticación

La API utiliza tokens Bearer mediante Laravel Sanctum.

**Iniciar sesión:**

```http
POST /api/login
Accept: application/json
Content-Type: application/json
```

Ejemplo de solicitud:

```json
{
  "email": "usuario@ejemplo.com",
  "password": "CONTRASENA_DE_PRUEBA"
}
```

Las credenciales deben coincidir con las del usuario creado mediante el seeder.

Para acceder a los endpoints protegidos, enviar el token obtenido:

```http
Authorization: Bearer TU_TOKEN
Accept: application/json
```

## Endpoints

### Autenticación

| Método | Ruta | Descripción |
|---|---|---|
| POST | `/api/login` | Iniciar sesión y obtener un token. |

### Productos

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/products` | Listar productos activos. |
| GET | `/api/products/{id}` | Consultar un producto. |
| POST | `/api/products` | Crear un producto. |
| PUT | `/api/products/{id}` | Actualizar un producto. |
| DELETE | `/api/products/{id}` | Desactivar un producto. |

Ejemplo de creación:

```json
{
  "name": "Monitor de 27 pulgadas",
  "description": "Monitor Full HD con conexión HDMI",
  "price": 4250.0000,
  "currency_id": 1,
  "tax_cost": 680.0000,
  "manufacturing_cost": 2900.0000
}
```

### Precios por producto

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/products/{id}/prices` | Listar precios del producto. |
| POST | `/api/products/{id}/prices` | Registrar un precio para una divisa. |

Ejemplo:

```json
{
  "currency_id": 3,
  "price": 150.5000
}
```

La combinación de producto y divisa es única en la tabla de precios.

### Divisas

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/currencies` | Listar divisas activas. |
| GET | `/api/currencies/{id}` | Consultar una divisa. |
| POST | `/api/currencies` | Crear una divisa. |
| PUT | `/api/currencies/{id}` | Actualizar una divisa. |
| DELETE | `/api/currencies/{id}` | Desactivar una divisa según las reglas de negocio. |

Ejemplo de creación:

```json
{
  "name": "Peso mexicano",
  "symbol": "MX$",
  "exchange_rate": 1.000000
}
```

La tasa de cambio del ejemplo es ilustrativa.

## Colección Postman

La colección se encuentra en:

```text
postman/Ciph3r.postman_collection.json
```

Para utilizarla:

1. Importar el archivo JSON en Postman.
2. Configurar las variables de la colección.
3. Ejecutar **Autenticación → Login**.
4. Copiar el token obtenido en la variable `token`.
5. Probar los endpoints de productos, precios y divisas.

| Variable | Descripción |
|---|---|
| `base_url` | `http://localhost:8000/api` |
| `token` | Token obtenido durante el login. |
| `email` | Correo del usuario de prueba. |
| `password` | Contraseña del usuario de prueba. |
| `product_id` | ID del producto que se utilizará en las pruebas. |
| `currency_id` | ID de la divisa que se utilizará en las pruebas. |

No compartir la colección con contraseñas o tokens reales.

## Pruebas automatizadas

Para ejecutar las pruebas:

```bash
docker compose exec app php artisan test
```

Durante el desarrollo se verificó una ejecución satisfactoria de **15 pruebas y 39 aserciones**.

Se recomienda utilizar una base de datos independiente para las pruebas automatizadas, evitando modificar datos de desarrollo o producción.

## Modelo de datos

El sistema utiliza tres tablas principales:

- **products:** información de productos, precio base, divisa, costo fiscal y costo de fabricación.
- **currencies:** catálogo de divisas, símbolos y tasas de cambio.
- **product_prices:** precios de productos en distintas divisas.

Las tres tablas incorporan los siguientes campos de auditoría:

```text
created_at
created_by
updated_at
updated_by
state
```

El campo `state` permite implementar eliminación lógica:

- `1`: registro activo.
- `0`: registro inactivo.

Los campos `created_by` y `updated_by` permiten identificar al usuario responsable de las operaciones.

La implementación utiliza Form Requests para validaciones, Eloquent para relaciones y consultas, y respuestas JSON directas desde los controladores.

## Despliegue

El proyecto también se ha desplegado en una instancia AWS EC2 utilizando Docker Compose y MySQL.

La URL pública de la API puede configurarse mediante `APP_URL` y la variable `base_url` de Postman.

Para entornos públicos se recomienda:

- Utilizar `APP_ENV=production` y `APP_DEBUG=false`.
- Configurar HTTPS mediante un servidor web o proxy inverso.
- No exponer el puerto de MySQL a Internet.
- Utilizar contraseñas seguras y no versionar secretos.
- Evitar el servidor de desarrollo `php artisan serve` como solución de producción permanente.

## Estado del proyecto

La implementación incluye:

- Autenticación mediante Laravel Sanctum.
- CRUD de productos.
- Gestión de precios por divisa.
- CRUD de divisas.
- Eliminación lógica.
- Campos de auditoría.
- Migraciones y seeders.
- Pruebas automatizadas.
- Colección Postman.
- Configuración Docker para ejecución y despliegue.
