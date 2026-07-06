<?php

declare(strict_types=1);

namespace Elyra\Domain\Repository;

use Elyra\Domain\Entity\Documento;

interface DocumentoRepositoryInterface
{
    public function findById(int $id): ?Documento;
    public function findByCodigoQr(int $codigoQrId): ?Documento;
    public function findByEncuesta(int $encuestaId): ?Documento;
    public function findAll(?int $categoriaId = null, ?string $busqueda = null, int $page = 1, int $perPage = 20): array;
    public function count(?int $categoriaId = null, ?string $busqueda = null): int;
    public function countTotal(): int;
    public function countActivos(): int;
    public function save(Documento $documento): Documento;
    public function update(Documento $documento): void;
    public function delete(int $id): void;
}
