<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Persistence\MySQL;

use Elyra\Domain\Entity\ElementoTraslado;
use Elyra\Domain\Entity\HistorialEstado;
use Elyra\Domain\Entity\Traslado;
use Elyra\Domain\Repository\TrasladoRepositoryInterface;
use Elyra\Domain\ValueObject\EstadoTraslado;
use Elyra\Domain\ValueObject\TipoElemento;

class TrasladoRepository implements TrasladoRepositoryInterface
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::get();
    }

    public function findById(int $id): ?Traslado
    {
        $stmt = $this->pdo->prepare("SELECT * FROM traslado WHERE id = ?");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$id]);
        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch();

        if (!$row) return null;

        return $this->hydrate($row);
    }

    public function findByCodigo(string $codigo): ?Traslado
    {
        $stmt = $this->pdo->prepare("SELECT * FROM traslado WHERE codigo = ?");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$codigo]);
        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch();

        if (!$row) return null;

        return $this->hydrate($row);
    }

    public function findElementosByTrasladoId(int $trasladoId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM elemento_traslado WHERE traslado_id = ? ORDER BY id");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$trasladoId]);
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $stmt->fetchAll();

        return array_map(fn (array $row) => $this->hydrateElemento($row), $rows);
    }

    public function findHistorialByTrasladoId(int $trasladoId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM historial_estado WHERE traslado_id = ? ORDER BY created_at ASC");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$trasladoId]);
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $stmt->fetchAll();

        return array_map(fn (array $row) => $this->hydrateHistorial($row), $rows);
    }

    public function findAll(?string $estado = null, ?int $conductorId = null, ?string $fechaDesde = null, ?string $fechaHasta = null, int $page = 1, int $perPage = 20): array
    {
        $sql = "SELECT * FROM traslado WHERE 1=1";
        $params = [];

        if ($estado !== null) {
            $sql .= " AND estado = ?";
            $params[] = $estado;
        }
        if ($conductorId !== null) {
            $sql .= " AND conductor_id = ?";
            $params[] = $conductorId;
        }
        if ($fechaDesde !== null) {
            $sql .= " AND DATE(created_at) >= ?";
            $params[] = $fechaDesde;
        }
        if ($fechaHasta !== null) {
            $sql .= " AND DATE(created_at) <= ?";
            $params[] = $fechaHasta;
        }

        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare($sql);
        /** @var \PDOStatement $stmt */
        $stmt->execute($params);
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $stmt->fetchAll();

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    public function count(?string $estado = null, ?int $conductorId = null): int
    {
        $sql = "SELECT COUNT(*) FROM traslado WHERE 1=1";
        $params = [];

        if ($estado !== null) {
            $sql .= " AND estado = ?";
            $params[] = $estado;
        }
        if ($conductorId !== null) {
            $sql .= " AND conductor_id = ?";
            $params[] = $conductorId;
        }

        $stmt = $this->pdo->prepare($sql);
        /** @var \PDOStatement $stmt */
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function countTotal(): int
    {
        /** @var \PDOStatement $stmt */
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM traslado");
        return (int) $stmt->fetchColumn();
    }

    public function countByEstado(string $estado): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM traslado WHERE estado = ?");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$estado]);
        return (int) $stmt->fetchColumn();
    }

    public function save(Traslado $traslado): Traslado
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO traslado (codigo, conductor_id, copiloto_id, vehiculo_id, ruta_id, origen, destino,
                hora_salida_estimada, hora_salida_efectiva, hora_llegada_destino, hora_inicio_retorno,
                hora_llegada_hospital, estado, motivo_cancelacion, registrado_por, observaciones)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        /** @var \PDOStatement $stmt */
        $stmt->execute([
            $traslado->getCodigo(),
            $traslado->getConductorId(),
            $traslado->getCopilotoId(),
            $traslado->getVehiculoId(),
            $traslado->getRutaId(),
            $traslado->getOrigen(),
            $traslado->getDestino(),
            $traslado->getHoraSalidaEstimada(),
            $traslado->getHoraSalidaEfectiva(),
            $traslado->getHoraLlegadaDestino(),
            $traslado->getHoraInicioRetorno(),
            $traslado->getHoraLlegadaHospital(),
            $traslado->getEstado()->value(),
            $traslado->getMotivoCancelacion(),
            $traslado->getRegistradoPor(),
            $traslado->getObservaciones(),
        ]);
        $traslado->setId((int) $this->pdo->lastInsertId());
        return $traslado;
    }

    public function saveElemento(ElementoTraslado $elemento): ElementoTraslado
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO elemento_traslado (traslado_id, tipo, paciente_id, descripcion, cantidad)
            VALUES (?, ?, ?, ?, ?)
        ");
        /** @var \PDOStatement $stmt */
        $stmt->execute([
            $elemento->getTrasladoId(),
            $elemento->getTipo()->value(),
            $elemento->getPacienteId(),
            $elemento->getDescripcion(),
            $elemento->getCantidad(),
        ]);
        $elemento->setId((int) $this->pdo->lastInsertId());
        return $elemento;
    }

    public function saveHistorial(HistorialEstado $historial): HistorialEstado
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO historial_estado (traslado_id, estado_anterior, estado_nuevo, observacion, actualizado_por)
            VALUES (?, ?, ?, ?, ?)
        ");
        /** @var \PDOStatement $stmt */
        $stmt->execute([
            $historial->getTrasladoId(),
            $historial->getEstadoAnterior(),
            $historial->getEstadoNuevo(),
            $historial->getObservacion(),
            $historial->getActualizadoPor(),
        ]);
        $historial->setId((int) $this->pdo->lastInsertId());
        return $historial;
    }

    public function update(Traslado $traslado): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE traslado SET conductor_id = ?, copiloto_id = ?, vehiculo_id = ?, ruta_id = ?,
                origen = ?, destino = ?, hora_salida_estimada = ?, hora_salida_efectiva = ?,
                hora_llegada_destino = ?, hora_inicio_retorno = ?, hora_llegada_hospital = ?,
                estado = ?, motivo_cancelacion = ?, observaciones = ?
            WHERE id = ?
        ");
        /** @var \PDOStatement $stmt */
        $stmt->execute([
            $traslado->getConductorId(),
            $traslado->getCopilotoId(),
            $traslado->getVehiculoId(),
            $traslado->getRutaId(),
            $traslado->getOrigen(),
            $traslado->getDestino(),
            $traslado->getHoraSalidaEstimada(),
            $traslado->getHoraSalidaEfectiva(),
            $traslado->getHoraLlegadaDestino(),
            $traslado->getHoraInicioRetorno(),
            $traslado->getHoraLlegadaHospital(),
            $traslado->getEstado()->value(),
            $traslado->getMotivoCancelacion(),
            $traslado->getObservaciones(),
            $traslado->getId(),
        ]);
    }

    public function nextCodigo(): string
    {
        /** @var \PDOStatement $stmt */
        $stmt = $this->pdo->query("SELECT MAX(id) FROM traslado");
        $maxId = (int) $stmt->fetchColumn();
        $year = date('y');
        $secuencial = str_pad((string)($maxId + 1), 3, '0', STR_PAD_LEFT);
        return "TR-{$year}{$secuencial}";
    }

    public function delete(int $id): void
    {
        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare("DELETE FROM elemento_traslado WHERE traslado_id = ?")->execute([$id]);
            $this->pdo->prepare("DELETE FROM historial_estado WHERE traslado_id = ?")->execute([$id]);
            $this->pdo->prepare("DELETE FROM traslado WHERE id = ?")->execute([$id]);
            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function hydrate(array $row): Traslado
    {
        return new Traslado(
            id: (int) $row['id'],
            codigo: $row['codigo'],
            conductorId: (int) $row['conductor_id'],
            origen: $row['origen'],
            destino: $row['destino'],
            registradoPor: (int) $row['registrado_por'],
            copilotoId: $row['copiloto_id'] !== null ? (int) $row['copiloto_id'] : null,
            vehiculoId: $row['vehiculo_id'] !== null ? (int) $row['vehiculo_id'] : null,
            rutaId: $row['ruta_id'] !== null ? (int) $row['ruta_id'] : null,
            horaSalidaEstimada: $row['hora_salida_estimada'],
            horaSalidaEfectiva: $row['hora_salida_efectiva'],
            horaLlegadaDestino: $row['hora_llegada_destino'],
            horaInicioRetorno: $row['hora_inicio_retorno'],
            horaLlegadaHospital: $row['hora_llegada_hospital'],
            estado: $row['estado'],
            motivoCancelacion: $row['motivo_cancelacion'],
            observaciones: $row['observaciones'],
            createdAt: $row['created_at'],
            updatedAt: $row['updated_at'],
        );
    }

    private function hydrateElemento(array $row): ElementoTraslado
    {
        return new ElementoTraslado(
            id: (int) $row['id'],
            trasladoId: (int) $row['traslado_id'],
            tipo: new TipoElemento($row['tipo']),
            cantidad: (int) $row['cantidad'],
            pacienteId: $row['paciente_id'] !== null ? (int) $row['paciente_id'] : null,
            descripcion: $row['descripcion'],
            createdAt: $row['created_at'],
        );
    }

    private function hydrateHistorial(array $row): HistorialEstado
    {
        return new HistorialEstado(
            id: (int) $row['id'],
            trasladoId: (int) $row['traslado_id'],
            estadoNuevo: $row['estado_nuevo'],
            actualizadoPor: (int) $row['actualizado_por'],
            estadoAnterior: $row['estado_anterior'],
            observacion: $row['observacion'],
            createdAt: $row['created_at'],
        );
    }
}
