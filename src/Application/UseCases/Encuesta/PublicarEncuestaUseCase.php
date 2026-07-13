<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Encuesta;

use Elyra\Domain\Repository\EncuestaRepositoryInterface;

final class PublicarEncuestaUseCase
{
    public function __construct(
        private EncuestaRepositoryInterface $encuestaRepo,
    ) {
    }

    /**
     * @param array{id: int, activa: bool} $input
     */
    public function execute(array $input): void
    {
        $encuesta = $this->encuestaRepo->findById($input['id']);
        if ($encuesta === null) {
            throw new \DomainException('Encuesta no encontrada.');
        }

        $encuesta->setActiva($input['activa']);
        $this->encuestaRepo->update($encuesta);
    }
}
