<?php

declare(strict_types=1);

namespace Elyra\Domain\Entity;

use Elyra\Domain\ValueObject\TipoElemento;

class ElementoTraslado
{
    private ?int $id;
    private int $trasladoId;
    private TipoElemento $tipo;
    private ?int $pacienteId;
    private ?string $descripcion;
    private int $cantidad;
    private ?string $createdAt;

    public function __construct(
        ?int $id,
        int $trasladoId,
        TipoElemento $tipo,
        int $cantidad = 1,
        ?int $pacienteId = null,
        ?string $descripcion = null,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->trasladoId = $trasladoId;
        $this->tipo = $tipo;
        $this->cantidad = $cantidad;
        $this->pacienteId = $pacienteId;
        $this->descripcion = $descripcion;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int { return $this->id; }
    public function getTrasladoId(): int { return $this->trasladoId; }
    public function getTipo(): TipoElemento { return $this->tipo; }
    public function getPacienteId(): ?int { return $this->pacienteId; }
    public function getDescripcion(): ?string { return $this->descripcion; }
    public function getCantidad(): int { return $this->cantidad; }
    public function getCreatedAt(): ?string { return $this->createdAt; }

    public function setId(int $id): void { $this->id = $id; }
}
