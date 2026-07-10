<?php

declare(strict_types=1);

namespace Elyra\Domain\Repository;

use Elyra\Domain\Entity\ElementoTraslado;
use Elyra\Domain\Entity\HistorialEstado;
use Elyra\Domain\Entity\Traslado;

interface TrasladoRepositoryInterface
{
    public function findById(int $id): ?Traslado;
    public function findByCodigo(string $codigo): ?Traslado;
    /** @return ElementoTraslado[] */
    public function findElementosByTrasladoId(int $trasladoId): array;
    /** @return HistorialEstado[] */
    public function findHistorialByTrasladoId(int $trasladoId): array;
    /** @return Traslado[] */
    public function findAll(?string $estado = null, ?int $conductorId = null, ?string $fechaDesde = null, ?string $fechaHasta = null, int $page = 1, int $perPage = 20): array;
    public function count(?string $estado = null, ?int $conductorId = null): int;
    public function countTotal(): int;
    public function countByEstado(string $estado): int;
    public function save(Traslado $traslado): Traslado;
    public function saveElemento(ElementoTraslado $elemento): ElementoTraslado;
    public function saveHistorial(HistorialEstado $historial): HistorialEstado;
    public function update(Traslado $traslado): void;
    public function nextCodigo(): string;
    public function delete(int $id): void;
}
