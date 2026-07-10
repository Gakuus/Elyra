<?php

declare(strict_types=1);

namespace Elyra\Domain\Repository;

use Elyra\Domain\Entity\Funcionario;

interface ConductorRepositoryInterface
{
    public function findById(int $id): ?Funcionario;
    /** @return Funcionario[] */
    public function findAll(?bool $activo = null): array;
    public function countTotal(): int;
    public function countActivos(): int;
    /** @return Funcionario[] */
    public function findDisponibles(): array;
    public function save(Funcionario $conductor): Funcionario;
    public function update(Funcionario $conductor): void;
    public function delete(int $id): void;
}
