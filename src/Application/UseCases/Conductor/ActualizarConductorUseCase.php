<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Conductor;

use Elyra\Domain\Entity\Funcionario;
use Elyra\Domain\Repository\ConductorRepositoryInterface;
use Elyra\Domain\ValueObject\RolUsuario;

final class ActualizarConductorUseCase
{
    public function __construct(
        private ConductorRepositoryInterface $conductorRepo,
    ) {
    }

    /**
     * @param array{
     *     id: int,
     *     nombre?: string,
     *     apellido?: string,
     *     email?: string,
     *     licencia?: string,
     *     telefono?: string,
     *     activo?: bool,
     * } $input
     */
    public function execute(array $input): void
    {
        $conductor = $this->conductorRepo->findById($input['id']);
        if ($conductor === null) {
            throw new \DomainException('Conductor no encontrado.');
        }

        if (isset($input['email']) && $input['email'] !== '' && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('El email no es válido.');
        }

        $updated = new Funcionario(
            id: $conductor->getId(),
            nombre: $input['nombre'] ?? $conductor->getNombre(),
            apellido: $input['apellido'] ?? $conductor->getApellido(),
            rol: $conductor->getRol(),
            username: $conductor->getUsername(),
            passwordHash: $conductor->getPasswordHash(),
            email: $input['email'] ?? $conductor->getEmail(),
            documentoIdentidad: $conductor->getDocumentoIdentidad(),
            licencia: $input['licencia'] ?? $conductor->getLicencia(),
            telefono: $input['telefono'] ?? $conductor->getTelefono(),
            activo: $input['activo'] ?? $conductor->isActivo(),
            foto: $conductor->getFoto(),
            createdAt: $conductor->getCreatedAt(),
        );

        $this->conductorRepo->update($updated);
    }
}
