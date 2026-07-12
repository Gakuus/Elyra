<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Traslado;

use Elyra\Domain\Entity\Traslado;
use Elyra\Domain\Repository\TrasladoRepositoryInterface;

final class HistorialTrasladosUseCase
{
    public function __construct(
        private TrasladoRepositoryInterface $trasladoRepo,
    ) {
    }

    /**
     * @param array{
     *     estado?: string|null,
     *     conductorId?: int|null,
     *     fechaDesde?: string|null,
     *     fechaHasta?: string|null,
     *     page?: int,
     *     perPage?: int,
     * } $input
     *
     * @return array{traslados: list<Traslado>, total: int, page: int, totalPages: int, conductores: list<array{id: int, nombre: string}>}
     */
    public function execute(array $input): array
    {
        $page = max(1, $input['page'] ?? 1);
        $perPage = min(100, max(1, $input['perPage'] ?? 20));
        $estado = ($input['estado'] ?? '') !== '' ? $input['estado'] : null;
        $conductorId = $input['conductorId'] ?? null;
        $fechaDesde = $input['fechaDesde'] ?? null;
        $fechaHasta = $input['fechaHasta'] ?? null;

        /** @var list<\Elyra\Domain\Entity\Traslado> $traslados */
        $traslados = array_values($this->trasladoRepo->findAll($estado, $conductorId, $fechaDesde, $fechaHasta, $page, $perPage));
        $total = $this->trasladoRepo->count($estado, $conductorId);
        $totalPages = max(1, (int) ceil($total / $perPage));

        $conductores = [];
        /** @var list<\Elyra\Domain\Entity\Traslado> $allConductores */
        $allConductores = array_values($this->trasladoRepo->findAll(null, null, null, null, 1, 1000));
        $seen = [];
        foreach ($allConductores as $t) {
            $cid = $t->getConductorId();
            if (!isset($seen[$cid])) {
                $seen[$cid] = true;
                $conductores[] = ['id' => $cid, 'nombre' => 'Conductor #' . $cid];
            }
        }

        return [
            'traslados' => $traslados,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'conductores' => $conductores,
        ];
    }
}
