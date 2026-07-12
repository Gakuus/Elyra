<?php

declare(strict_types=1);

namespace Elyra\Domain\Repository;

use Elyra\Domain\Entity\UbicacionConductor;

interface UbicacionConductorRepositoryInterface
{
    public function upsert(UbicacionConductor $ubicacion): void;

    /**
     * @return list<array{ubicacion: UbicacionConductor, conductor_nombre: string, traslado_codigo: string|null, traslado_estado: string|null, traslado_origen: string|null, traslado_destino: string|null}>
     */
    public function findLatest(): array;

    public function findByConductorId(int $conductorId): ?UbicacionConductor;

    /**
     * @return list<array{latitud: float, longitud: float, created_at: string}>
     */
    public function findHistorial(int $conductorId, ?int $trasladoId, string $desde, string $hasta): array;
}
