<?php

declare(strict_types=1);

namespace Elyra\Domain\Entity;

class Encuesta
{
    private ?int $id;
    private string $titulo;
    private ?string $descripcion;
    private bool $activa;
    private int $creadaPor;
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct(
        ?int $id,
        string $titulo,
        int $creadaPor,
        ?string $descripcion = null,
        bool $activa = true,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->titulo = $titulo;
        $this->creadaPor = $creadaPor;
        $this->descripcion = $descripcion;
        $this->activa = $activa;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): ?int { return $this->id; }
    public function getTitulo(): string { return $this->titulo; }
    public function getDescripcion(): ?string { return $this->descripcion; }
    public function isActiva(): bool { return $this->activa; }
    public function getCreadaPor(): int { return $this->creadaPor; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }

    public function setId(int $id): void { $this->id = $id; }
    public function setActiva(bool $activa): void { $this->activa = $activa; }
}
