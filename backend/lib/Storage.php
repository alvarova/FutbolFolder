<?php

class Storage
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
        $dir = dirname($this->path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        if (!file_exists($this->path)) {
            $this->save([
                'pnts' => [],
                'tiempos' => [],
                'carpetas' => [],
            ]);
        }
    }

    public function load(): array
    {
        $raw = file_get_contents($this->path);
        if ($raw === false || $raw === '') {
            return [
                'pnts' => [],
                'tiempos' => [],
                'carpetas' => [],
            ];
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            throw new RuntimeException('No se pudo decodificar el archivo de datos.');
        }

        return array_merge(
            ['pnts' => [], 'tiempos' => [], 'carpetas' => []],
            $data
        );
    }

    public function save(array $data): void
    {
        $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($encoded === false) {
            throw new RuntimeException('No se pudo serializar el estado.');
        }

        $result = file_put_contents($this->path, $encoded . PHP_EOL);
        if ($result === false) {
            throw new RuntimeException('No se pudo escribir el archivo de datos.');
        }
    }
}
