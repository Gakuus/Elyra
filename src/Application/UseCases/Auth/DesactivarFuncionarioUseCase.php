<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Auth;

use Elyra\Domain\Repository\UsuarioRepositoryInterface;

final class DesactivarFuncionarioUseCase
{
    public function __construct(
        private UsuarioRepositoryInterface $usuarioRepo,
    ) {
    }

    /**
     * @param array{id: int} $input
     *
     * @return array{success: bool}
     */
    public function execute(array $input): array
    {
        $id = $input['id'];

        $existing = $this->usuarioRepo->findById($id);
        if ($existing === null || $existing->getTipo() !== 'funcionario') {
            throw new \InvalidArgumentException('Funcionario no encontrado.');
        }

        /** @var \Elyra\Domain\Entity\Funcionario $funcionario */
        $funcionario = $existing;

        if (!$funcionario->isActivo()) {
            throw new \InvalidArgumentException('El funcionario ya está desactivado.');
        }

        $funcionario->setActivo(false);
        $this->usuarioRepo->updateFuncionario($funcionario);

        return ['success' => true];
    }
}
