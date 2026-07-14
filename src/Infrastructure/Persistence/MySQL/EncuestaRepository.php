<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Persistence\MySQL;

use Elyra\Domain\Entity\Encuesta;
use Elyra\Domain\Entity\Pregunta;
use Elyra\Domain\Entity\Respuesta;
use Elyra\Domain\Repository\EncuestaRepositoryInterface;
use Elyra\Domain\ValueObject\TipoPregunta;

class EncuestaRepository implements EncuestaRepositoryInterface
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::get();
    }

    public function findById(int $id): ?Encuesta
    {
        $stmt = $this->pdo->prepare("SELECT * FROM encuesta WHERE id = ?");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$id]);
        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch();

        if (!$row) return null;

        return $this->hydrate($row);
    }

    public function findPreguntasByEncuestaId(int $encuestaId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM pregunta WHERE encuesta_id = ? ORDER BY `orden`");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$encuestaId]);
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $stmt->fetchAll();

        return array_map(fn (array $row) => $this->hydratePregunta($row), $rows);
    }

    public function findRespuestasByEncuestaId(int $encuestaId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM respuesta WHERE encuesta_id = ? ORDER BY created_at");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$encuestaId]);
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $stmt->fetchAll();

        return array_map(fn (array $row) => $this->hydrateRespuesta($row), $rows);
    }

    public function findRespuestasByPreguntaId(int $preguntaId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM respuesta WHERE pregunta_id = ?");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$preguntaId]);
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $stmt->fetchAll();

        return array_map(fn (array $row) => $this->hydrateRespuesta($row), $rows);
    }

    public function findAll(bool $soloActivas = false): array
    {
        $sql = "SELECT * FROM encuesta";
        $params = [];
        if ($soloActivas) {
            $sql .= " WHERE activa = 1";
        }
        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        /** @var \PDOStatement $stmt */
        $stmt->execute($params);
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $stmt->fetchAll();

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    public function countTotal(): int
    {
        /** @var \PDOStatement $stmt */
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM encuesta");
        return (int) $stmt->fetchColumn();
    }

    public function countActivas(): int
    {
        /** @var \PDOStatement $stmt */
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM encuesta WHERE activa = 1");
        return (int) $stmt->fetchColumn();
    }

    public function countRespuestas(): int
    {
        /** @var \PDOStatement $stmt */
        $stmt = $this->pdo->query("SELECT COUNT(DISTINCT sesion_token) FROM respuesta");
        return (int) $stmt->fetchColumn();
    }

    public function save(Encuesta $encuesta): Encuesta
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO encuesta (titulo, descripcion, activa, creada_por) VALUES (?, ?, ?, ?)"
        );
        /** @var \PDOStatement $stmt */
        $stmt->execute([
            $encuesta->getTitulo(),
            $encuesta->getDescripcion(),
            $encuesta->isActiva() ? 1 : 0,
            $encuesta->getCreadaPor(),
        ]);
        $encuesta->setId((int) $this->pdo->lastInsertId());
        return $encuesta;
    }

    public function savePregunta(Pregunta $pregunta): Pregunta
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO pregunta (encuesta_id, tipo, texto, opciones, requerida, `orden`) VALUES (?, ?, ?, ?, ?, ?)"
        );
        /** @var \PDOStatement $stmt */
        $stmt->execute([
            $pregunta->getEncuestaId(),
            $pregunta->getTipo()->value(),
            $pregunta->getTexto(),
            $pregunta->getOpciones() !== null ? json_encode($pregunta->getOpciones(), JSON_UNESCAPED_UNICODE) : null,
            $pregunta->isRequerida() ? 1 : 0,
            $pregunta->getOrden(),
        ]);
        $pregunta->setId((int) $this->pdo->lastInsertId());
        return $pregunta;
    }

    public function saveRespuesta(Respuesta $respuesta): Respuesta
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO respuesta (sesion_token, encuesta_id, pregunta_id, token_paciente, valor_opcion, valor_texto, valor_numerico)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        /** @var \PDOStatement $stmt */
        $stmt->execute([
            $respuesta->getSesionToken(),
            $respuesta->getEncuestaId(),
            $respuesta->getPreguntaId(),
            $respuesta->getTokenPaciente(),
            $respuesta->getValorOpcion(),
            $respuesta->getValorTexto(),
            $respuesta->getValorNumerico(),
        ]);
        $respuesta->setId((int) $this->pdo->lastInsertId());
        return $respuesta;
    }

    public function update(Encuesta $encuesta): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE encuesta SET titulo = ?, descripcion = ?, activa = ? WHERE id = ?"
        );
        /** @var \PDOStatement $stmt */
        $stmt->execute([
            $encuesta->getTitulo(),
            $encuesta->getDescripcion(),
            $encuesta->isActiva() ? 1 : 0,
            $encuesta->getId(),
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM encuesta WHERE id = ?");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$id]);
    }

    /**
     * @param array<int, string> $respuestas
     */
    public function saveRespuestasBatch(int $encuestaId, string $sesionToken, ?string $tokenPaciente, array $respuestas): void
    {
        $preguntas = $this->findPreguntasByEncuestaId($encuestaId);

        $this->pdo->beginTransaction();
        try {
            foreach ($preguntas as $i => $pregunta) {
                $valorRaw = $respuestas[$i] ?? '';

                $valorOpcion = null;
                $valorTexto = null;
                $valorNumerico = null;

                if ($pregunta->getTipo()->value() === 'escala') {
                    $valorNumerico = $valorRaw !== '' ? (int) $valorRaw : null;
                } elseif ($pregunta->getTipo()->value() === 'multiple_choice') {
                    $opciones = $pregunta->getOpciones() ?? [];
                    $idx = array_search($valorRaw, $opciones, true);
                    $valorOpcion = $idx !== false ? $idx : null;
                } else {
                    $valorTexto = $valorRaw !== '' ? $valorRaw : null;
                }

                $respuesta = new Respuesta(
                    id: null,
                    sesionToken: $sesionToken,
                    encuestaId: $encuestaId,
                    preguntaId: $pregunta->getId() ?? throw new \RuntimeException('Pregunta sin ID'),
                    tokenPaciente: $tokenPaciente,
                    valorOpcion: $valorOpcion,
                    valorTexto: $valorTexto,
                    valorNumerico: $valorNumerico,
                );

                $this->saveRespuesta($respuesta);
            }
            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * @return list<array{encuesta: Encuesta, preguntas: list<Pregunta>}>
     */
    public function findEncuestasConPreguntas(bool $soloActivas = false): array
    {
        $encuestas = $this->findAll($soloActivas);
        /** @var list<array{encuesta: Encuesta, preguntas: list<Pregunta>}> $result */
        $result = [];
        foreach ($encuestas as $encuesta) {
            $preguntas = $this->findPreguntasByEncuestaId($encuesta->getId() ?? throw new \RuntimeException('Encuesta sin ID'));
            $result[] = [
                'encuesta' => $encuesta,
                'preguntas' => array_values($preguntas),
            ];
        }
        return $result;
    }

    /**
     * @return array{stats: list<array<string, mixed>>, totalRespuestas: int}
     */
    public function findRespuestasAgrupadas(int $encuestaId): array
    {
        $preguntas = $this->findPreguntasByEncuestaId($encuestaId);
        $respuestas = $this->findRespuestasByEncuestaId($encuestaId);

        $stats = [];
        $sesiones = [];

        foreach ($respuestas as $r) {
            $sesiones[$r->getSesionToken()] = true;
        }

        foreach ($preguntas as $p) {
            $tipo = $p->getTipo()->value();
            $s = [
                'tipo' => $tipo === 'texto_libre' ? 'texto' : $tipo,
                'texto' => $p->getTexto(),
                'datos' => [],
            ];

            $pRespuestas = array_filter($respuestas, fn(Respuesta $r) => $r->getPreguntaId() === $p->getId());

            if ($tipo === 'multiple_choice') {
                $opciones = $p->getOpciones() ?? [];
                $conteo = array_fill(0, count($opciones), 0);
                foreach ($pRespuestas as $r) {
                    $idx = $r->getValorOpcion();
                    if ($idx !== null && isset($conteo[$idx])) {
                        $conteo[$idx]++;
                    }
                }
                $s['datos'] = [];
                foreach ($opciones as $idx => $opt) {
                    $s['datos'][$opt] = $conteo[$idx];
                }
            } elseif ($tipo === 'escala') {
                $conteo = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
                $suma = 0;
                $total = 0;
                foreach ($pRespuestas as $r) {
                    $val = $r->getValorNumerico();
                    if ($val !== null && $val >= 1 && $val <= 5) {
                        $conteo[$val]++;
                        $suma += $val;
                        $total++;
                    }
                }
                $s['datos'] = $conteo;
                $s['promedio'] = $total > 0 ? round($suma / $total, 1) : 0;
            } else {
                $textos = [];
                foreach ($pRespuestas as $r) {
                    $txt = $r->getValorTexto();
                    if ($txt !== null && trim($txt) !== '') {
                        $textos[] = $txt;
                    }
                }
                $s['datos'] = $textos;
            }

            $stats[] = $s;
        }

        return [
            'stats' => $stats,
            'totalRespuestas' => count($sesiones),
        ];
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): Encuesta
    {
        /** @var int $id */
        $id = $row['id'];
        /** @var string $titulo */
        $titulo = $row['titulo'];
        /** @var int $creadaPor */
        $creadaPor = $row['creada_por'];
        /** @var string|null $descripcion */
        $descripcion = $row['descripcion'];
        /** @var bool $activa */
        $activa = (bool) $row['activa'];
        /** @var string|null $createdAt */
        $createdAt = $row['created_at'];
        /** @var string|null $updatedAt */
        $updatedAt = $row['updated_at'];

        return new Encuesta(
            id: $id,
            titulo: $titulo,
            creadaPor: $creadaPor,
            descripcion: $descripcion,
            activa: $activa,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    /** @param array<string, mixed> $row */
    private function hydratePregunta(array $row): Pregunta
    {
        /** @var string $opcionesJson */
        $opcionesJson = $row['opciones'];
        /** @var list<string>|null $opciones */
        $opciones = $opcionesJson ? json_decode($opcionesJson, true) : null;

        /** @var int $id */
        $id = $row['id'];
        /** @var int $encuestaId */
        $encuestaId = $row['encuesta_id'];
        /** @var string $tipo */
        $tipo = $row['tipo'];
        /** @var string $texto */
        $texto = $row['texto'];
        /** @var int $orden */
        $orden = $row['orden'];
        /** @var bool $requerida */
        $requerida = (bool) $row['requerida'];
        /** @var string|null $createdAt */
        $createdAt = $row['created_at'];

        return new Pregunta(
            id: $id,
            encuestaId: $encuestaId,
            tipo: new TipoPregunta($tipo),
            texto: $texto,
            orden: $orden,
            opciones: $opciones,
            requerida: $requerida,
            createdAt: $createdAt,
        );
    }

    /** @param array<string, mixed> $row */
    private function hydrateRespuesta(array $row): Respuesta
    {
        /** @var int $id */
        $id = $row['id'];
        /** @var string $sesionToken */
        $sesionToken = $row['sesion_token'];
        /** @var int $encuestaId */
        $encuestaId = $row['encuesta_id'];
        /** @var int $preguntaId */
        $preguntaId = $row['pregunta_id'];
        /** @var string|null $tokenPaciente */
        $tokenPaciente = $row['token_paciente'];
        /** @var string|null $valorOpcionRaw */
        $valorOpcionRaw = $row['valor_opcion'];
        /** @var int|null $valorOpcion */
        $valorOpcion = $valorOpcionRaw !== null ? (int) $valorOpcionRaw : null;
        /** @var string|null $valorTexto */
        $valorTexto = $row['valor_texto'];
        /** @var string|null $valorNumericoRaw */
        $valorNumericoRaw = $row['valor_numerico'];
        /** @var int|null $valorNumerico */
        $valorNumerico = $valorNumericoRaw !== null ? (int) $valorNumericoRaw : null;
        /** @var string|null $createdAt */
        $createdAt = $row['created_at'];

        return new Respuesta(
            id: $id,
            sesionToken: $sesionToken,
            encuestaId: $encuestaId,
            preguntaId: $preguntaId,
            tokenPaciente: $tokenPaciente,
            valorOpcion: $valorOpcion,
            valorTexto: $valorTexto,
            valorNumerico: $valorNumerico,
            createdAt: $createdAt,
        );
    }
}
