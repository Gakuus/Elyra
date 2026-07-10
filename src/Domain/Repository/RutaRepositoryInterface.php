<?php

declare(strict_types=1);

namespace Elyra\Domain\Repository;

use Elyra\Domain\Entity\Ruta;

interface RutaRepositoryInterface
{
    public function findById(int $id): ?Ruta;
    public function findAll(): array;
    public function countTotal(): int;
    public function save(Ruta $ruta): Ruta;
    public function update(Ruta $ruta): void;
    public function delete(int $id): void;
}
