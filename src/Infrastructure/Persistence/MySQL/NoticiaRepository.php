<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Persistence\MySQL;

use Elyra\Domain\Entity\Noticia;
use Elyra\Domain\Repository\NoticiaRepositoryInterface;

class NoticiaRepository implements NoticiaRepositoryInterface
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::get();
    }

    public function findById(int $id): ?Noticia
    {
        $stmt = $this->pdo->prepare("
            SELECT n.*, u.nombre AS autor_nombre
            FROM noticias n
            LEFT JOIN usuario u ON u.id = n.autor_id
            WHERE n.id = ?
        ");
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
        $stmt = $this->pdo->query("
            SELECT n.*, u.nombre AS autor_nombre
            FROM noticias n
            LEFT JOIN usuario u ON u.id = n.autor_id
            ORDER BY n.created_at DESC
        ");
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $stmt->fetchAll();

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    public function findThisWeek(): array
    {
        /** @var \PDOStatement $stmt */
        $stmt = $this->pdo->query("
            SELECT n.*, u.nombre AS autor_nombre
            FROM noticias n
            LEFT JOIN usuario u ON u.id = n.autor_id
            WHERE n.activo = 1
                AND n.created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
            ORDER BY n.created_at DESC
        ");
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $stmt->fetchAll();

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    public function findLatest(int $limit = 3): array
    {
        $stmt = $this->pdo->prepare("
            SELECT n.*, u.nombre AS autor_nombre
            FROM noticias n
            LEFT JOIN usuario u ON u.id = n.autor_id
            WHERE n.activo = 1
            ORDER BY n.created_at DESC
            LIMIT ?
        ");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$limit]);
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $stmt->fetchAll();

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    public function save(Noticia $noticia): Noticia
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO noticias (titulo, contenido, imagen, autor_id, activo)
            VALUES (?, ?, ?, ?, ?)
        ");
        /** @var \PDOStatement $stmt */
        $stmt->execute([
            $noticia->getTitulo(),
            $noticia->getContenido(),
            $noticia->getImagen(),
            $noticia->getAutorId(),
            $noticia->isActivo() ? 1 : 0,
        ]);

        $id = (int) $this->pdo->lastInsertId();
        return $this->findById($id) ?? $noticia;
    }

    public function update(Noticia $noticia): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE noticias SET titulo = ?, contenido = ?, imagen = ?, activo = ?
            WHERE id = ?
        ");
        /** @var \PDOStatement $stmt */
        $stmt->execute([
            $noticia->getTitulo(),
            $noticia->getContenido(),
            $noticia->getImagen(),
            $noticia->isActivo() ? 1 : 0,
            $noticia->getId(),
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM noticias WHERE id = ?");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$id]);
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): Noticia
    {
        /** @var int $id */
        $id = $row['id'];
        /** @var string $titulo */
        $titulo = $row['titulo'];
        /** @var string $contenido */
        $contenido = $row['contenido'];

        $imagen = isset($row['imagen']) && is_string($row['imagen']) ? $row['imagen'] : null;
        /** @var int|string|null $autorIdVal */
        $autorIdVal = $row['autor_id'] ?? null;
        $autorId = $autorIdVal !== null ? (int) $autorIdVal : null;
        /** @var string|null $autorNombre */
        $autorNombre = isset($row['autor_nombre']) && is_string($row['autor_nombre']) ? $row['autor_nombre'] : null;
        /** @var int|string $activoVal */
        $activoVal = $row['activo'] ?? 1;
        $activo = (bool) $activoVal;
        /** @var string|null $createdAt */
        $createdAt = isset($row['created_at']) && is_string($row['created_at']) ? $row['created_at'] : null;

        return new Noticia(
            id: $id,
            titulo: $titulo,
            contenido: $contenido,
            imagen: $imagen,
            autorId: $autorId,
            autorNombre: $autorNombre,
            activo: $activo,
            createdAt: $createdAt,
        );
    }
}
