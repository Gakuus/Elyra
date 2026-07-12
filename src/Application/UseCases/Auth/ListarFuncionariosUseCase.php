<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Auth;

use Elyra\Domain\Entity\Funcionario;
use Elyra\Domain\Repository\UsuarioRepositoryInterface;

final class ListarFuncionariosUseCase
{
    public function __construct(
        private UsuarioRepositoryInterface $usuarioRepo,
    ) {
    }

    /**
     * @param array{activo?: bool|null, buscar?: string} $input
     *
     * @return array{funcionarios: list<Funcionario>, total: int, activos: int, inactivos: int}
     */
    public function execute(array $input): array
    {
        $activo = $input['activo'] ?? null;
        $buscar = trim($input['buscar'] ?? '');

        /** @var list<Funcionario> $funcionarios */
        $funcionarios = $this->usuarioRepo->findAllFuncionarios($activo);

        if ($buscar !== '') {
            $buscarLower = strtolower($buscar);
            $funcionarios = array_values(array_filter(
                $funcionarios,
                fn(Funcionario $f) =>
                    str_contains(strtolower($f->getNombre()), $buscarLower)
                    || str_contains(strtolower($f->getApellido()), $buscarLower)
                    || str_contains(strtolower($f->getUsername() ?? ''), $buscarLower)
                    || str_contains(strtolower($f->getEmail() ?? ''), $buscarLower)
            ));
        }

        $allFuncionarios = $this->usuarioRepo->findAllFuncionarios();
        $total = count($allFuncionarios);
        $activos = 0;
        foreach ($allFuncionarios as $f) {
            if ($f->isActivo()) {
                $activos++;
            }
        }

        return [
            'funcionarios' => $funcionarios,
            'total' => $total,
            'activos' => $activos,
            'inactivos' => $total - $activos,
        ];
    }
}
