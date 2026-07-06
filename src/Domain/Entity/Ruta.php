<?php

declare(strict_types=1);

namespace Elyra\Domain\Entity;

class Ruta
{
    private ?int $id;
    private string $nombre;
    private string $origen;
    private string $destino;
    private ?float $distanciaKm;
    private ?string $descripcion;
    private ?string $createdAt;

    public function __construct(
        ?int $id,
        string $nombre,
        string $origen,
        string $destino,
        ?float $distanciaKm = null,
        ?string $descripcion = null,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->origen = $origen;
        $this->destino = $destino;
        $this->distanciaKm = $distanciaKm;
        $this->descripcion = $descripcion;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getOrigen(): string { return $this->origen; }
    public function getDestino(): string { return $this->destino; }
    public function getDistanciaKm(): ?float { return $this->distanciaKm; }
    public function getDescripcion(): ?string { return $this->descripcion; }
    public function getCreatedAt(): ?string { return $this->createdAt; }

    public function setId(int $id): void { $this->id = $id; }
}
