<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Auth;

use Elyra\Domain\Entity\Funcionario;
use Elyra\Domain\Repository\UsuarioRepositoryInterface;
use Elyra\Domain\ValueObject\RolUsuario;

final class CrearFuncionarioUseCase
{
    public function __construct(
        private UsuarioRepositoryInterface $usuarioRepo,
    ) {
    }

    /**
     * @param array{
     *     nombre: string,
     *     apellido: string,
     *     username: string,
     *     password?: string,
     *     rol: string,
     *     email?: string,
     *     documentoIdentidad?: string,
     *     licencia?: string,
     *     licenciaConducir?: string,
     *     telefono?: string,
     * } $input
     *
     * @return array{success: bool, funcionarioId: int}
     */
    public function execute(array $input): array
    {
        $nombre = trim($input['nombre']);
        $apellido = trim($input['apellido']);
        $username = trim($input['username']);
        $password = trim($input['password'] ?? '');
        $rol = trim($input['rol']);

        if (strlen($nombre) < 2) {
            throw new \InvalidArgumentException('El nombre debe tener al menos 2 caracteres.');
        }
        if (strlen($apellido) < 2) {
            throw new \InvalidArgumentException('El apellido debe tener al menos 2 caracteres.');
        }
        if (strlen($username) < 3) {
            throw new \InvalidArgumentException('El usuario debe tener al menos 3 caracteres.');
        }

        $rolVo = new RolUsuario($rol);
        $esAdmin = in_array($rol, ['admin', 'superadmin'], true);

        if ($esAdmin) {
            if ($password === '') {
                throw new \InvalidArgumentException('La contraseña es obligatoria para admins y superadmins.');
            }
            if (strlen($password) < 6) {
                throw new \InvalidArgumentException('La contraseña debe tener al menos 6 caracteres.');
            }
        } else {
            $documento = $input['documentoIdentidad'] ?? '';
            if ($password === '') {
                if ($documento === '' || preg_match('/^\d{8}$/', $documento) !== 1) {
                    throw new \InvalidArgumentException('La cédula es obligatoria (8 dígitos) para usar como contraseña por defecto.');
                }
                $password = $documento;
            } elseif (strlen($password) < 6) {
                throw new \InvalidArgumentException('La contraseña debe tener al menos 6 caracteres.');
            }
        }

        if (!empty($input['email']) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('El email no es válido.');
        }

        if (!empty($input['documentoIdentidad']) && preg_match('/^\d{8}$/', $input['documentoIdentidad']) !== 1) {
            throw new \InvalidArgumentException('La cédula de identidad debe tener 8 dígitos.');
        }

        if ($this->usuarioRepo->findFuncionarioByUsername($username) !== null) {
            throw new \InvalidArgumentException('El nombre de usuario ya está registrado.');
        }

        if (!empty($input['email']) && $this->usuarioRepo->findFuncionarioByEmail($input['email']) !== null) {
            throw new \InvalidArgumentException('El email ya está registrado.');
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $funcionario = new Funcionario(
            id: null,
            nombre: $nombre,
            apellido: $apellido,
            rol: $rolVo,
            username: $username,
            passwordHash: $passwordHash,
            email: $input['email'] ?? null,
            documentoIdentidad: $input['documentoIdentidad'] ?? null,
            licencia: $input['licencia'] ?? null,
            licenciaConducir: $input['licenciaConducir'] ?? null,
            telefono: $input['telefono'] ?? null,
            activo: true,
        );

        $saved = $this->usuarioRepo->saveFuncionario($funcionario);

        return ['success' => true, 'funcionarioId' => $saved->getId() ?? 0];
    }
}
