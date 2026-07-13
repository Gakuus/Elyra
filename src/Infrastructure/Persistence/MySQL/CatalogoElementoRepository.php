<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Persistence\MySQL;

use Elyra\Domain\Entity\CatalogoElemento;
use Elyra\Domain\Repository\CatalogoElementoRepositoryInterface;

class CatalogoElementoRepository implements CatalogoElementoRepositoryInterface
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::get();
    }

    public function findById(int $id): ?CatalogoElemento
    {
        $stmt = $this->pdo->prepare("SELECT * FROM catalogo_elemento WHERE id = ?");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$id]);
        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch();

        if (!$row) return null;

        return $this->hydrate($row);
    }

    public function findByTipo(string $tipo, ?bool $activo = true): array
    {
        $sql = "SELECT * FROM catalogo_elemento WHERE tipo = ?";
        $params = [$tipo];

        if ($activo !== null) {
            $sql .= " AND activo = ?";
            $params[] = $activo ? 1 : 0;
        }

        $sql .= " ORDER BY nombre";

        $stmt = $this->pdo->prepare($sql);
        /** @var \PDOStatement $stmt */
        $stmt->execute($params);
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $stmt->fetchAll();

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    public function findAll(?bool $activo = null): array
    {
        $sql = "SELECT * FROM catalogo_elemento WHERE 1=1";
        $params = [];

        if ($activo !== null) {
            $sql .= " AND activo = ?";
            $params[] = $activo ? 1 : 0;
        }

        $sql .= " ORDER BY tipo, nombre";

        $stmt = $this->pdo->prepare($sql);
        /** @var \PDOStatement $stmt */
        $stmt->execute($params);
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $stmt->fetchAll();

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    public function save(CatalogoElemento $item): CatalogoElemento
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO catalogo_elemento (tipo, nombre, descripcion, activo)
            VALUES (?, ?, ?, ?)
        ");
        /** @var \PDOStatement $stmt */
        $stmt->execute([
            $item->getTipo(),
            $item->getNombre(),
            $item->getDescripcion(),
            $item->isActivo() ? 1 : 0,
        ]);
        $item->setId((int) $this->pdo->lastInsertId());
        return $item;
    }

    public function update(CatalogoElemento $item): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE catalogo_elemento SET tipo = ?, nombre = ?, descripcion = ?, activo = ?
            WHERE id = ?
        ");
        /** @var \PDOStatement $stmt */
        $stmt->execute([
            $item->getTipo(),
            $item->getNombre(),
            $item->getDescripcion(),
            $item->isActivo() ? 1 : 0,
            $item->getId(),
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM catalogo_elemento WHERE id = ?");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$id]);
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): CatalogoElemento
    {
        /** @var int $id */
        $id = $row['id'];
        /** @var string $tipo */
        $tipo = $row['tipo'];
        /** @var string $nombre */
        $nombre = $row['nombre'];
        /** @var string|null $descripcion */
        $descripcion = $row['descripcion'];
        $activo = (bool) $row['activo'];
        /** @var string|null $createdAt */
        $createdAt = $row['created_at'];

        return new CatalogoElemento(
            id: $id,
            tipo: $tipo,
            nombre: $nombre,
            descripcion: $descripcion,
            activo: $activo,
            createdAt: $createdAt,
        );
    }
}
