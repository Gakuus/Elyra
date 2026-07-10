<?php

declare(strict_types=1);

namespace Elyra\Domain\Repository;

use Elyra\Domain\Entity\Categoria;

interface CategoriaRepositoryInterface
{
    public function findById(int $id): ?Categoria;
    /** @return Categoria[] */
    public function findAll(): array;
    /** @return Categoria[] */
    public function findByTipo(string $tipo): array;
    public function save(Categoria $categoria): Categoria;
    public function update(Categoria $categoria): void;
    public function delete(int $id): void;
}
