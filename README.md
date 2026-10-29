# Ciph3r — API REST de productos, precios y divisas

API REST desarrollada con **Laravel 13** para administrar productos, precios asociados a distintas divisas y un catálogo de divisas. El proyecto incorpora autenticación mediante **Laravel Sanctum**, validación de solicitudes, relaciones con Eloquent, auditoría básica y eliminación lógica.

## Tecnologías

- PHP 8.3 y Laravel 13.
- MySQL 8.4.
- Docker y Docker Compose.
- Laravel Sanctum para autenticación mediante tokens.
- Eloquent ORM y pruebas automatizadas de Laravel.
- Postman para probar los endpoints.

## Requisitos previos

- Docker y Docker Compose instalados.
- Git (para clonar el repositorio).
- Postman (opcional, para probar la API).

> **Nota de configuración:** Los nombres de los servicios, puertos y credenciales dependen de `compose.yaml` y del archivo de entorno del proyecto. Los ejemplos siguientes suponen un servicio de aplicación llamado `app` y que la API está disponible en `http://localhost:8000`. Verifica esos valores en tu instalación.

## Instalación

1. Clonar el repositorio y entrar a la carpeta del proyecto:

   ```bash
   git clone https://github.com/arojast/ciph3r/
   cd laravel-backend-test
   ```

2. Levantar los contenedores:

   ```bash
   docker compose up -d --build
   ```

3. Preparar el entorno de Laravel, si todavía no existe `src/.env`:

   ```bash
   cp src/.env.example src/.env
   ```

   Revisar en `src/.env` las variables `APP_URL`, `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` para que coincidan con la configuración de Docker. Desde el contenedor de Laravel, `DB_HOST` normalmente corresponde al **nombre del servicio de base de datos**, no a `localhost`.
   Revise las variable `SEED_USER_EMAIL` y `SEED_USER_PASSWORD` que seran usadas para crear el usuario con el que se hara login

4. Instalar dependencias de PHP si la imagen no las instala durante su construcción:

   ```bash
   docker compose exec app composer install
   ```

5. Generar la clave de la aplicación:

   ```bash
   docker compose exec app php artisan key:generate
   ```

6. Ejecutar migraciones y seeders:

   ```bash
   docker compose exec app php artisan migrate --seed
   ```

   Los seeders contemplan un usuario inicial y tres divisas: **bolívar (VES), dólar estadounidense (USD) y euro (EUR)**. Consulta los seeders para conocer los datos concretos del usuario de prueba. No publiques contraseñas reales en el repositorio.

7. Comprobar que la API responde en la dirección configurada. En los ejemplos de este documento, la URL base es:

   ```text
   http://localhost:8000/api
   ```

> Si tu proyecto ya tiene dependencias instaladas, archivo `.env` y clave de aplicación, no necesitas repetir esos pasos. Evita ejecutar comandos de reinicio de base de datos sobre información que quieras conservar.

## Autenticación

La API utiliza tokens Bearer de **Laravel Sanctum**. Para obtener un token, realiza una solicitud de inicio de sesión:

```http
POST /api/login
Accept: application/json
Content-Type: application/json
```

Ejemplo de cuerpo:

```json
{
  "email": "usuario@ejemplo.com",
  "password": "TU_CONTRASEÑA_LOCAL"
}
```

Utiliza las credenciales del usuario creado por el seeder o de un usuario válido de tu instalación. Copia el token devuelto por el endpoint y envíalo en las solicitudes protegidas:

```http
Authorization: Bearer TU_TOKEN
Accept: application/json
```

No incluyas tokens ni contraseñas reales en archivos versionados.

## Endpoints

Los siguientes endpoints se encuentran documentados en la colección Postman `Ciph3r`.

### Autenticación

| Método | Ruta | Descripción |
|---|---|---|
| `POST` | `/api/login` | Iniciar sesión y obtener un token. |

### Productos

| Método | Ruta | Descripción |
|---|---|---|
| `GET` | `/api/products` | Listar productos activos. |
| `GET` | `/api/products/{id}` | Consultar un producto. |
| `POST` | `/api/products` | Crear un producto. |
| `PUT` | `/api/products/{id}` | Actualizar un producto. |
| `DELETE` | `/api/products/{id}` | Desactivar un producto mediante eliminación lógica. |

Ejemplo de creación de producto:

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

El valor de `currency_id` debe corresponder a una divisa válida en la base de datos.

### Precios por producto

| Método | Ruta | Descripción |
|---|---|---|
| `GET` | `/api/products/{id}/prices` | Listar precios asociados al producto. |
| `POST` | `/api/products/{id}/prices` | Registrar un precio para una divisa. |

Ejemplo:

```json
{
  "currency_id": 3,
  "price": 150.5000
}
```

El producto y la divisa deben existir y cumplir las validaciones de la API. La combinación de producto y divisa es única en la tabla de precios.

### Divisas

| Método | Ruta | Descripción |
|---|---|---|
| `GET` | `/api/currencies` | Listar divisas activas. |
| `GET` | `/api/currencies/{id}` | Consultar una divisa. |
| `POST` | `/api/currencies` | Crear una divisa. |
| `PUT` | `/api/currencies/{id}` | Actualizar una divisa. |
| `DELETE` | `/api/currencies/{id}` | Desactivar una divisa cuando las reglas de uso lo permiten. |

Ejemplo de creación de divisa:

```json
{
  "name": "Peso mexicano",
  "symbol": "MX$",
  "exchange_rate": 1.000000
}
```

La tasa de cambio del ejemplo es ilustrativa; no representa una cotización actual.

## Colección Postman

La colección se encuentra en:

```text
postman/Ciph3r.postman_collection.json
```

Para utilizarla:

1. Abrir Postman e importar el archivo JSON.
2. Configurar las variables de la colección.
3. Ejecutar **Autenticación → Login**.
4. Copiar el token de la respuesta en la variable local `token`.
5. Probar los endpoints de productos, precios y divisas.

| Variable | Descripción | Ejemplo |
|---|---|---|
| `base_url` | URL base de la API. | `http://localhost:8000/api` |
| `token` | Token Bearer obtenido en el login. | Mantener privado. |
| `product_id` | Identificador del producto de prueba. | `1` |
| `currency_id` | Identificador de la divisa de prueba. | `1` |
| `email` | Correo del usuario de prueba. | Mantener privado. |
| `password` | Contraseña del usuario de prueba. | Mantener privada. |

**Importante:** La colección debe compartirse sin credenciales ni tokens reales. Los IDs de ejemplo pueden variar según los datos de cada instalación. Algunas solicitudes de creación incluyen valores de ejemplo que deben ajustarse antes de ejecutarlas.

## Pruebas automatizadas

Para ejecutar las pruebas de Laravel:

```bash
docker compose exec app php artisan test
```

Durante el desarrollo se verificó una ejecución con **15 pruebas y 39 aserciones satisfactorias**. Esta cifra corresponde a esa ejecución y puede cambiar si se agregan o modifican pruebas.

**Recomendación:** Configura una base de datos independiente para pruebas (por ejemplo, mediante `src/.env.testing`) antes de ejecutar suites que creen, modifiquen o eliminen registros. No ejecutes pruebas destructivas contra una base de datos con información importante.

## Modelo de datos y decisiones técnicas

El sistema utiliza tres tablas principales:

- **`products`**: información de productos, precio base, divisa, costo fiscal y costo de fabricación.
- **`currencies`**: catálogo de divisas, símbolos y tasas de cambio.
- **`product_prices`**: precios de productos en distintas divisas, con una combinación única de `product_id` y `currency_id`.

Las tres tablas incorporan campos de auditoría:

```text
created_at
created_by
updated_at
updated_by
state
```

La columna `state` permite la **eliminación lógica**: `1` indica un registro activo y `0` uno inactivo. En lugar de borrar físicamente un registro, los endpoints de eliminación actualizan su estado según las reglas de negocio. Los campos `created_by` y `updated_by` registran al usuario responsable de las operaciones.

Se emplean **Form Requests** para validar los datos de entrada, **Eloquent** para las relaciones y consultas, y respuestas JSON directas desde los controladores.

## Seguridad y buenas prácticas

- No subir `src/.env`, tokens, contraseñas ni credenciales de bases de datos al repositorio.
- Mantener `src/.env.example` con valores de ejemplo que no sean secretos.
- Utilizar autenticación Bearer en las rutas protegidas.
- Validar los datos recibidos y comprobar la existencia y el estado de los registros relacionados.
- Mantener una base de datos separada para las pruebas automatizadas.

## Estado del proyecto

La implementación incluye autenticación, CRUD de productos, precios por divisa, CRUD de divisas, eliminación lógica, campos de auditoría, pruebas automatizadas y colección Postman.

