<?php

declare(strict_types=1);

namespace Elyra\Domain\ValueObject;

final class Coordenada
{
    private float $latitud;
    private float $longitud;

    public function __construct(float $latitud, float $longitud)
    {
        if ($latitud < -90 || $latitud > 90) {
            throw new \InvalidArgumentException("Latitud fuera de rango: {$latitud}");
        }
        if ($longitud < -180 || $longitud > 180) {
            throw new \InvalidArgumentException("Longitud fuera de rango: {$longitud}");
        }
        $this->latitud = $latitud;
        $this->longitud = $longitud;
    }

    public function latitud(): float
    {
        return $this->latitud;
    }

    public function longitud(): float
    {
        return $this->longitud;
    }

    /**
     * @return array{lat: float, lng: float}
     */
    public function toArray(): array
    {
        return ['lat' => $this->latitud, 'lng' => $this->longitud];
    }

    public function distanceTo(Coordenada $other): float
    {
        $earthRadius = 6371.0;
        $dLat = deg2rad($other->latitud - $this->latitud);
        $dLng = deg2rad($other->longitud - $this->longitud);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($this->latitud)) * cos(deg2rad($other->latitud))
            * sin($dLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function equals(Coordenada $other): bool
    {
        return abs($this->latitud - $other->latitud) < 0.0000001
            && abs($this->longitud - $other->longitud) < 0.0000001;
    }

    public function __toString(): string
    {
        return "{$this->latitud},{$this->longitud}";
    }
}
