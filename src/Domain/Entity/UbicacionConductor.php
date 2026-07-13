<?php

declare(strict_types=1);

namespace Elyra\Domain\Entity;

use Elyra\Domain\ValueObject\Coordenada;

final class UbicacionConductor
{
    public function __construct(
        private ?int $id,
        private int $conductorId,
        private ?int $trasladoId,
        private Coordenada $coordenada,
        private ?int $heading,
        private ?float $velocidad,
        private ?string $updatedAt,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConductorId(): int
    {
        return $this->conductorId;
    }

    public function getTrasladoId(): ?int
    {
        return $this->trasladoId;
    }

    public function getCoordenada(): Coordenada
    {
        return $this->coordenada;
    }

    public function getHeading(): ?int
    {
        return $this->heading;
    }

    public function getVelocidad(): ?float
    {
        return $this->velocidad;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    /**
     * @return array{id: int|null, conductor_id: int, traslado_id: int|null, latitud: float, longitud: float, heading: int|null, velocidad: float|null, updated_at: string|null}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'conductor_id' => $this->conductorId,
            'traslado_id' => $this->trasladoId,
            'latitud' => $this->coordenada->latitud(),
            'longitud' => $this->coordenada->longitud(),
            'heading' => $this->heading,
            'velocidad' => $this->velocidad,
            'updated_at' => $this->updatedAt,
        ];
    }
}
