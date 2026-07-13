<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Ubicacion;

use Elyra\Domain\Repository\UbicacionConductorRepositoryInterface;

final class ObtenerUbicacionesActivasUseCase
{
    public function __construct(
        private UbicacionConductorRepositoryInterface $ubicacionRepo,
    ) {
    }

    /**
     * @return list<array{ubicacion: \Elyra\Domain\Entity\UbicacionConductor, conductor_nombre: string, traslado_codigo: string|null, traslado_estado: string|null, traslado_origen: string|null, traslado_destino: string|null}>
     */
    public function execute(): array
    {
        return array_values($this->ubicacionRepo->findLatest());
    }
}
