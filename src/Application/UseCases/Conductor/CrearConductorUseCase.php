<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Conductor;

use Elyra\Domain\Entity\Funcionario;
use Elyra\Domain\Repository\ConductorRepositoryInterface;
use Elyra\Domain\ValueObject\RolUsuario;

final class CrearConductorUseCase
{
    public function __construct(
        private ConductorRepositoryInterface $conductorRepo,
    ) {
    }

    /**
     * @param array{
     *     nombre: string,
     *     apellido: string,
     *     username: string,
     *     password: string,
     *     email?: string,
     *     documentoIdentidad?: string,
     *     licencia?: string,
     *     telefono?: string,
     * } $input
     *
     * @return array{success: bool, conductorId: int}
     */
    public function execute(array $input): array
    {
        $nombre = trim($input['nombre']);
        $apellido = trim($input['apellido']);
        $username = trim($input['username']);
        $password = $input['password'];

        if (strlen($nombre) < 2) {
            throw new \InvalidArgumentException('El nombre debe tener al menos 2 caracteres.');
        }
        if (strlen($apellido) < 2) {
            throw new \InvalidArgumentException('El apellido debe tener al menos 2 caracteres.');
        }
        if (strlen($username) < 3) {
            throw new \InvalidArgumentException('El username debe tener al menos 3 caracteres.');
        }
        if (strlen($password) < 6) {
            throw new \InvalidArgumentException('La contraseña debe tener al menos 6 caracteres.');
        }

        if (!empty($input['email']) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('El email no es válido.');
        }

        if (!empty($input['documentoIdentidad']) && preg_match('/^\d{8}$/', $input['documentoIdentidad']) !== 1) {
            throw new \InvalidArgumentException('La cédula de identidad debe tener 8 dígitos.');
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $conductor = new Funcionario(
            id: null,
            nombre: $nombre,
            apellido: $apellido,
            rol: new RolUsuario('conductor'),
            username: $username,
            passwordHash: $passwordHash,
            email: $input['email'] ?? null,
            documentoIdentidad: $input['documentoIdentidad'] ?? null,
            licencia: $input['licencia'] ?? null,
            telefono: $input['telefono'] ?? null,
            activo: true,
        );

        $saved = $this->conductorRepo->save($conductor);

        return ['success' => true, 'conductorId' => $saved->getId() ?? 0];
    }
}
