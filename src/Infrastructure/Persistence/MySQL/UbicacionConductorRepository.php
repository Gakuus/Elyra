<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Persistence\MySQL;

use Elyra\Domain\Entity\UbicacionConductor;
use Elyra\Domain\Repository\UbicacionConductorRepositoryInterface;
use Elyra\Domain\ValueObject\Coordenada;
use Elyra\Infrastructure\Persistence\MySQL\Connection;

final class UbicacionConductorRepository implements UbicacionConductorRepositoryInterface
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::get();
    }

    public function upsert(UbicacionConductor $ubicacion): void
    {
        $sql = "INSERT INTO ubicacion_conductor (conductor_id, traslado_id, latitud, longitud, heading, velocidad)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    traslado_id = VALUES(traslado_id),
                    latitud = VALUES(latitud),
                    longitud = VALUES(longitud),
                    heading = VALUES(heading),
                    velocidad = VALUES(velocidad),
                    updated_at = CURRENT_TIMESTAMP";

        /** @var \PDOStatement $stmt */
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $ubicacion->getConductorId(),
            $ubicacion->getTrasladoId(),
            $ubicacion->getCoordenada()->latitud(),
            $ubicacion->getCoordenada()->longitud(),
            $ubicacion->getHeading(),
            $ubicacion->getVelocidad(),
        ]);

        $histSql = "INSERT INTO historial_ubicacion (conductor_id, traslado_id, latitud, longitud)
                    VALUES (?, ?, ?, ?)";
        /** @var \PDOStatement $histStmt */
        $histStmt = $this->pdo->prepare($histSql);
        $histStmt->execute([
            $ubicacion->getConductorId(),
            $ubicacion->getTrasladoId(),
            $ubicacion->getCoordenada()->latitud(),
            $ubicacion->getCoordenada()->longitud(),
        ]);
    }

    public function findLatest(): array
    {
        $sql = "SELECT uc.*, 
                       u.nombre AS conductor_nombre,
                       u.apellido AS conductor_apellido,
                       t.codigo AS traslado_codigo,
                       t.estado AS traslado_estado,
                       t.origen AS traslado_origen,
                       t.destino AS traslado_destino
                FROM ubicacion_conductor uc
                INNER JOIN funcionario f ON f.id = uc.conductor_id
                INNER JOIN usuario u ON u.id = f.id
                LEFT JOIN traslado t ON t.id = uc.traslado_id
                WHERE f.activo = 1
                ORDER BY uc.updated_at DESC";

        /** @var \PDOStatement $stmt */
        $stmt = $this->pdo->query($sql);
        /** @var list<array<string, mixed>> $rows */
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $ubicacion = $this->hydrate($row);
            /** @var string $conductorNombre */
            $conductorNombre = $row['conductor_nombre'];
            /** @var string $conductorApellido */
            $conductorApellido = $row['conductor_apellido'];
            /** @var string|null $trasladoCodigo */
            $trasladoCodigo = $row['traslado_codigo'];
            /** @var string|null $trasladoEstado */
            $trasladoEstado = $row['traslado_estado'];
            /** @var string|null $trasladoOrigen */
            $trasladoOrigen = $row['traslado_origen'];
            /** @var string|null $trasladoDestino */
            $trasladoDestino = $row['traslado_destino'];
            $result[] = [
                'ubicacion' => $ubicacion,
                'conductor_nombre' => $conductorNombre . ' ' . $conductorApellido,
                'traslado_codigo' => $trasladoCodigo,
                'traslado_estado' => $trasladoEstado,
                'traslado_origen' => $trasladoOrigen,
                'traslado_destino' => $trasladoDestino,
            ];
        }

        return $result;
    }

    public function findByConductorId(int $conductorId): ?UbicacionConductor
    {
        $sql = "SELECT * FROM ubicacion_conductor WHERE conductor_id = ?";

        /** @var \PDOStatement $stmt */
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$conductorId]);
        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function findHistorial(int $conductorId, ?int $trasladoId, string $desde, string $hasta): array
    {
        $sql = "SELECT latitud, longitud, created_at FROM historial_ubicacion
                WHERE conductor_id = ? AND created_at BETWEEN ? AND ?";
        $params = [$conductorId, $desde, $hasta];

        if ($trasladoId !== null) {
            $sql .= " AND traslado_id = ?";
            $params[] = $trasladoId;
        }

        $sql .= " ORDER BY created_at ASC";

        /** @var \PDOStatement $stmt */
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        /** @var list<array<string, mixed>> $rows */
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            /** @var string $latitud */
            $latitud = $row['latitud'];
            /** @var string $longitud */
            $longitud = $row['longitud'];
            /** @var string $createdAt */
            $createdAt = $row['created_at'];
            $result[] = [
                'latitud' => (float) $latitud,
                'longitud' => (float) $longitud,
                'created_at' => $createdAt,
            ];
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): UbicacionConductor
    {
        /** @var int $id */
        $id = $row['id'];
        /** @var int $conductorId */
        $conductorId = $row['conductor_id'];
        /** @var string $latitud */
        $latitud = $row['latitud'];
        /** @var string $longitud */
        $longitud = $row['longitud'];
        /** @var int|null $trasladoId */
        $trasladoId = $row['traslado_id'];
        /** @var int|null $heading */
        $heading = $row['heading'];
        /** @var float|null $velocidad */
        $velocidad = $row['velocidad'];
        /** @var string|null $updatedAt */
        $updatedAt = $row['updated_at'];

        return new UbicacionConductor(
            id: $id,
            conductorId: $conductorId,
            trasladoId: $trasladoId,
            coordenada: new Coordenada((float) $latitud, (float) $longitud),
            heading: $heading,
            velocidad: $velocidad,
            updatedAt: $updatedAt,
        );
    }
}
