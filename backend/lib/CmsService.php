<?php

require_once __DIR__ . '/Storage.php';

class CmsService
{
    private Storage $storage;
    private array $state;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
        $this->state = $storage->load();
    }

    private function persist(): void
    {
        $this->storage->save($this->state);
    }

    private function ensureIdentifier(string $prefix, ?string $identifier): string
    {
        if ($identifier === null || trim($identifier) === '') {
            return uniqid($prefix, true);
        }

        return (string) $identifier;
    }

    public function listPnts(): array
    {
        return array_values($this->state['pnts']);
    }

    public function getPnt(string $identifier): ?array
    {
        return $this->state['pnts'][$identifier] ?? null;
    }

    public function createPnt(array $payload): array
    {
        $identifier = $this->ensureIdentifier('pnt_', $payload['identifier'] ?? null);
        if (isset($this->state['pnts'][$identifier])) {
            throw new InvalidArgumentException('Ya existe un PNT con ese identificador.');
        }

        $record = [
            'identifier' => $identifier,
            'titulo' => trim($payload['titulo'] ?? ''),
            'guion' => trim($payload['guion'] ?? ''),
            'duracion_segundos' => (int) ($payload['duracion_segundos'] ?? 0),
            'formato' => trim($payload['formato'] ?? ''),
            'marca' => trim($payload['marca'] ?? ''),
            'notas' => trim($payload['notas'] ?? ''),
        ];

        if ($record['titulo'] === '' || $record['guion'] === '') {
            throw new InvalidArgumentException('El título y el guion son obligatorios.');
        }

        $this->state['pnts'][$identifier] = $record;
        $this->persist();

        return $record;
    }

    public function updatePnt(string $identifier, array $payload): array
    {
        if (!isset($this->state['pnts'][$identifier])) {
            throw new InvalidArgumentException('PNT inexistente.');
        }

        $record = $this->state['pnts'][$identifier];
        $record['titulo'] = trim($payload['titulo'] ?? $record['titulo']);
        $record['guion'] = trim($payload['guion'] ?? $record['guion']);
        $record['duracion_segundos'] = (int) ($payload['duracion_segundos'] ?? $record['duracion_segundos']);
        $record['formato'] = trim($payload['formato'] ?? $record['formato']);
        $record['marca'] = trim($payload['marca'] ?? $record['marca']);
        $record['notas'] = trim($payload['notas'] ?? $record['notas']);

        if ($record['titulo'] === '' || $record['guion'] === '') {
            throw new InvalidArgumentException('El título y el guion son obligatorios.');
        }

        $this->state['pnts'][$identifier] = $record;
        $this->persist();

        return $record;
    }

    public function deletePnt(string $identifier): void
    {
        if (!isset($this->state['pnts'][$identifier])) {
            throw new InvalidArgumentException('PNT inexistente.');
        }

        unset($this->state['pnts'][$identifier]);

        foreach ($this->state['tiempos'] as &$tiempo) {
            $tiempo['pnt_ids'] = array_values(array_filter(
                $tiempo['pnt_ids'],
                fn ($id) => $id !== $identifier
            ));
        }

        $this->persist();
    }

    public function listTiempos(): array
    {
        return array_values($this->state['tiempos']);
    }

    public function getTiempo(string $identifier): ?array
    {
        return $this->state['tiempos'][$identifier] ?? null;
    }

    public function createTiempo(array $payload): array
    {
        $identifier = $this->ensureIdentifier('tiempo_', $payload['identifier'] ?? null);
        if (isset($this->state['tiempos'][$identifier])) {
            throw new InvalidArgumentException('Ya existe un tiempo con ese identificador.');
        }

        $record = [
            'identifier' => $identifier,
            'nombre' => trim($payload['nombre'] ?? ''),
            'duracion_total' => (int) ($payload['duracion_total'] ?? 0),
            'pnt_ids' => array_values(array_unique($payload['pnt_ids'] ?? [])),
        ];

        if ($record['nombre'] === '') {
            throw new InvalidArgumentException('El nombre del tiempo es obligatorio.');
        }

        $this->validatePntIds($record['pnt_ids']);

        $this->state['tiempos'][$identifier] = $record;
        $this->persist();

        return $record;
    }

    public function updateTiempo(string $identifier, array $payload): array
    {
        if (!isset($this->state['tiempos'][$identifier])) {
            throw new InvalidArgumentException('Tiempo inexistente.');
        }

        $record = $this->state['tiempos'][$identifier];
        $record['nombre'] = trim($payload['nombre'] ?? $record['nombre']);
        $record['duracion_total'] = (int) ($payload['duracion_total'] ?? $record['duracion_total']);

        if (isset($payload['pnt_ids'])) {
            $pntIds = array_values(array_unique($payload['pnt_ids']));
            $this->validatePntIds($pntIds);
            $record['pnt_ids'] = $pntIds;
        }

        if ($record['nombre'] === '') {
            throw new InvalidArgumentException('El nombre del tiempo es obligatorio.');
        }

        $this->state['tiempos'][$identifier] = $record;
        $this->persist();

        return $record;
    }

    public function deleteTiempo(string $identifier): void
    {
        if (!isset($this->state['tiempos'][$identifier])) {
            throw new InvalidArgumentException('Tiempo inexistente.');
        }

        unset($this->state['tiempos'][$identifier]);

        foreach ($this->state['carpetas'] as &$carpeta) {
            $carpeta['tiempo_ids'] = array_values(array_filter(
                $carpeta['tiempo_ids'],
                fn ($id) => $id !== $identifier
            ));
        }

        $this->persist();
    }

    public function addPntToTiempo(string $tiempoId, string $pntId): array
    {
        $tiempo = $this->state['tiempos'][$tiempoId] ?? null;
        if ($tiempo === null) {
            throw new InvalidArgumentException('Tiempo inexistente.');
        }

        if (!isset($this->state['pnts'][$pntId])) {
            throw new InvalidArgumentException('PNT inexistente.');
        }

        if (!in_array($pntId, $tiempo['pnt_ids'], true)) {
            $tiempo['pnt_ids'][] = $pntId;
        }

        $this->state['tiempos'][$tiempoId] = $tiempo;
        $this->persist();

        return $tiempo;
    }

    public function removePntFromTiempo(string $tiempoId, string $pntId): array
    {
        $tiempo = $this->state['tiempos'][$tiempoId] ?? null;
        if ($tiempo === null) {
            throw new InvalidArgumentException('Tiempo inexistente.');
        }

        $tiempo['pnt_ids'] = array_values(array_filter(
            $tiempo['pnt_ids'],
            fn ($id) => $id !== $pntId
        ));

        $this->state['tiempos'][$tiempoId] = $tiempo;
        $this->persist();

        return $tiempo;
    }

    public function listCarpetas(): array
    {
        return array_values($this->state['carpetas']);
    }

    public function getCarpeta(string $identifier): ?array
    {
        return $this->state['carpetas'][$identifier] ?? null;
    }

    public function createCarpeta(array $payload): array
    {
        $identifier = $this->ensureIdentifier('carpeta_', $payload['identifier'] ?? null);
        if (isset($this->state['carpetas'][$identifier])) {
            throw new InvalidArgumentException('Ya existe una carpeta con ese identificador.');
        }

        $fecha = $this->parseDate($payload['fecha_emision'] ?? null);

        $record = [
            'identifier' => $identifier,
            'nombre' => trim($payload['nombre'] ?? ''),
            'fecha_emision' => $fecha->format('Y-m-d'),
            'tiempo_ids' => array_values(array_unique($payload['tiempo_ids'] ?? [])),
            'observaciones' => trim($payload['observaciones'] ?? ''),
        ];

        if ($record['nombre'] === '') {
            throw new InvalidArgumentException('El nombre de la carpeta es obligatorio.');
        }

        $this->validateTiempoIds($record['tiempo_ids']);

        $this->state['carpetas'][$identifier] = $record;
        $this->persist();

        return $record;
    }

    public function updateCarpeta(string $identifier, array $payload): array
    {
        if (!isset($this->state['carpetas'][$identifier])) {
            throw new InvalidArgumentException('Carpeta inexistente.');
        }

        $record = $this->state['carpetas'][$identifier];
        $record['nombre'] = trim($payload['nombre'] ?? $record['nombre']);
        if (isset($payload['fecha_emision'])) {
            $fecha = $this->parseDate($payload['fecha_emision']);
            $record['fecha_emision'] = $fecha->format('Y-m-d');
        }

        if (isset($payload['tiempo_ids'])) {
            $tiempoIds = array_values(array_unique($payload['tiempo_ids']));
            $this->validateTiempoIds($tiempoIds);
            $record['tiempo_ids'] = $tiempoIds;
        }

        $record['observaciones'] = trim($payload['observaciones'] ?? $record['observaciones']);

        if ($record['nombre'] === '') {
            throw new InvalidArgumentException('El nombre de la carpeta es obligatorio.');
        }

        $this->state['carpetas'][$identifier] = $record;
        $this->persist();

        return $record;
    }

    public function deleteCarpeta(string $identifier): void
    {
        if (!isset($this->state['carpetas'][$identifier])) {
            throw new InvalidArgumentException('Carpeta inexistente.');
        }

        unset($this->state['carpetas'][$identifier]);
        $this->persist();
    }

    public function addTiempoToCarpeta(string $carpetaId, string $tiempoId): array
    {
        $carpeta = $this->state['carpetas'][$carpetaId] ?? null;
        if ($carpeta === null) {
            throw new InvalidArgumentException('Carpeta inexistente.');
        }

        if (!isset($this->state['tiempos'][$tiempoId])) {
            throw new InvalidArgumentException('Tiempo inexistente.');
        }

        if (!in_array($tiempoId, $carpeta['tiempo_ids'], true)) {
            $carpeta['tiempo_ids'][] = $tiempoId;
        }

        $this->state['carpetas'][$carpetaId] = $carpeta;
        $this->persist();

        return $carpeta;
    }

    public function removeTiempoFromCarpeta(string $carpetaId, string $tiempoId): array
    {
        $carpeta = $this->state['carpetas'][$carpetaId] ?? null;
        if ($carpeta === null) {
            throw new InvalidArgumentException('Carpeta inexistente.');
        }

        $carpeta['tiempo_ids'] = array_values(array_filter(
            $carpeta['tiempo_ids'],
            fn ($id) => $id !== $tiempoId
        ));

        $this->state['carpetas'][$carpetaId] = $carpeta;
        $this->persist();

        return $carpeta;
    }

    public function resumenGeneral(): array
    {
        $result = [];
        foreach ($this->state['carpetas'] as $carpeta) {
            $totalDuracion = 0;
            $tiempos = [];
            foreach ($carpeta['tiempo_ids'] as $tiempoId) {
                $tiempo = $this->state['tiempos'][$tiempoId] ?? null;
                if ($tiempo === null) {
                    continue;
                }
                $duracionTiempo = $tiempo['duracion_total'];
                $pnts = [];
                foreach ($tiempo['pnt_ids'] as $pntId) {
                    $pnt = $this->state['pnts'][$pntId] ?? null;
                    if ($pnt === null) {
                        continue;
                    }
                    $duracionTiempo += $pnt['duracion_segundos'];
                    $pnts[] = $pnt;
                }
                $totalDuracion += $duracionTiempo;
                $tiempos[] = [
                    'tiempo' => $tiempo,
                    'pnts' => $pnts,
                    'duracion_total_calculada' => $duracionTiempo,
                ];
            }

            $result[] = [
                'carpeta' => $carpeta,
                'tiempos' => $tiempos,
                'duracion_total_calculada' => $totalDuracion,
            ];
        }

        return $result;
    }

    public function agendaPorFecha(string $fecha): array
    {
        $date = $this->parseDate($fecha);
        $target = $date->format('Y-m-d');

        $agenda = array_values(array_filter(
            $this->state['carpetas'],
            fn ($carpeta) => $carpeta['fecha_emision'] === $target
        ));

        $detalle = [];
        foreach ($agenda as $carpeta) {
            $tiempos = array_values(array_filter(array_map(
                fn ($tiempoId) => $this->state['tiempos'][$tiempoId] ?? null,
                $carpeta['tiempo_ids']
            )));
            $detalle[] = [
                'carpeta' => $carpeta,
                'tiempos' => $tiempos,
            ];
        }

        return $detalle;
    }

    private function parseDate(?string $value): DateTimeImmutable
    {
        if ($value === null || trim($value) === '') {
            throw new InvalidArgumentException('La fecha de emisión es obligatoria.');
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', substr(trim($value), 0, 10));
        if (!$date) {
            throw new InvalidArgumentException('Formato de fecha inválido, utilice YYYY-MM-DD.');
        }

        return $date;
    }

    private function validatePntIds(array $ids): void
    {
        foreach ($ids as $id) {
            if (!isset($this->state['pnts'][$id])) {
                throw new InvalidArgumentException("El PNT {$id} no existe.");
            }
        }
    }

    private function validateTiempoIds(array $ids): void
    {
        foreach ($ids as $id) {
            if (!isset($this->state['tiempos'][$id])) {
                throw new InvalidArgumentException("El tiempo {$id} no existe.");
            }
        }
    }
}
