<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Persistence\MySQL;

use Elyra\Domain\Entity\ElementoTraslado;
use Elyra\Domain\Entity\HistorialEstado;
use Elyra\Domain\Entity\Traslado;
use Elyra\Domain\Repository\TrasladoRepositoryInterface;
use Elyra\Domain\ValueObject\Coordenada;
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

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): Traslado
    {
        /** @var int $id */
        $id = $row['id'];
        /** @var string $codigo */
        $codigo = $row['codigo'];
        /** @var int $conductorId */
        $conductorId = $row['conductor_id'];
        /** @var string $origen */
        $origen = $row['origen'];
        /** @var string $destino */
        $destino = $row['destino'];
        /** @var int $registradoPor */
        $registradoPor = $row['registrado_por'];
        /** @var string|null $copilotoIdRaw */
        $copilotoIdRaw = $row['copiloto_id'];
        /** @var int|null $copilotoId */
        $copilotoId = $copilotoIdRaw !== null ? (int) $copilotoIdRaw : null;
        /** @var string|null $vehiculoIdRaw */
        $vehiculoIdRaw = $row['vehiculo_id'];
        /** @var int|null $vehiculoId */
        $vehiculoId = $vehiculoIdRaw !== null ? (int) $vehiculoIdRaw : null;
        /** @var string|null $rutaIdRaw */
        $rutaIdRaw = $row['ruta_id'];
        /** @var int|null $rutaId */
        $rutaId = $rutaIdRaw !== null ? (int) $rutaIdRaw : null;
        /** @var string|null $horaSalidaEstimada */
        $horaSalidaEstimada = $row['hora_salida_estimada'];
        /** @var string|null $horaSalidaEfectiva */
        $horaSalidaEfectiva = $row['hora_salida_efectiva'];
        /** @var string|null $horaLlegadaDestino */
        $horaLlegadaDestino = $row['hora_llegada_destino'];
        /** @var string|null $horaInicioRetorno */
        $horaInicioRetorno = $row['hora_inicio_retorno'];
        /** @var string|null $horaLlegadaHospital */
        $horaLlegadaHospital = $row['hora_llegada_hospital'];
        /** @var string|null $estado */
        $estado = $row['estado'];
        /** @var string|null $motivoCancelacion */
        $motivoCancelacion = $row['motivo_cancelacion'];
        /** @var string|null $observaciones */
        $observaciones = $row['observaciones'];
        /** @var string|null $createdAt */
        $createdAt = $row['created_at'];
        /** @var string|null $updatedAt */
        $updatedAt = $row['updated_at'];

        $origenCoord = null;
        /** @var string|null $origenLat */
        $origenLat = $row['origen_lat'];
        /** @var string|null $origenLng */
        $origenLng = $row['origen_lng'];
        if ($origenLat !== null && $origenLng !== null) {
            $origenCoord = new Coordenada((float) $origenLat, (float) $origenLng);
        }
        $destinoCoord = null;
        /** @var string|null $destinoLat */
        $destinoLat = $row['destino_lat'];
        /** @var string|null $destinoLng */
        $destinoLng = $row['destino_lng'];
        if ($destinoLat !== null && $destinoLng !== null) {
            $destinoCoord = new Coordenada((float) $destinoLat, (float) $destinoLng);
        }

        return new Traslado(
            id: $id,
            codigo: $codigo,
            conductorId: $conductorId,
            origen: $origen,
            destino: $destino,
            registradoPor: $registradoPor,
            copilotoId: $copilotoId,
            vehiculoId: $vehiculoId,
            rutaId: $rutaId,
            horaSalidaEstimada: $horaSalidaEstimada,
            horaSalidaEfectiva: $horaSalidaEfectiva,
            horaLlegadaDestino: $horaLlegadaDestino,
            horaInicioRetorno: $horaInicioRetorno,
            horaLlegadaHospital: $horaLlegadaHospital,
            estado: $estado,
            motivoCancelacion: $motivoCancelacion,
            observaciones: $observaciones,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            origenCoordenada: $origenCoord,
            destinoCoordenada: $destinoCoord,
        );
    }

    /** @param array<string, mixed> $row */
    private function hydrateElemento(array $row): ElementoTraslado
    {
        /** @var int $id */
        $id = $row['id'];
        /** @var int $trasladoId */
        $trasladoId = $row['traslado_id'];
        /** @var string $tipo */
        $tipo = $row['tipo'];
        /** @var int $cantidad */
        $cantidad = $row['cantidad'];
        /** @var string|null $pacienteIdRaw */
        $pacienteIdRaw = $row['paciente_id'];
        /** @var int|null $pacienteId */
        $pacienteId = $pacienteIdRaw !== null ? (int) $pacienteIdRaw : null;
        /** @var string|null $descripcion */
        $descripcion = $row['descripcion'];
        /** @var string|null $createdAt */
        $createdAt = $row['created_at'];

        return new ElementoTraslado(
            id: $id,
            trasladoId: $trasladoId,
            tipo: new TipoElemento($tipo),
            cantidad: $cantidad,
            pacienteId: $pacienteId,
            descripcion: $descripcion,
            createdAt: $createdAt,
        );
    }

    /** @param array<string, mixed> $row */
    private function hydrateHistorial(array $row): HistorialEstado
    {
        /** @var int $id */
        $id = $row['id'];
        /** @var int $trasladoId */
        $trasladoId = $row['traslado_id'];
        /** @var string $estadoNuevo */
        $estadoNuevo = $row['estado_nuevo'];
        /** @var int $actualizadoPor */
        $actualizadoPor = $row['actualizado_por'];
        /** @var string|null $estadoAnterior */
        $estadoAnterior = $row['estado_anterior'];
        /** @var string|null $observacion */
        $observacion = $row['observacion'];
        /** @var string|null $createdAt */
        $createdAt = $row['created_at'];

        return new HistorialEstado(
            id: $id,
            trasladoId: $trasladoId,
            estadoNuevo: $estadoNuevo,
            actualizadoPor: $actualizadoPor,
            estadoAnterior: $estadoAnterior,
            observacion: $observacion,
            createdAt: $createdAt,
        );
    }
}
