<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Encuesta;

use Elyra\Domain\Entity\Encuesta;
use Elyra\Domain\Entity\Pregunta;
use Elyra\Domain\Repository\EncuestaRepositoryInterface;
use Elyra\Domain\ValueObject\TipoPregunta;

final class CrearEncuestaUseCase
{
    public function __construct(
        private EncuestaRepositoryInterface $encuestaRepo,
    ) {
    }

    /**
     * @param array{
     *     titulo: string,
     *     creadaPor: int,
     *     descripcion?: string,
     *     preguntas: list<array{tipo: string, texto: string, opciones?: list<string>, requerida?: bool}>,
     * } $input
     *
     * @return array{success: bool, encuestaId: int}
     */
    public function execute(array $input): array
    {
        $titulo = trim($input['titulo']);
        if (strlen($titulo) < 3 || strlen($titulo) > 200) {
            throw new \InvalidArgumentException('El título debe tener entre 3 y 200 caracteres.');
        }

        $preguntasInput = $input['preguntas'];
        if (count($preguntasInput) < 1) {
            throw new \InvalidArgumentException('Agregá al menos una pregunta.');
        }

        $tiposValidos = ['multiple_choice', 'escala', 'texto', 'texto_libre'];
        $preguntasData = [];

        foreach ($preguntasInput as $i => $p) {
            $texto = trim($p['texto']);
            $tipo = trim($p['tipo']);

            if (strlen($texto) < 3) {
                throw new \InvalidArgumentException('La pregunta ' . ($i + 1) . ' debe tener al menos 3 caracteres.');
            }

            if ($tipo === 'texto') {
                $tipo = 'texto_libre';
            }

            if (!in_array($tipo, $tiposValidos, true)) {
                throw new \InvalidArgumentException('Tipo inválido en la pregunta ' . ($i + 1) . '.');
            }

            $opciones = null;
            if ($tipo === 'multiple_choice') {
                $opciones = array_values(array_filter(array_map('trim', $p['opciones'] ?? [])));
                if (count($opciones) < 2) {
                    throw new \InvalidArgumentException('La pregunta ' . ($i + 1) . ' necesita al menos 2 opciones.');
                }
            }

            $preguntasData[] = [
                'tipo' => $tipo,
                'texto' => $texto,
                'opciones' => $opciones,
                'requerida' => $p['requerida'] ?? true,
            ];
        }

        $encuesta = new Encuesta(
            id: null,
            titulo: $titulo,
            creadaPor: $input['creadaPor'],
            descripcion: $input['descripcion'] ?? null,
            activa: true,
        );

        $saved = $this->encuestaRepo->save($encuesta);
        $encuestaId = $saved->getId();

        foreach ($preguntasData as $orden => $pd) {
            $pregunta = new Pregunta(
                id: null,
                encuestaId: $encuestaId ?? 0,
                tipo: new TipoPregunta($pd['tipo']),
                texto: $pd['texto'],
                orden: $orden,
                opciones: $pd['opciones'],
                requerida: $pd['requerida'],
            );
            $this->encuestaRepo->savePregunta($pregunta);
        }

        return ['success' => true, 'encuestaId' => $encuestaId ?? 0];
    }
}
