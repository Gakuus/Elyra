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

    private const JOIN_CATEGORIA = ' JOIN categoria c ON c.id = d.categoria_id';
    private const JOIN_ESPECIALIDAD = ' LEFT JOIN categoria e ON e.id = d.especialidad_id';
    private const SELECT_COLS = 'd.id, d.titulo, d.descripcion, d.archivo_path, d.archivo_nombre, d.codigo_qr_id, d.qr_path, d.categoria_id, d.especialidad_id, d.encuesta_id, d.subido_por, d.activo, d.created_at, d.updated_at, c.nombre as categoria_nombre, e.nombre as especialidad_nombre';

    public function findById(int $id): ?Documento
    {
        $stmt = $this->pdo->prepare("SELECT " . self::SELECT_COLS . " FROM documento d" . self::JOIN_CATEGORIA . self::JOIN_ESPECIALIDAD . " WHERE d.id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return $this->hydrate($row);
    }

    public function findByCodigoQr(int $codigoQrId): ?Documento
    {
        $stmt = $this->pdo->prepare("SELECT " . self::SELECT_COLS . " FROM documento d" . self::JOIN_CATEGORIA . self::JOIN_ESPECIALIDAD . " WHERE d.codigo_qr_id = ?");
        $stmt->execute([$codigoQrId]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return $this->hydrate($row);
    }

    public function findByEncuesta(int $encuestaId): ?Documento
    {
        $stmt = $this->pdo->prepare("SELECT " . self::SELECT_COLS . " FROM documento d" . self::JOIN_CATEGORIA . self::JOIN_ESPECIALIDAD . " WHERE d.encuesta_id = ?");
        $stmt->execute([$encuestaId]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return $this->hydrate($row);
    }

    public function findAll(?int $categoriaId = null, ?string $busqueda = null, int $page = 1, int $perPage = 20): array
    {
        $sql = "SELECT " . self::SELECT_COLS . " FROM documento d" . self::JOIN_CATEGORIA . self::JOIN_ESPECIALIDAD . " WHERE 1=1";
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

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
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

    public function getArchivoContent(int $id): ?string
    {
        $stmt = $this->pdo->prepare("SELECT archivo_contenido FROM documento WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? $row['archivo_contenido'] : null;
    }

    public function save(Documento $documento): Documento
    {
        $contenido = $documento->getArchivoContenido()
            ?? (is_file($documento->getArchivoPath()) ? file_get_contents($documento->getArchivoPath()) : null);

        $stmt = $this->pdo->prepare("
            INSERT INTO documento (titulo, descripcion, archivo_path, archivo_nombre, archivo_contenido, codigo_qr_id, qr_path, categoria_id, especialidad_id, encuesta_id, subido_por, activo)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $documento->getTitulo(),
            $documento->getDescripcion(),
            $documento->getArchivoPath(),
            $documento->getArchivoNombre(),
            $contenido,
            $documento->getCodigoQrId(),
            $documento->getQrPath(),
            $documento->getCategoriaId(),
            $documento->getEspecialidadId(),
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
            UPDATE documento SET titulo = ?, descripcion = ?, categoria_id = ?, especialidad_id = ?, activo = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $documento->getTitulo(),
            $documento->getDescripcion(),
            $documento->getCategoriaId(),
            $documento->getEspecialidadId(),
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
            codigoQrId: $row['codigo_qr_id'] !== null ? (int) $row['codigo_qr_id'] : null,
            categoriaId: (int) $row['categoria_id'],
            subidoPor: (int) $row['subido_por'],
            descripcion: $row['descripcion'],
            qrPath: $row['qr_path'],
            especialidadId: $row['especialidad_id'] !== null ? (int) $row['especialidad_id'] : null,
            encuestaId: $row['encuesta_id'] !== null ? (int) $row['encuesta_id'] : null,
            activo: (bool) $row['activo'],
            createdAt: $row['created_at'],
            updatedAt: $row['updated_at'],
            categoriaNombre: $row['categoria_nombre'] ?? null,
            especialidadNombre: $row['especialidad_nombre'] ?? null
        );
    }
}
