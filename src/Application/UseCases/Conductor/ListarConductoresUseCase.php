<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Conductor;

use Elyra\Domain\Entity\Funcionario;
use Elyra\Domain\Repository\ConductorRepositoryInterface;

final class ListarConductoresUseCase
{
    public function __construct(
        private ConductorRepositoryInterface $conductorRepo,
    ) {
    }

    /**
     * @param array{activo?: bool|null, buscar?: string} $input
     *
     * @return array{conductores: list<Funcionario>, total: int, activos: int}
     */
    public function execute(array $input): array
    {
        $activo = $input['activo'] ?? null;
        $buscar = trim($input['buscar'] ?? '');
        /** @var list<\Elyra\Domain\Entity\Funcionario> $conductores */
        $conductores = array_values($this->conductorRepo->findAll($activo, $buscar));
        $total = $this->conductorRepo->countTotal();
        $activos = $this->conductorRepo->countActivos();

        return [
            'conductores' => $conductores,
            'total' => $total,
            'activos' => $activos,
        ];
    }
}
