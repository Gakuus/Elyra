<?php

declare(strict_types=1);

namespace Elyra\Domain\Entity;

class CatalogoElemento
{
    private ?int $id;
    private string $tipo;
    private string $nombre;
    private ?string $descripcion;
    private bool $activo;
    private ?string $createdAt;

    public function __construct(
        ?int $id,
        string $tipo,
        string $nombre,
        ?string $descripcion = null,
        bool $activo = true,
        ?string $createdAt = null,
    ) {
        $this->id = $id;
        $this->tipo = $tipo;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->activo = $activo;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int { return $this->id; }
    public function getTipo(): string { return $this->tipo; }
    public function getNombre(): string { return $this->nombre; }
    public function getDescripcion(): ?string { return $this->descripcion; }
    public function isActivo(): bool { return $this->activo; }
    public function getCreatedAt(): ?string { return $this->createdAt; }

    public function setId(int $id): void { $this->id = $id; }
    public function setNombre(string $nombre): void { $this->nombre = $nombre; }
    public function setDescripcion(?string $descripcion): void { $this->descripcion = $descripcion; }
    public function setActivo(bool $activo): void { $this->activo = $activo; }
}
