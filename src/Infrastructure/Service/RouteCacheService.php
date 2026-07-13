<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Service;

final class RouteCacheService
{
    private const OSRM_URL = 'https://router.project-osrm.org/route/v1/driving/';
    private const CACHE_DIR = __DIR__ . '/../../../storage/route-cache';
    private const CACHE_TTL = 86400 * 30;

    /**
     * @return array{coordinates: list<array{float, float}>, distance_km: float, duration_min: float}|null
     */
    public function getRoute(float $origenLat, float $origenLng, float $destinoLat, float $destinoLng): ?array
    {
        $key = $this->cacheKey($origenLat, $origenLng, $destinoLat, $destinoLng);
        $cached = $this->readCache($key);
        if ($cached !== null) {
            return $cached;
        }

        $route = $this->fetchFromOsrm($origenLat, $origenLng, $destinoLat, $destinoLng);
        if ($route !== null) {
            $this->writeCache($key, $route);
        }

        return $route;
    }

    /**
     * @return array{coordinates: list<array{float, float}>, distance_km: float, duration_min: float}|null
     */
    private function fetchFromOsrm(float $origenLat, float $origenLng, float $destinoLat, float $destinoLng): ?array
    {
        $url = self::OSRM_URL
            . rawurlencode($origenLng . ',' . $origenLat)
            . ';'
            . rawurlencode($destinoLng . ',' . $destinoLat)
            . '?overview=full&geometries=geojson&steps=false';

        $ctx = stream_context_create([
            'http' => [
                'timeout' => 10,
                'method' => 'GET',
                'header' => "User-Agent: Elyra-Hospital/1.0\r\n",
            ],
        ]);

        $response = @file_get_contents($url, false, $ctx);
        if ($response === false) {
            return null;
        }

        $data = json_decode($response, true);
        if (!is_array($data) || ($data['code'] ?? '') !== 'Ok') {
            return null;
        }

        $routes = $data['routes'] ?? [];
        if (!is_array($routes) || count($routes) === 0) {
            return null;
        }

        $route = $routes[0] ?? null;
        if (!is_array($route)) {
            return null;
        }

        $geometry = $route['geometry'] ?? null;
        if (!is_array($geometry)) {
            return null;
        }

        $coords = $geometry['coordinates'] ?? [];
        if (!is_array($coords)) {
            return null;
        }

        $coordinates = [];
        foreach ($coords as $coord) {
            if (is_array($coord) && count($coord) >= 2) {
                $val0 = $coord[0];
                $val1 = $coord[1];
                $lat = (is_int($val1) || is_float($val1)) ? (float) $val1 : 0.0;
                $lng = (is_int($val0) || is_float($val0)) ? (float) $val0 : 0.0;
                $coordinates[] = [$lat, $lng];
            }
        }

        $distanceRaw = is_numeric($route['distance'] ?? null) ? (float) $route['distance'] : 0.0;
        $durationRaw = is_numeric($route['duration'] ?? null) ? (float) $route['duration'] : 0.0;

        return [
            'coordinates' => $coordinates,
            'distance_km' => round($distanceRaw / 1000, 2),
            'duration_min' => round($durationRaw / 60, 1),
        ];
    }

    private function cacheKey(float $origenLat, float $origenLng, float $destinoLat, float $destinoLng): string
    {
        return sprintf('%.6f_%.6f_%.6f_%.6f', $origenLat, $origenLng, $destinoLat, $destinoLng);
    }

    /**
     * @return array{coordinates: list<array{float, float}>, distance_km: float, duration_min: float}|null
     */
    private function readCache(string $key): ?array
    {
        $file = self::CACHE_DIR . '/' . md5($key) . '.json';
        if (!is_file($file)) {
            return null;
        }

        $mtime = @filemtime($file);
        if ($mtime === false || (time() - $mtime) > self::CACHE_TTL) {
            @unlink($file);
            return null;
        }

        $content = @file_get_contents($file);
        if ($content === false) {
            return null;
        }

        $data = json_decode($content, true);
        if (!is_array($data)) {
            return null;
        }

        $coords = $data['coordinates'] ?? null;
        if (!is_array($coords)) {
            return null;
        }

        $distanceKm = is_numeric($data['distance_km'] ?? null) ? (float) $data['distance_km'] : 0.0;
        $durationMin = is_numeric($data['duration_min'] ?? null) ? (float) $data['duration_min'] : 0.0;

        /** @var list<array{float, float}> $castCoords */
        $castCoords = [];
        foreach ($coords as $c) {
            if (is_array($c) && count($c) >= 2) {
                $c0 = $c[0];
                $c1 = $c[1];
                if (is_int($c0) || is_float($c0)) {
                    $val0 = (float) $c0;
                } else {
                    $val0 = 0.0;
                }
                if (is_int($c1) || is_float($c1)) {
                    $val1 = (float) $c1;
                } else {
                    $val1 = 0.0;
                }
                $castCoords[] = [$val0, $val1];
            }
        }

        return [
            'coordinates' => $castCoords,
            'distance_km' => $distanceKm,
            'duration_min' => $durationMin,
        ];
    }

    /**
     * @param array{coordinates: list<array{float, float}>, distance_km: float, duration_min: float} $route
     */
    private function writeCache(string $key, array $route): void
    {
        if (!is_dir(self::CACHE_DIR)) {
            @mkdir(self::CACHE_DIR, 0750, true);
        }

        $file = self::CACHE_DIR . '/' . md5($key) . '.json';
        @file_put_contents($file, json_encode($route, JSON_THROW_ON_ERROR));
    }
}
