<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Ruta;

use Elyra\Domain\Entity\Ruta;
use Elyra\Domain\Repository\RutaRepositoryInterface;

final class ListarRutasUseCase
{
    public function __construct(
        private RutaRepositoryInterface $rutaRepo,
    ) {
    }

    /**
     * @return array{rutas: list<Ruta>, total: int}
     */
    public function execute(): array
    {
        /** @var list<\Elyra\Domain\Entity\Ruta> $rutas */
        $rutas = array_values($this->rutaRepo->findAll());
        $total = $this->rutaRepo->countTotal();

        return [
            'rutas' => $rutas,
            'total' => $total,
        ];
    }
}
