<?php

declare(strict_types=1);

namespace Elyra\Domain\Repository;

use Elyra\Domain\Entity\CatalogoElemento;

interface CatalogoElementoRepositoryInterface
{
    public function findById(int $id): ?CatalogoElemento;
    /** @return CatalogoElemento[] */
    public function findByTipo(string $tipo, ?bool $activo = true): array;
    /** @return CatalogoElemento[] */
    public function findAll(?bool $activo = null): array;
    public function save(CatalogoElemento $item): CatalogoElemento;
    public function update(CatalogoElemento $item): void;
    public function delete(int $id): void;
}
