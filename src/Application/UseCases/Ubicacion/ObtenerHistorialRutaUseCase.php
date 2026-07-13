<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Ubicacion;

use Elyra\Domain\Repository\UbicacionConductorRepositoryInterface;

final class ObtenerHistorialRutaUseCase
{
    public function __construct(
        private UbicacionConductorRepositoryInterface $ubicacionRepo,
    ) {
    }

    /**
     * @param array{conductor_id: int, traslado_id?: int|null, desde?: string|null, hasta?: string|null} $input
     * @return list<array{latitud: float, longitud: float, created_at: string}>
     */
    public function execute(array $input): array
    {
        $conductorId = $input['conductor_id'];
        $trasladoId = $input['traslado_id'] ?? null;
        $desde = $input['desde'] ?? date('Y-m-d 00:00:00');
        $hasta = $input['hasta'] ?? date('Y-m-d 23:59:59');

        return $this->ubicacionRepo->findHistorial($conductorId, $trasladoId, $desde, $hasta);
    }
}
