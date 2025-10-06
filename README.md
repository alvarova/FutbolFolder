# FutbolFolder CMS

Plataforma ligera para gestionar piezas publicitarias no tradicionales (PNT), organizarlas en tiempos de emisión y planificar carpetas diarias listas para salir al aire. La aplicación se divide en un **backend PHP** expuesto vía API REST y un **frontend HTML5 + React** que consume dichos servicios.

## Arquitectura

- **Backend**: PHP 8+ sin dependencias externas. Expone endpoints RESTful para CRUD de PNTs, tiempos y carpetas; permite asociar entidades, generar resúmenes y consultar agendas diarias. La persistencia se realiza sobre un archivo JSON local (`backend/data/cms.json`).
- **Frontend**: interfaz React (via CDN) que utiliza HTML5, CSS moderno y JavaScript modular. Ofrece formularios guiados para altas y asignaciones, dashboards con tarjetas para visualizar la programación y paneles de resumen y agenda.
- **Comunicación**: ambos módulos se comunican mediante JSON. Se incluyen cabeceras CORS para que el frontend pueda ejecutarse en un puerto diferente al backend durante el desarrollo.

## Funcionalidades clave

- Alta, baja y modificación de PNTs con título, guion, formato, marca, duración y notas.
- Creación de tiempos de emisión y asignación/quita de PNTs dentro de cada tiempo.
- Programación de carpetas con fecha de salida al aire, observaciones y asociación de tiempos.
- Reportes automáticos: resumen general (duraciones acumuladas) y agenda diaria filtrada por fecha.
- Persistencia local robusta: toda operación actualiza el archivo JSON evitando pérdida de datos.

## Puesta en marcha

### 1. Backend PHP

```bash
cd backend
php -S localhost:8000 index.php
```

Esto inicia la API REST en `http://localhost:8000/api`.

### 2. Frontend React

En otra terminal:

```bash
cd frontend
php -S localhost:5173
```

Luego abre `http://localhost:5173` en tu navegador. El frontend apunta por defecto al backend en `http://localhost:8000/api`. Si necesitas cambiar el host o puerto puedes definir `window.APP_CONFIG = { apiUrl: 'http://otro-host:puerto/api' };` antes de cargar `app.js`.

## Endpoints principales

| Método | Ruta                                   | Descripción |
| ------ | -------------------------------------- | ----------- |
| GET    | `/api/pnts`                            | Lista todos los PNT registrados. |
| POST   | `/api/pnts`                            | Crea un nuevo PNT. |
| GET    | `/api/pnts/{id}`                       | Obtiene un PNT específico. |
| PUT    | `/api/pnts/{id}`                       | Actualiza un PNT existente. |
| DELETE | `/api/pnts/{id}`                       | Elimina un PNT y lo desasocia de los tiempos. |
| GET    | `/api/tiempos`                         | Lista los tiempos de emisión. |
| POST   | `/api/tiempos`                         | Crea un tiempo nuevo. |
| POST   | `/api/tiempos/{id}/pnts`               | Asocia un PNT a un tiempo. |
| DELETE | `/api/tiempos/{id}/pnts/{pntId}`       | Elimina la asociación del PNT dentro del tiempo. |
| GET    | `/api/carpetas`                        | Lista las carpetas programadas. |
| POST   | `/api/carpetas`                        | Crea una carpeta con fecha de emisión. |
| POST   | `/api/carpetas/{id}/tiempos`           | Vincula un tiempo a la carpeta. |
| DELETE | `/api/carpetas/{id}/tiempos/{tiempo}`  | Quita el tiempo de la carpeta. |
| GET    | `/api/report/resumen`                  | Devuelve duraciones acumuladas por carpeta y tiempo. |
| GET    | `/api/report/agenda?date=YYYY-MM-DD`   | Agenda diaria filtrada por fecha de emisión. |

Todas las rutas aceptan/retornan JSON. Ante validaciones fallidas se responde con código 400 y un mensaje descriptivo.

## Persistencia de datos

El archivo `backend/data/cms.json` se genera automáticamente al iniciar la API. Puedes versionarlo o respaldarlo para conservar la programación entre despliegues.

## Desarrollo y pruebas

- Lint rápido del backend:

  ```bash
  php -l backend/index.php
  php -l backend/lib/CmsService.php
  php -l backend/lib/Storage.php
  ```

- Puedes escribir pruebas end-to-end con herramientas como [Pest](https://pestphp.com/) o [PHPUnit](https://phpunit.de/); la estructura actual facilita su incorporación en `backend/tests/`.

## Próximos pasos sugeridos

- Incorporar autenticación por roles para productores, comerciales y clientes.
- Añadir exportaciones en PDF/Excel desde el backend para compartir la programación.
- Implementar filtros avanzados en el frontend (por marca, duración, formato, etc.).
- Desplegar en un hosting PHP y servir el frontend con un CDN o un servidor estático dedicado.
