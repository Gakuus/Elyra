<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Persistence\MySQL;

use Elyra\Domain\Entity\Ruta;
use Elyra\Domain\Repository\RutaRepositoryInterface;

class RutaRepository implements RutaRepositoryInterface
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::get();
    }

    public function findById(int $id): ?Ruta
    {
        $stmt = $this->pdo->prepare("SELECT * FROM ruta WHERE id = ?");
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
        $stmt = $this->pdo->query("SELECT * FROM ruta ORDER BY nombre");
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $stmt->fetchAll();

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    public function countTotal(): int
    {
        /** @var \PDOStatement $stmt */
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM ruta");
        return (int) $stmt->fetchColumn();
    }

    public function save(Ruta $ruta): Ruta
    {
        $stmt = $this->pdo->prepare("INSERT INTO ruta (nombre, origen, destino, distancia_km, descripcion) VALUES (?, ?, ?, ?, ?)");
        /** @var \PDOStatement $stmt */
        $stmt->execute([
            $ruta->getNombre(),
            $ruta->getOrigen(),
            $ruta->getDestino(),
            $ruta->getDistanciaKm(),
            $ruta->getDescripcion(),
        ]);
        $ruta->setId((int) $this->pdo->lastInsertId());
        return $ruta;
    }

    public function update(Ruta $ruta): void
    {
        $stmt = $this->pdo->prepare("UPDATE ruta SET nombre = ?, origen = ?, destino = ?, distancia_km = ?, descripcion = ? WHERE id = ?");
        /** @var \PDOStatement $stmt */
        $stmt->execute([
            $ruta->getNombre(),
            $ruta->getOrigen(),
            $ruta->getDestino(),
            $ruta->getDistanciaKm(),
            $ruta->getDescripcion(),
            $ruta->getId(),
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM ruta WHERE id = ?");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$id]);
    }

    private function hydrate(array $row): Ruta
    {
        return new Ruta(
            id: (int) $row['id'],
            nombre: $row['nombre'],
            origen: $row['origen'],
            destino: $row['destino'],
            distanciaKm: $row['distancia_km'] !== null ? (float) $row['distancia_km'] : null,
            descripcion: $row['descripcion'],
            createdAt: $row['created_at'],
        );
    }
}
