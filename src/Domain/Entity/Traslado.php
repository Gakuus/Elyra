<?php

declare(strict_types=1);

namespace Elyra\Domain\Entity;

use Elyra\Domain\ValueObject\EstadoTraslado;

class Traslado
{
    private ?int $id;
    private string $codigo;
    private int $conductorId;
    private ?int $copilotoId;
    private ?int $vehiculoId;
    private ?int $rutaId;
    private string $origen;
    private string $destino;
    private ?string $horaSalidaEstimada;
    private ?string $horaSalidaEfectiva;
    private ?string $horaLlegadaDestino;
    private ?string $horaInicioRetorno;
    private ?string $horaLlegadaHospital;
    private EstadoTraslado $estado;
    private ?string $motivoCancelacion;
    private int $registradoPor;
    private ?string $observaciones;
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct(
        ?int $id,
        string $codigo,
        int $conductorId,
        string $origen,
        string $destino,
        int $registradoPor,
        ?int $copilotoId = null,
        ?int $vehiculoId = null,
        ?int $rutaId = null,
        ?string $horaSalidaEstimada = null,
        ?string $horaSalidaEfectiva = null,
        ?string $horaLlegadaDestino = null,
        ?string $horaInicioRetorno = null,
        ?string $horaLlegadaHospital = null,
        ?string $estado = null,
        ?string $motivoCancelacion = null,
        ?string $observaciones = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->codigo = $codigo;
        $this->conductorId = $conductorId;
        $this->origen = $origen;
        $this->destino = $destino;
        $this->registradoPor = $registradoPor;
        $this->copilotoId = $copilotoId;
        $this->vehiculoId = $vehiculoId;
        $this->rutaId = $rutaId;
        $this->horaSalidaEstimada = $horaSalidaEstimada;
        $this->horaSalidaEfectiva = $horaSalidaEfectiva;
        $this->horaLlegadaDestino = $horaLlegadaDestino;
        $this->horaInicioRetorno = $horaInicioRetorno;
        $this->horaLlegadaHospital = $horaLlegadaHospital;
        $this->estado = new EstadoTraslado($estado ?? 'pendiente');
        $this->motivoCancelacion = $motivoCancelacion;
        $this->observaciones = $observaciones;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): ?int { return $this->id; }
    public function getCodigo(): string { return $this->codigo; }
    public function getConductorId(): int { return $this->conductorId; }
    public function getCopilotoId(): ?int { return $this->copilotoId; }
    public function getVehiculoId(): ?int { return $this->vehiculoId; }
    public function getRutaId(): ?int { return $this->rutaId; }
    public function getOrigen(): string { return $this->origen; }
    public function getDestino(): string { return $this->destino; }
    public function getHoraSalidaEstimada(): ?string { return $this->horaSalidaEstimada; }
    public function getHoraSalidaEfectiva(): ?string { return $this->horaSalidaEfectiva; }
    public function getHoraLlegadaDestino(): ?string { return $this->horaLlegadaDestino; }
    public function getHoraInicioRetorno(): ?string { return $this->horaInicioRetorno; }
    public function getHoraLlegadaHospital(): ?string { return $this->horaLlegadaHospital; }
    public function getEstado(): EstadoTraslado { return $this->estado; }
    public function getMotivoCancelacion(): ?string { return $this->motivoCancelacion; }
    public function getRegistradoPor(): int { return $this->registradoPor; }
    public function getObservaciones(): ?string { return $this->observaciones; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }

    public function setId(int $id): void { $this->id = $id; }

    public function actualizarEstado(EstadoTraslado $nuevoEstado, ?string $motivo = null): void
    {
        if (!$this->estado->puedeTransicionarA($nuevoEstado)) {
            throw new \DomainException(
                "No se puede cambiar de {$this->estado} a {$nuevoEstado}"
            );
        }
        if ($nuevoEstado->value() === 'cancelado' && empty($motivo)) {
            throw new \DomainException('Motivo de cancelación requerido');
        }
        $this->estado = $nuevoEstado;
        $this->motivoCancelacion = $motivo;
    }
}
