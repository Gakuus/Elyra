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
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return new Categoria(
            id: (int) $row['id'],
            nombre: $row['nombre'],
            descripcion: $row['descripcion']
        );
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM categoria ORDER BY nombre");
        $rows = $stmt->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[] = new Categoria(
                id: (int) $row['id'],
                nombre: $row['nombre'],
                descripcion: $row['descripcion']
            );
        }
        return $result;
    }

    public function save(Categoria $categoria): Categoria
    {
        $stmt = $this->pdo->prepare("INSERT INTO categoria (nombre, descripcion) VALUES (?, ?)");
        $stmt->execute([$categoria->getNombre(), $categoria->getDescripcion()]);
        $categoria->setId((int) $this->pdo->lastInsertId());
        return $categoria;
    }

    public function update(Categoria $categoria): void
    {
        $stmt = $this->pdo->prepare("UPDATE categoria SET nombre = ?, descripcion = ? WHERE id = ?");
        $stmt->execute([$categoria->getNombre(), $categoria->getDescripcion(), $categoria->getId()]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM categoria WHERE id = ?");
        $stmt->execute([$id]);
    }
}
