<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Persistence\MySQL;

use Elyra\Domain\Entity\Vehiculo;
use Elyra\Domain\Repository\VehiculoRepositoryInterface;

class VehiculoRepository implements VehiculoRepositoryInterface
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::get();
    }

    public function findById(int $id): ?Vehiculo
    {
        $stmt = $this->pdo->prepare("SELECT * FROM vehiculo WHERE id = ?");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$id]);
        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch();

        if (!$row) return null;

        return $this->hydrate($row);
    }

    public function findAll(): array
    {
        /** @var \PDOStatement $stmt */
        $stmt = $this->pdo->query("SELECT * FROM vehiculo ORDER BY patente");
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $stmt->fetchAll();

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    public function findByPatente(string $patente): ?Vehiculo
    {
        $stmt = $this->pdo->prepare("SELECT * FROM vehiculo WHERE patente = ?");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$patente]);
        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch();

        if (!$row) return null;

        return $this->hydrate($row);
    }

    public function countTotal(): int
    {
        /** @var \PDOStatement $stmt */
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM vehiculo");
        return (int) $stmt->fetchColumn();
    }

    public function save(Vehiculo $vehiculo): Vehiculo
    {
        $stmt = $this->pdo->prepare("INSERT INTO vehiculo (patente, modelo, anio) VALUES (?, ?, ?)");
        /** @var \PDOStatement $stmt */
        $stmt->execute([
            $vehiculo->getPatente(),
            $vehiculo->getModelo(),
            $vehiculo->getAnio(),
        ]);
        $vehiculo->setId((int) $this->pdo->lastInsertId());
        return $vehiculo;
    }

    public function update(Vehiculo $vehiculo): void
    {
        $stmt = $this->pdo->prepare("UPDATE vehiculo SET patente = ?, modelo = ?, anio = ? WHERE id = ?");
        /** @var \PDOStatement $stmt */
        $stmt->execute([
            $vehiculo->getPatente(),
            $vehiculo->getModelo(),
            $vehiculo->getAnio(),
            $vehiculo->getId(),
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM vehiculo WHERE id = ?");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$id]);
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): Vehiculo
    {
        /** @var int $id */
        $id = $row['id'];
        /** @var string $patente */
        $patente = $row['patente'];
        /** @var string|null $modelo */
        $modelo = $row['modelo'];
        /** @var string|null $anio */
        $anio = $row['anio'];
        /** @var string|null $createdAt */
        $createdAt = $row['created_at'];

        return new Vehiculo(
            id: $id,
            patente: $patente,
            modelo: $modelo,
            anio: $anio,
            createdAt: $createdAt,
        );
    }
}
