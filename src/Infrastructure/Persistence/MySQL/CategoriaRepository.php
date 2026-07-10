<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Persistence\MySQL;

use Elyra\Domain\Entity\Categoria;
use Elyra\Domain\Repository\CategoriaRepositoryInterface;

class CategoriaRepository implements CategoriaRepositoryInterface
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::get();
    }

    public function findById(int $id): ?Categoria
    {
        $stmt = $this->pdo->prepare("SELECT * FROM categoria WHERE id = ?");
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
        $stmt = $this->pdo->query("SELECT * FROM categoria ORDER BY nombre");
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $stmt->fetchAll();

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    public function findByTipo(string $tipo): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM categoria WHERE tipo = ? ORDER BY nombre");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$tipo]);
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $stmt->fetchAll();

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    public function save(Categoria $categoria): Categoria
    {
        $stmt = $this->pdo->prepare("INSERT INTO categoria (nombre, descripcion, tipo) VALUES (?, ?, ?)");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$categoria->getNombre(), $categoria->getDescripcion(), $categoria->getTipo()]);
        $categoria->setId((int) $this->pdo->lastInsertId());
        return $categoria;
    }

    public function update(Categoria $categoria): void
    {
        $stmt = $this->pdo->prepare("UPDATE categoria SET nombre = ?, descripcion = ?, tipo = ? WHERE id = ?");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$categoria->getNombre(), $categoria->getDescripcion(), $categoria->getTipo(), $categoria->getId()]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM categoria WHERE id = ?");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$id]);
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): Categoria
    {
        /** @var int $id */
        $id = $row['id'];
        /** @var string $nombre */
        $nombre = $row['nombre'];
        /** @var string|null $descripcion */
        $descripcion = $row['descripcion'];
        /** @var string $tipo */
        $tipo = $row['tipo'];

        return new Categoria(
            id: $id,
            nombre: $nombre,
            descripcion: $descripcion,
            tipo: $tipo
        );
    }
}
