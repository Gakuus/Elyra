<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Persistence\MySQL;

use Elyra\Domain\Entity\Documento;
use Elyra\Domain\Repository\DocumentoRepositoryInterface;

class DocumentoRepository implements DocumentoRepositoryInterface
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::get();
    }

    public function findById(int $id): ?Documento
    {
        $stmt = $this->pdo->prepare("SELECT * FROM documento WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return $this->hydrate($row);
    }

    public function findByCodigoQr(int $codigoQrId): ?Documento
    {
        $stmt = $this->pdo->prepare("SELECT * FROM documento WHERE codigo_qr_id = ?");
        $stmt->execute([$codigoQrId]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return $this->hydrate($row);
    }

    public function findByEncuesta(int $encuestaId): ?Documento
    {
        $stmt = $this->pdo->prepare("SELECT * FROM documento WHERE encuesta_id = ?");
        $stmt->execute([$encuestaId]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return $this->hydrate($row);
    }

    public function findAll(?int $categoriaId = null, ?string $busqueda = null, int $page = 1, int $perPage = 20): array
    {
        $sql = "SELECT d.*, c.nombre as categoria_nombre FROM documento d JOIN categoria c ON c.id = d.categoria_id WHERE 1=1";
        $params = [];

        if ($categoriaId !== null) {
            $sql .= " AND d.categoria_id = ?";
            $params[] = $categoriaId;
        }

        if ($busqueda !== null && $busqueda !== '') {
            $sql .= " AND (d.titulo LIKE ? OR d.descripcion LIKE ?)";
            $params[] = "%{$busqueda}%";
            $params[] = "%{$busqueda}%";
        }

        $sql .= " ORDER BY d.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->hydrate($row);
        }
        return $result;
    }

    public function count(?int $categoriaId = null, ?string $busqueda = null): int
    {
        $sql = "SELECT COUNT(*) FROM documento d WHERE 1=1";
        $params = [];

        if ($categoriaId !== null) {
            $sql .= " AND d.categoria_id = ?";
            $params[] = $categoriaId;
        }

        if ($busqueda !== null && $busqueda !== '') {
            $sql .= " AND (d.titulo LIKE ? OR d.descripcion LIKE ?)";
            $params[] = "%{$busqueda}%";
            $params[] = "%{$busqueda}%";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function countTotal(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM documento");
        return (int) $stmt->fetchColumn();
    }

    public function countActivos(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM documento WHERE activo = TRUE");
        return (int) $stmt->fetchColumn();
    }

    public function save(Documento $documento): Documento
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO documento (titulo, descripcion, archivo_path, archivo_nombre, codigo_qr_id, qr_path, categoria_id, encuesta_id, subido_por, activo)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $documento->getTitulo(),
            $documento->getDescripcion(),
            $documento->getArchivoPath(),
            $documento->getArchivoNombre(),
            $documento->getCodigoQrId(),
            $documento->getQrPath(),
            $documento->getCategoriaId(),
            $documento->getEncuestaId(),
            $documento->getSubidoPor(),
            $documento->isActivo() ? 1 : 0,
        ]);
        $documento->setId((int) $this->pdo->lastInsertId());
        return $documento;
    }

    public function update(Documento $documento): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE documento SET titulo = ?, descripcion = ?, categoria_id = ?, activo = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $documento->getTitulo(),
            $documento->getDescripcion(),
            $documento->getCategoriaId(),
            $documento->isActivo() ? 1 : 0,
            $documento->getId(),
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("UPDATE documento SET activo = FALSE WHERE id = ?");
        $stmt->execute([$id]);
    }

    private function hydrate(array $row): Documento
    {
        return new Documento(
            id: (int) $row['id'],
            titulo: $row['titulo'],
            archivoPath: $row['archivo_path'],
            archivoNombre: $row['archivo_nombre'],
            codigoQrId: (int) $row['codigo_qr_id'],
            categoriaId: (int) $row['categoria_id'],
            subidoPor: (int) $row['subido_por'],
            descripcion: $row['descripcion'],
            qrPath: $row['qr_path'],
            encuestaId: $row['encuesta_id'] !== null ? (int) $row['encuesta_id'] : null,
            activo: (bool) $row['activo'],
            createdAt: $row['created_at'],
            updatedAt: $row['updated_at']
        );
    }
}
