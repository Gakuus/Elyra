<?php

declare(strict_types=1);

namespace Elyra\Domain\Repository;

use Elyra\Domain\Entity\Encuesta;
use Elyra\Domain\Entity\Pregunta;
use Elyra\Domain\Entity\Respuesta;

interface EncuestaRepositoryInterface
{
    public function findById(int $id): ?Encuesta;
    /** @return Pregunta[] */
    public function findPreguntasByEncuestaId(int $encuestaId): array;
    /** @return Respuesta[] */
    public function findRespuestasByEncuestaId(int $encuestaId): array;
    /** @return Respuesta[] */
    public function findRespuestasByPreguntaId(int $preguntaId): array;
    /** @return Encuesta[] */
    public function findAll(bool $soloActivas = false): array;
    public function countTotal(): int;
    public function countActivas(): int;
    public function save(Encuesta $encuesta): Encuesta;
    public function savePregunta(Pregunta $pregunta): Pregunta;
    public function saveRespuesta(Respuesta $respuesta): Respuesta;
    public function update(Encuesta $encuesta): void;
    public function delete(int $id): void;
}
