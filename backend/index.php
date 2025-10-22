<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/CmsService.php';
require_once __DIR__ . '/lib/Storage.php';

$storage = new Storage(__DIR__ . '/data/cms.json');
$service = new CmsService($storage);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$segments = array_values(array_filter(explode('/', trim($path, '/'))));

function readJsonBody(): array
{
    $contents = file_get_contents('php://input');
    if ($contents === false || trim($contents) === '') {
        return [];
    }

    $data = json_decode($contents, true);
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        throw new InvalidArgumentException('JSON inválido: ' . json_last_error_msg());
    }

    return $data ?? [];
}

function respond(int $status, $payload = null): void
{
    http_response_code($status);
    if ($payload !== null) {
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}

try {
    if (count($segments) === 0 || $segments[0] !== 'api') {
        respond(200, [
            'name' => 'FutbolFolder CMS API',
            'version' => '2.0.0',
            'endpoints' => [
                '/api/pnts',
                '/api/tiempos',
                '/api/carpetas',
                '/api/report/resumen',
                '/api/report/agenda?date=YYYY-MM-DD',
            ],
        ]);
        return;
    }

    $method = $_SERVER['REQUEST_METHOD'];

    if ($segments[1] ?? null) {
        switch ($segments[1]) {
            case 'pnts':
                if ($method === 'GET' && count($segments) === 2) {
                    respond(200, $service->listPnts());
                    return;
                }

                if ($method === 'POST' && count($segments) === 2) {
                    $payload = readJsonBody();
                    respond(201, $service->createPnt($payload));
                    return;
                }

                if (isset($segments[2])) {
                    $identifier = $segments[2];

                    if ($method === 'GET') {
                        $pnt = $service->getPnt($identifier);
                        if ($pnt === null) {
                            respond(404, ['error' => 'PNT inexistente']);
                        } else {
                            respond(200, $pnt);
                        }
                        return;
                    }

                    if ($method === 'PUT') {
                        $payload = readJsonBody();
                        respond(200, $service->updatePnt($identifier, $payload));
                        return;
                    }

                    if ($method === 'DELETE') {
                        $service->deletePnt($identifier);
                        respond(204);
                        return;
                    }
                }
                break;

            case 'tiempos':
                if ($method === 'GET' && count($segments) === 2) {
                    respond(200, $service->listTiempos());
                    return;
                }

                if ($method === 'POST' && count($segments) === 2) {
                    $payload = readJsonBody();
                    respond(201, $service->createTiempo($payload));
                    return;
                }

                if (isset($segments[2])) {
                    $identifier = $segments[2];

                    if ($method === 'GET') {
                        $tiempo = $service->getTiempo($identifier);
                        if ($tiempo === null) {
                            respond(404, ['error' => 'Tiempo inexistente']);
                        } else {
                            respond(200, $tiempo);
                        }
                        return;
                    }

                    if ($method === 'PUT') {
                        $payload = readJsonBody();
                        respond(200, $service->updateTiempo($identifier, $payload));
                        return;
                    }

                    if ($method === 'DELETE') {
                        $service->deleteTiempo($identifier);
                        respond(204);
                        return;
                    }

                    if (isset($segments[3]) && $segments[3] === 'pnts') {
                        if ($method === 'POST') {
                            $payload = readJsonBody();
                            $pntId = $payload['pnt_id'] ?? null;
                            if ($pntId === null) {
                                throw new InvalidArgumentException('Se requiere pnt_id');
                            }
                            respond(200, $service->addPntToTiempo($identifier, (string) $pntId));
                            return;
                        }

                        if ($method === 'DELETE' && isset($segments[4])) {
                            respond(200, $service->removePntFromTiempo($identifier, $segments[4]));
                            return;
                        }
                    }
                }
                break;

            case 'carpetas':
                if ($method === 'GET' && count($segments) === 2) {
                    respond(200, $service->listCarpetas());
                    return;
                }

                if ($method === 'POST' && count($segments) === 2) {
                    $payload = readJsonBody();
                    respond(201, $service->createCarpeta($payload));
                    return;
                }

                if (isset($segments[2])) {
                    $identifier = $segments[2];

                    if ($method === 'GET') {
                        $carpeta = $service->getCarpeta($identifier);
                        if ($carpeta === null) {
                            respond(404, ['error' => 'Carpeta inexistente']);
                        } else {
                            respond(200, $carpeta);
                        }
                        return;
                    }

                    if ($method === 'PUT') {
                        $payload = readJsonBody();
                        respond(200, $service->updateCarpeta($identifier, $payload));
                        return;
                    }

                    if ($method === 'DELETE') {
                        $service->deleteCarpeta($identifier);
                        respond(204);
                        return;
                    }

                    if (isset($segments[3]) && $segments[3] === 'tiempos') {
                        if ($method === 'POST') {
                            $payload = readJsonBody();
                            $tiempoId = $payload['tiempo_id'] ?? null;
                            if ($tiempoId === null) {
                                throw new InvalidArgumentException('Se requiere tiempo_id');
                            }
                            respond(200, $service->addTiempoToCarpeta($identifier, (string) $tiempoId));
                            return;
                        }

                        if ($method === 'DELETE' && isset($segments[4])) {
                            respond(200, $service->removeTiempoFromCarpeta($identifier, $segments[4]));
                            return;
                        }
                    }
                }
                break;

            case 'report':
                if ($method === 'GET' && isset($segments[2])) {
                    if ($segments[2] === 'resumen') {
                        respond(200, $service->resumenGeneral());
                        return;
                    }

                    if ($segments[2] === 'agenda') {
                        $date = $_GET['date'] ?? null;
                        if ($date === null) {
                            throw new InvalidArgumentException('Parámetro date obligatorio');
                        }
                        respond(200, $service->agendaPorFecha((string) $date));
                        return;
                    }
                }
                break;
        }
    }

    respond(404, ['error' => 'Ruta no encontrada']);
} catch (InvalidArgumentException $exception) {
    respond(400, ['error' => $exception->getMessage()]);
} catch (RuntimeException $exception) {
    respond(500, ['error' => $exception->getMessage()]);
} catch (Throwable $exception) {
    respond(500, ['error' => 'Error inesperado', 'detalle' => $exception->getMessage()]);
}
