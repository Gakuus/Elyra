<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Traslado;

use Elyra\Domain\Entity\HistorialEstado;
use Elyra\Domain\Repository\TrasladoRepositoryInterface;
use Elyra\Domain\ValueObject\EstadoTraslado;

final class ActualizarEstadoTrasladoUseCase
{
    public function __construct(
        private TrasladoRepositoryInterface $trasladoRepo,
    ) {
    }

    /**
     * @param array{id: int, nuevoEstado: string, actualizadoPor: int, motivo?: string, observacion?: string} $input
     */
    public function execute(array $input): void
    {
        $traslado = $this->trasladoRepo->findById($input['id']);
        if ($traslado === null) {
            throw new \DomainException('Traslado no encontrado.');
        }

        $nuevoEstado = new EstadoTraslado($input['nuevoEstado']);
        $estadoActual = $traslado->getEstado();

        if (!$estadoActual->puedeTransicionarA($nuevoEstado)) {
            throw new \DomainException(
                "No se puede cambiar de {$estadoActual} a {$nuevoEstado}"
            );
        }

        if ($nuevoEstado->value() === 'cancelado' && empty($input['motivo'])) {
            throw new \DomainException('Motivo de cancelación requerido.');
        }

        $traslado->actualizarEstado($nuevoEstado, $input['motivo'] ?? null);
        $this->trasladoRepo->update($traslado);

        $historial = new HistorialEstado(
            id: null,
            trasladoId: $input['id'],
            estadoNuevo: $nuevoEstado->value(),
            actualizadoPor: $input['actualizadoPor'],
            estadoAnterior: $estadoActual->value(),
            observacion: $input['observacion'] ?? null,
        );
        $this->trasladoRepo->saveHistorial($historial);
    }
}
