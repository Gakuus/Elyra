<?php

declare(strict_types=1);

namespace Elyra\Domain\Entity;

class HistorialEstado
{
    private ?int $id;
    private int $trasladoId;
    private ?string $estadoAnterior;
    private string $estadoNuevo;
    private ?string $observacion;
    private int $actualizadoPor;
    private ?string $createdAt;

    public function __construct(
        ?int $id,
        int $trasladoId,
        string $estadoNuevo,
        int $actualizadoPor,
        ?string $estadoAnterior = null,
        ?string $observacion = null,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->trasladoId = $trasladoId;
        $this->estadoNuevo = $estadoNuevo;
        $this->actualizadoPor = $actualizadoPor;
        $this->estadoAnterior = $estadoAnterior;
        $this->observacion = $observacion;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int { return $this->id; }
    public function getTrasladoId(): int { return $this->trasladoId; }
    public function getEstadoAnterior(): ?string { return $this->estadoAnterior; }
    public function getEstadoNuevo(): string { return $this->estadoNuevo; }
    public function getObservacion(): ?string { return $this->observacion; }
    public function getActualizadoPor(): int { return $this->actualizadoPor; }
    public function getCreatedAt(): ?string { return $this->createdAt; }

    public function setId(int $id): void { $this->id = $id; }
}
