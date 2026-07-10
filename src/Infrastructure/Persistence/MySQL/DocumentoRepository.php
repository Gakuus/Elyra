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
    private const JOIN_PACIENTE = ' LEFT JOIN usuario pu ON pu.id = d.paciente_id';
    private const SELECT_COLS = 'd.id, d.titulo, d.descripcion, d.archivo_path, d.archivo_nombre, d.codigo_qr_id, d.qr_path, d.categoria_id, d.especialidad_id, d.encuesta_id, d.paciente_id, d.subido_por, d.activo, d.created_at, d.updated_at, c.nombre as categoria_nombre, e.nombre as especialidad_nombre, CONCAT(pu.apellido, \', \', pu.nombre) as paciente_nombre';

    public function findById(int $id): ?Documento
    {
        $stmt = $this->pdo->prepare("SELECT " . self::SELECT_COLS . " FROM documento d" . self::JOIN_CATEGORIA . self::JOIN_ESPECIALIDAD . self::JOIN_PACIENTE . " WHERE d.id = ?");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$id]);
        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch();

        if (!$row) return null;

        return $this->hydrate($row);
    }

    public function findByCodigoQr(int $codigoQrId): ?Documento
    {
        $stmt = $this->pdo->prepare("SELECT " . self::SELECT_COLS . " FROM documento d" . self::JOIN_CATEGORIA . self::JOIN_ESPECIALIDAD . self::JOIN_PACIENTE . " WHERE d.codigo_qr_id = ?");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$codigoQrId]);
        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch();

        if (!$row) return null;

        return $this->hydrate($row);
    }

    public function findByEncuesta(int $encuestaId): ?Documento
    {
        $stmt = $this->pdo->prepare("SELECT " . self::SELECT_COLS . " FROM documento d" . self::JOIN_CATEGORIA . self::JOIN_ESPECIALIDAD . self::JOIN_PACIENTE . " WHERE d.encuesta_id = ?");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$encuestaId]);
        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch();

        if (!$row) return null;

        return $this->hydrate($row);
    }

    public function findByPaciente(int $pacienteId, ?int $categoriaId = null, ?string $busqueda = null, int $page = 1, int $perPage = 20): array
    {
        return $this->findAll($categoriaId, $busqueda, $pacienteId, $page, $perPage);
    }

    public function countByPaciente(int $pacienteId, ?int $categoriaId = null, ?string $busqueda = null): int
    {
        return $this->count($categoriaId, $busqueda, $pacienteId);
    }

    public function findAll(?int $categoriaId = null, ?string $busqueda = null, ?int $pacienteId = null, int $page = 1, int $perPage = 20): array
    {
        $sql = "SELECT " . self::SELECT_COLS . " FROM documento d" . self::JOIN_CATEGORIA . self::JOIN_ESPECIALIDAD . self::JOIN_PACIENTE . " WHERE 1=1";
        $params = [];

        if ($categoriaId !== null) {
            $sql .= " AND d.categoria_id = ?";
            $params[] = $categoriaId;
        }

        if ($busqueda !== null && $busqueda !== '') {
            $sql .= " AND (d.titulo LIKE ? OR d.descripcion LIKE ? OR CONCAT(pu.apellido, ', ', pu.nombre) LIKE ?)";
            $params[] = "%{$busqueda}%";
            $params[] = "%{$busqueda}%";
            $params[] = "%{$busqueda}%";
        }

        if ($pacienteId !== null) {
            $sql .= " AND d.paciente_id = ?";
            $params[] = $pacienteId;
        }

        $sql .= " ORDER BY d.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare($sql);
        /** @var \PDOStatement $stmt */
        $stmt->execute($params);
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $stmt->fetchAll();

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    public function count(?int $categoriaId = null, ?string $busqueda = null, ?int $pacienteId = null): int
    {
        $sql = "SELECT COUNT(*) FROM documento d LEFT JOIN usuario pu ON pu.id = d.paciente_id WHERE 1=1";
        $params = [];

        if ($categoriaId !== null) {
            $sql .= " AND d.categoria_id = ?";
            $params[] = $categoriaId;
        }

        if ($busqueda !== null && $busqueda !== '') {
            $sql .= " AND (d.titulo LIKE ? OR d.descripcion LIKE ? OR CONCAT(pu.apellido, ', ', pu.nombre) LIKE ?)";
            $params[] = "%{$busqueda}%";
            $params[] = "%{$busqueda}%";
            $params[] = "%{$busqueda}%";
        }

        if ($pacienteId !== null) {
            $sql .= " AND d.paciente_id = ?";
            $params[] = $pacienteId;
        }

        $stmt = $this->pdo->prepare($sql);
        /** @var \PDOStatement $stmt */
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function countTotal(): int
    {
        /** @var \PDOStatement $stmt */
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM documento");
        return (int) $stmt->fetchColumn();
    }

    public function countActivos(): int
    {
        /** @var \PDOStatement $stmt */
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM documento WHERE activo = TRUE");
        return (int) $stmt->fetchColumn();
    }

    public function findGenerales(?int $categoriaId = null, ?string $busqueda = null, int $page = 1, int $perPage = 20): array
    {
        $sql = "SELECT " . self::SELECT_COLS . " FROM documento d" . self::JOIN_CATEGORIA . self::JOIN_ESPECIALIDAD . self::JOIN_PACIENTE . " WHERE d.paciente_id IS NULL";
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
        /** @var \PDOStatement $stmt */
        $stmt->execute($params);
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $stmt->fetchAll();

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    public function countGenerales(?int $categoriaId = null, ?string $busqueda = null): int
    {
        $sql = "SELECT COUNT(*) FROM documento d WHERE d.paciente_id IS NULL";
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
        /** @var \PDOStatement $stmt */
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function findDePacientes(?int $categoriaId = null, ?string $busqueda = null, ?int $pacienteId = null, int $page = 1, int $perPage = 20): array
    {
        $sql = "SELECT " . self::SELECT_COLS . " FROM documento d" . self::JOIN_CATEGORIA . self::JOIN_ESPECIALIDAD . self::JOIN_PACIENTE . " WHERE d.paciente_id IS NOT NULL";
        $params = [];

        if ($categoriaId !== null) {
            $sql .= " AND d.categoria_id = ?";
            $params[] = $categoriaId;
        }

        if ($busqueda !== null && $busqueda !== '') {
            $sql .= " AND (d.titulo LIKE ? OR d.descripcion LIKE ? OR CONCAT(pu.apellido, ', ', pu.nombre) LIKE ?)";
            $params[] = "%{$busqueda}%";
            $params[] = "%{$busqueda}%";
            $params[] = "%{$busqueda}%";
        }

        if ($pacienteId !== null) {
            $sql .= " AND d.paciente_id = ?";
            $params[] = $pacienteId;
        }

        $sql .= " ORDER BY d.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare($sql);
        /** @var \PDOStatement $stmt */
        $stmt->execute($params);
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $stmt->fetchAll();

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    public function countDePacientes(?int $categoriaId = null, ?string $busqueda = null, ?int $pacienteId = null): int
    {
        $sql = "SELECT COUNT(*) FROM documento d LEFT JOIN usuario pu ON pu.id = d.paciente_id WHERE d.paciente_id IS NOT NULL";
        $params = [];

        if ($categoriaId !== null) {
            $sql .= " AND d.categoria_id = ?";
            $params[] = $categoriaId;
        }

        if ($busqueda !== null && $busqueda !== '') {
            $sql .= " AND (d.titulo LIKE ? OR d.descripcion LIKE ? OR CONCAT(pu.apellido, ', ', pu.nombre) LIKE ?)";
            $params[] = "%{$busqueda}%";
            $params[] = "%{$busqueda}%";
            $params[] = "%{$busqueda}%";
        }

        if ($pacienteId !== null) {
            $sql .= " AND d.paciente_id = ?";
            $params[] = $pacienteId;
        }

        $stmt = $this->pdo->prepare($sql);
        /** @var \PDOStatement $stmt */
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getArchivoContent(int $id): ?string
    {
        $stmt = $this->pdo->prepare("SELECT archivo_contenido FROM documento WHERE id = ?");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$id]);
        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        /** @var string|null $content */
        $content = $row ? $row['archivo_contenido'] : null;
        return $content;
    }

    public function save(Documento $documento): Documento
    {
        $contenido = $documento->getArchivoContenido()
            ?? (is_file($documento->getArchivoPath()) ? file_get_contents($documento->getArchivoPath()) : null);

        $stmt = $this->pdo->prepare("
            INSERT INTO documento (titulo, descripcion, archivo_path, archivo_nombre, archivo_contenido, codigo_qr_id, qr_path, categoria_id, especialidad_id, encuesta_id, paciente_id, subido_por, activo)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        /** @var \PDOStatement $stmt */
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
            $documento->getPacienteId(),
            $documento->getSubidoPor(),
            $documento->isActivo() ? 1 : 0,
        ]);
        $documento->setId((int) $this->pdo->lastInsertId());
        return $documento;
    }

    public function update(Documento $documento): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE documento SET titulo = ?, descripcion = ?, categoria_id = ?, especialidad_id = ?, paciente_id = ?, activo = ?
            WHERE id = ?
        ");
        /** @var \PDOStatement $stmt */
        $stmt->execute([
            $documento->getTitulo(),
            $documento->getDescripcion(),
            $documento->getCategoriaId(),
            $documento->getEspecialidadId(),
            $documento->getPacienteId(),
            $documento->isActivo() ? 1 : 0,
            $documento->getId(),
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("UPDATE documento SET activo = FALSE WHERE id = ?");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$id]);
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): Documento
    {
        /** @var int $id */
        $id = $row['id'];
        /** @var string $titulo */
        $titulo = $row['titulo'];
        /** @var string $archivoPath */
        $archivoPath = $row['archivo_path'];
        /** @var string $archivoNombre */
        $archivoNombre = $row['archivo_nombre'];
        /** @var string|null $codigoQrIdRaw */
        $codigoQrIdRaw = $row['codigo_qr_id'];
        /** @var int|null $codigoQrId */
        $codigoQrId = $codigoQrIdRaw !== null ? (int) $codigoQrIdRaw : null;
        /** @var int $categoriaId */
        $categoriaId = $row['categoria_id'];
        /** @var int $subidoPor */
        $subidoPor = $row['subido_por'];
        /** @var string|null $descripcion */
        $descripcion = $row['descripcion'];
        /** @var string|null $qrPath */
        $qrPath = $row['qr_path'];
        /** @var string|null $especialidadIdRaw */
        $especialidadIdRaw = $row['especialidad_id'];
        /** @var int|null $especialidadId */
        $especialidadId = $especialidadIdRaw !== null ? (int) $especialidadIdRaw : null;
        /** @var string|null $encuestaIdRaw */
        $encuestaIdRaw = $row['encuesta_id'];
        /** @var int|null $encuestaId */
        $encuestaId = $encuestaIdRaw !== null ? (int) $encuestaIdRaw : null;
        /** @var string|null $pacienteIdRaw */
        $pacienteIdRaw = $row['paciente_id'];
        /** @var int|null $pacienteId */
        $pacienteId = $pacienteIdRaw !== null ? (int) $pacienteIdRaw : null;
        $activo = (bool) $row['activo'];
        /** @var string|null $createdAt */
        $createdAt = $row['created_at'];
        /** @var string|null $updatedAt */
        $updatedAt = $row['updated_at'];
        /** @var string|null $categoriaNombre */
        $categoriaNombre = $row['categoria_nombre'] ?? null;
        /** @var string|null $especialidadNombre */
        $especialidadNombre = $row['especialidad_nombre'] ?? null;
        /** @var string|null $pacienteNombre */
        $pacienteNombre = $row['paciente_nombre'] ?? null;

        return new Documento(
            id: $id,
            titulo: $titulo,
            archivoPath: $archivoPath,
            archivoNombre: $archivoNombre,
            codigoQrId: $codigoQrId,
            categoriaId: $categoriaId,
            subidoPor: $subidoPor,
            descripcion: $descripcion,
            qrPath: $qrPath,
            especialidadId: $especialidadId,
            encuestaId: $encuestaId,
            pacienteId: $pacienteId,
            activo: $activo,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            categoriaNombre: $categoriaNombre,
            especialidadNombre: $especialidadNombre,
            pacienteNombre: $pacienteNombre
        );
    }
}
