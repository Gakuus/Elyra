<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Traslado;

use Elyra\Domain\Entity\ElementoTraslado;
use Elyra\Domain\Entity\HistorialEstado;
use Elyra\Domain\Entity\Traslado;
use Elyra\Domain\Repository\TrasladoRepositoryInterface;
use Elyra\Domain\ValueObject\TipoElemento;

final class RegistrarTrasladoUseCase
{
    public function __construct(
        private TrasladoRepositoryInterface $trasladoRepo,
    ) {
    }

    /**
     * @param array{
     *     conductorId: int,
     *     origen: string,
     *     destino: string,
     *     registradoPor: int,
     *     copilotoId?: int,
     *     vehiculoId?: int,
     *     rutaId?: int,
     *     horaSalidaEstimada?: string,
     *     observaciones?: string,
     *     elementos?: list<array{tipo: string, descripcion?: string, cantidad?: int, pacienteId?: int}>,
     * } $input
     *
     * @return array{success: bool, trasladoId: int, codigo: string}
     */
    public function execute(array $input): array
    {
        if ($input['conductorId'] <= 0) {
            throw new \InvalidArgumentException('Conductor inválido.');
        }

        $origen = trim($input['origen']);
        $destino = trim($input['destino']);
        if ($origen === '' || $destino === '') {
            throw new \InvalidArgumentException('Origen y destino son requeridos.');
        }
        if ($origen === $destino) {
            throw new \InvalidArgumentException('Origen y destino deben ser distintos.');
        }

        $codigo = $this->trasladoRepo->nextCodigo();

        $traslado = new Traslado(
            id: null,
            codigo: $codigo,
            conductorId: $input['conductorId'],
            origen: $origen,
            destino: $destino,
            registradoPor: $input['registradoPor'],
            copilotoId: $input['copilotoId'] ?? null,
            vehiculoId: $input['vehiculoId'] ?? null,
            rutaId: $input['rutaId'] ?? null,
            horaSalidaEstimada: $input['horaSalidaEstimada'] ?? null,
            observaciones: $input['observaciones'] ?? null,
        );

        $saved = $this->trasladoRepo->save($traslado);
        $trasladoId = $saved->getId();

        if ($trasladoId !== null) {
            $historial = new HistorialEstado(
                id: null,
                trasladoId: $trasladoId,
                estadoNuevo: 'pendiente',
                actualizadoPor: $input['registradoPor'],
                estadoAnterior: null,
                observacion: 'Traslado registrado',
            );
            $this->trasladoRepo->saveHistorial($historial);

            foreach ($input['elementos'] ?? [] as $el) {
                $elemento = new ElementoTraslado(
                    id: null,
                    trasladoId: $trasladoId,
                    tipo: new TipoElemento($el['tipo']),
                    cantidad: $el['cantidad'] ?? 1,
                    pacienteId: $el['pacienteId'] ?? null,
                    descripcion: $el['descripcion'] ?? null,
                );
                $this->trasladoRepo->saveElemento($elemento);
            }
        }

        return ['success' => true, 'trasladoId' => $trasladoId ?? 0, 'codigo' => $codigo];
    }
}
