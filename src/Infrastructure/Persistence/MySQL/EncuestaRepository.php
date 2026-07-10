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
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return $this->hydrate($row);
    }

    public function findPreguntasByEncuestaId(int $encuestaId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM pregunta WHERE encuesta_id = ? ORDER BY `orden`");
        $stmt->execute([$encuestaId]);
        $rows = $stmt->fetchAll();

        return array_map(fn (array $row) => $this->hydratePregunta($row), $rows);
    }

    public function findRespuestasByEncuestaId(int $encuestaId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM respuesta WHERE encuesta_id = ? ORDER BY created_at");
        $stmt->execute([$encuestaId]);
        $rows = $stmt->fetchAll();

        return array_map(fn (array $row) => $this->hydrateRespuesta($row), $rows);
    }

    public function findRespuestasByPreguntaId(int $preguntaId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM respuesta WHERE pregunta_id = ?");
        $stmt->execute([$preguntaId]);
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
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    public function countTotal(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM encuesta");
        return (int) $stmt->fetchColumn();
    }

    public function countActivas(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM encuesta WHERE activa = 1");
        return (int) $stmt->fetchColumn();
    }

    public function save(Encuesta $encuesta): Encuesta
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO encuesta (titulo, descripcion, activa, creada_por) VALUES (?, ?, ?, ?)"
        );
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
        $stmt->execute([$id]);
    }

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

    public function findEncuestasConPreguntas(bool $soloActivas = false): array
    {
        $encuestas = $this->findAll($soloActivas);
        $result = [];
        foreach ($encuestas as $encuesta) {
            $preguntas = $this->findPreguntasByEncuestaId($encuesta->getId() ?? throw new \RuntimeException('Encuesta sin ID'));
            $result[] = [
                'encuesta' => $encuesta,
                'preguntas' => $preguntas,
            ];
        }
        return $result;
    }

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

    private function hydrate(array $row): Encuesta
    {
        return new Encuesta(
            id: (int) $row['id'],
            titulo: $row['titulo'],
            creadaPor: (int) $row['creada_por'],
            descripcion: $row['descripcion'],
            activa: (bool) $row['activa'],
            createdAt: $row['created_at'],
            updatedAt: $row['updated_at'],
        );
    }

    private function hydratePregunta(array $row): Pregunta
    {
        /** @var list<string>|null $opciones */
        $opciones = $row['opciones'] ? json_decode($row['opciones'], true) : null;

        return new Pregunta(
            id: (int) $row['id'],
            encuestaId: (int) $row['encuesta_id'],
            tipo: new TipoPregunta($row['tipo']),
            texto: $row['texto'],
            orden: (int) $row['orden'],
            opciones: $opciones,
            requerida: (bool) $row['requerida'],
            createdAt: $row['created_at'],
        );
    }

    private function hydrateRespuesta(array $row): Respuesta
    {
        return new Respuesta(
            id: (int) $row['id'],
            sesionToken: $row['sesion_token'],
            encuestaId: (int) $row['encuesta_id'],
            preguntaId: (int) $row['pregunta_id'],
            tokenPaciente: $row['token_paciente'],
            valorOpcion: $row['valor_opcion'] !== null ? (int) $row['valor_opcion'] : null,
            valorTexto: $row['valor_texto'],
            valorNumerico: $row['valor_numerico'] !== null ? (int) $row['valor_numerico'] : null,
            createdAt: $row['created_at'],
        );
    }
}
