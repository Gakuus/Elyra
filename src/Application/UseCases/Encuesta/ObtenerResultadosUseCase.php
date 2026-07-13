<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Encuesta;

use Elyra\Domain\Repository\EncuestaRepositoryInterface;

final class ObtenerResultadosUseCase
{
    public function __construct(
        private EncuestaRepositoryInterface $encuestaRepo,
    ) {
    }

    /**
     * @param array{id: int} $input
     *
     * @return array{encuesta: \Elyra\Domain\Entity\Encuesta, preguntas: list<\Elyra\Domain\Entity\Pregunta>, totalRespuestas: int, stats: list<array{preguntaId: int, texto: string, tipo: string, opciones: list<string>|null, conteo: array<int, int>, textosLibres: list<string>, total: int}>}|null
     */
    public function execute(array $input): ?array
    {
        $encuesta = $this->encuestaRepo->findById($input['id']);
        if ($encuesta === null) {
            return null;
        }

        /** @var list<\Elyra\Domain\Entity\Pregunta> $preguntas */
        $preguntas = array_values($this->encuestaRepo->findPreguntasByEncuestaId($input['id']));
        $respuestas = $this->encuestaRepo->findRespuestasByEncuestaId($input['id']);
        $totalRespuestas = count($respuestas);

        $stats = [];
        foreach ($preguntas as $p) {
            $preguntaId = $p->getId();
            if ($preguntaId === null) {
                continue;
            }

            $respuestasPregunta = $this->encuestaRepo->findRespuestasByPreguntaId($preguntaId);
            $conteo = [];
            $textosLibres = [];

            foreach ($respuestasPregunta as $resp) {
                if ($resp->getValorOpcion() !== null) {
                    $key = (string) $resp->getValorOpcion();
                    $conteo[$key] = ($conteo[$key] ?? 0) + 1;
                } elseif ($resp->getValorTexto() !== null) {
                    $textosLibres[] = $resp->getValorTexto();
                } elseif ($resp->getValorNumerico() !== null) {
                    $key = (string) $resp->getValorNumerico();
                    $conteo[$key] = ($conteo[$key] ?? 0) + 1;
                }
            }

            $stats[] = [
                'preguntaId' => $preguntaId,
                'texto' => $p->getTexto(),
                'tipo' => $p->getTipo()->value(),
                'opciones' => $p->getOpciones(),
                'conteo' => $conteo,
                'textosLibres' => $textosLibres,
                'total' => count($respuestasPregunta),
            ];
        }

        return [
            'encuesta' => $encuesta,
            'preguntas' => $preguntas,
            'totalRespuestas' => $totalRespuestas,
            'stats' => $stats,
        ];
    }
}
