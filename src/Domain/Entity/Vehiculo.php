<?php

declare(strict_types=1);

namespace Elyra\Domain\Entity;

class Vehiculo
{
    private ?int $id;
    private string $patente;
    private ?string $modelo;
    private ?string $anio;
    private ?string $createdAt;

    public function __construct(
        ?int $id,
        string $patente,
        ?string $modelo = null,
        ?string $anio = null,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->patente = $patente;
        $this->modelo = $modelo;
        $this->anio = $anio;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int { return $this->id; }
    public function getPatente(): string { return $this->patente; }
    public function getModelo(): ?string { return $this->modelo; }
    public function getAnio(): ?string { return $this->anio; }
    public function getCreatedAt(): ?string { return $this->createdAt; }

    public function setId(int $id): void { $this->id = $id; }
}
