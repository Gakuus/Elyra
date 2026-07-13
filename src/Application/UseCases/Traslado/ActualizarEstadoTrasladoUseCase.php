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
     * @param array{id: int, nuevoEstado: string, actualizadoPor: int, motivo?: string, observacion?: string, actualizadoPorRol?: string} $input
     */
    public function execute(array $input): void
    {
        $traslado = $this->trasladoRepo->findById($input['id']);
        if ($traslado === null) {
            throw new \DomainException('Traslado no encontrado.');
        }

        $rol = $input['actualizadoPorRol'] ?? '';
        $userId = $input['actualizadoPor'];
        if ($rol !== 'admin' && $rol !== 'superadmin' && $traslado->getConductorId() !== $userId) {
            throw new \DomainException('No tenés permiso para modificar este traslado.');
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

        if ($nuevoEstado->value() === 'en_curso') {
            $activos = $this->trasladoRepo->findAllByEstados(['en_curso', 'en_destino', 'en_retorno']);
            foreach ($activos as $a) {
                if ($a->getConductorId() === $traslado->getConductorId() && $a->getId() !== $traslado->getId()) {
                    throw new \DomainException('El conductor ya tiene un traslado activo.');
                }
            }
        }

        $traslado->actualizarEstado($nuevoEstado, $input['motivo'] ?? null);

        $now = date('Y-m-d H:i:s');
        $estadoVal = $nuevoEstado->value();
        if ($estadoVal === 'en_curso') {
            $traslado->setHoraSalidaEfectiva($now);
        } elseif ($estadoVal === 'en_destino') {
            $traslado->setHoraLlegadaDestino($now);
        } elseif ($estadoVal === 'en_retorno') {
            $traslado->setHoraInicioRetorno($now);
        } elseif ($estadoVal === 'completado') {
            $traslado->setHoraLlegadaHospital($now);
        }

        try {
            $this->trasladoRepo->beginTransaction();

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

            $this->trasladoRepo->commit();
        } catch (\Exception $e) {
            $this->trasladoRepo->rollback();
            throw $e;
        }
    }
}
