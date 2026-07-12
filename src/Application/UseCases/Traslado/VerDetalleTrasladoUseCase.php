<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Traslado;

use Elyra\Domain\Repository\TrasladoRepositoryInterface;

final class VerDetalleTrasladoUseCase
{
    public function __construct(
        private TrasladoRepositoryInterface $trasladoRepo,
    ) {
    }

    /**
     * @param array{id: int} $input
     *
     * @return array{traslado: \Elyra\Domain\Entity\Traslado, elementos: list<\Elyra\Domain\Entity\ElementoTraslado>, historial: list<\Elyra\Domain\Entity\HistorialEstado>}|null
     */
    public function execute(array $input): ?array
    {
        $id = $input['id'];
        if ($id <= 0) {
            return null;
        }

        $traslado = $this->trasladoRepo->findById($id);
        if ($traslado === null) {
            return null;
        }

        /** @var list<\Elyra\Domain\Entity\ElementoTraslado> $elementos */
        $elementos = array_values($this->trasladoRepo->findElementosByTrasladoId($id));
        /** @var list<\Elyra\Domain\Entity\HistorialEstado> $historial */
        $historial = array_values($this->trasladoRepo->findHistorialByTrasladoId($id));

        return [
            'traslado' => $traslado,
            'elementos' => $elementos,
            'historial' => $historial,
        ];
    }
}
