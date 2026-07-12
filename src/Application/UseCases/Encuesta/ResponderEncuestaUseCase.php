<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Encuesta;

use Elyra\Domain\Entity\Respuesta;
use Elyra\Domain\Repository\EncuestaRepositoryInterface;

final class ResponderEncuestaUseCase
{
    public function __construct(
        private EncuestaRepositoryInterface $encuestaRepo,
    ) {
    }

    /**
     * @param array{
     *     encuestaId: int,
     *     sesionToken: string,
     *     respuestas: list<array{preguntaId: int, valorOpcion?: int, valorTexto?: string, valorNumerico?: int}>,
     *     tokenPaciente?: string,
     * } $input
     *
     * @return array{success: bool, guardadas: int}
     */
    public function execute(array $input): array
    {
        $encuesta = $this->encuestaRepo->findById($input['encuestaId']);
        if ($encuesta === null) {
            throw new \DomainException('Encuesta no encontrada.');
        }

        if (!$encuesta->isActiva()) {
            throw new \DomainException('Esta encuesta no está activa.');
        }

        $preguntas = $this->encuestaRepo->findPreguntasByEncuestaId($input['encuestaId']);
        $preguntaIds = array_map(fn($p) => $p->getId(), $preguntas);
        $requeridas = [];
        foreach ($preguntas as $p) {
            if ($p->isRequerida() && $p->getId() !== null) {
                $requeridas[$p->getId()] = true;
            }
        }

        $respondidas = [];
        $guardadas = 0;

        foreach ($input['respuestas'] as $r) {
            $preguntaId = $r['preguntaId'];
            if (!in_array($preguntaId, $preguntaIds, true)) {
                continue;
            }

            unset($requeridas[$preguntaId]);
            $respondidas[$preguntaId] = true;

            $respuesta = new Respuesta(
                id: null,
                sesionToken: $input['sesionToken'],
                encuestaId: $input['encuestaId'],
                preguntaId: $preguntaId,
                tokenPaciente: $input['tokenPaciente'] ?? null,
                valorOpcion: $r['valorOpcion'] ?? null,
                valorTexto: $r['valorTexto'] ?? null,
                valorNumerico: $r['valorNumerico'] ?? null,
            );

            $this->encuestaRepo->saveRespuesta($respuesta);
            $guardadas++;
        }

        if (!empty($requeridas)) {
            throw new \InvalidArgumentException('Faltan preguntas requeridas por responder.');
        }

        return ['success' => true, 'guardadas' => $guardadas];
    }
}
