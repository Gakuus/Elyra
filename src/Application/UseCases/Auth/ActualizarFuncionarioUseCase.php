<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Auth;

use Elyra\Domain\Repository\UsuarioRepositoryInterface;
use Elyra\Domain\ValueObject\RolUsuario;

final class ActualizarFuncionarioUseCase
{
    public function __construct(
        private UsuarioRepositoryInterface $usuarioRepo,
    ) {
    }

    /**
     * @param array{
     *     id: int,
     *     nombre: string,
     *     apellido: string,
     *     username: string,
     *     rol: string,
     *     email?: string,
     *     documentoIdentidad?: string,
     *     licencia?: string,
     *     licenciaConducir?: string,
     *     telefono?: string,
     *     password?: string,
     * } $input
     *
     * @return array{success: bool}
     */
    public function execute(array $input): array
    {
        $id = $input['id'];
        $nombre = trim($input['nombre']);
        $apellido = trim($input['apellido']);
        $username = trim($input['username']);
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

        if (!empty($input['email']) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('El email no es válido.');
        }

        if (!empty($input['documentoIdentidad']) && preg_match('/^\d{8}$/', $input['documentoIdentidad']) !== 1) {
            throw new \InvalidArgumentException('La cédula de identidad debe tener 8 dígitos.');
        }

        $existing = $this->usuarioRepo->findById($id);
        if ($existing === null || $existing->getTipo() !== 'funcionario') {
            throw new \InvalidArgumentException('Funcionario no encontrado.');
        }

        /** @var \Elyra\Domain\Entity\Funcionario $funcionario */
        $funcionario = $existing;

        $dupUsername = $this->usuarioRepo->findFuncionarioByUsername($username);
        if ($dupUsername !== null && $dupUsername->getId() !== $id) {
            throw new \InvalidArgumentException('El nombre de usuario ya está registrado.');
        }

        if (!empty($input['email'])) {
            $dupEmail = $this->usuarioRepo->findFuncionarioByEmail($input['email']);
            if ($dupEmail !== null && $dupEmail->getId() !== $id) {
                throw new \InvalidArgumentException('El email ya está registrado.');
            }
        }

        $funcionario->setUsername($username);
        $funcionario->setTelefono($input['telefono'] ?? null);

        $this->usuarioRepo->updateFuncionario($funcionario);

        if (!empty($input['password'])) {
            if (strlen($input['password']) < 6) {
                throw new \InvalidArgumentException('La contraseña debe tener al menos 6 caracteres.');
            }
            $passwordHash = password_hash($input['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $this->usuarioRepo->updatePasswordHash($id, $passwordHash);
        }

        return ['success' => true];
    }
}
