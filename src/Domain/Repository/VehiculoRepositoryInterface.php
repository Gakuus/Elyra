<?php

declare(strict_types=1);

namespace Elyra\Domain\Repository;

use Elyra\Domain\Entity\Vehiculo;

interface VehiculoRepositoryInterface
{
    public function findById(int $id): ?Vehiculo;
    public function findAll(): array;
    public function findByPatente(string $patente): ?Vehiculo;
    public function countTotal(): int;
    public function save(Vehiculo $vehiculo): Vehiculo;
    public function update(Vehiculo $vehiculo): void;
    public function delete(int $id): void;
}
