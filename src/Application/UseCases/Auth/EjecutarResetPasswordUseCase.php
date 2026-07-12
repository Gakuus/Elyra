<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Auth;

use Elyra\Domain\Repository\UsuarioRepositoryInterface;
use Elyra\Infrastructure\Service\SessionManager;

final class EjecutarResetPasswordUseCase
{
    public function __construct(
        private UsuarioRepositoryInterface $usuarioRepo,
    ) {
    }

    /**
     * @param array{token: string, password: string} $input
     *
     * @return array{success: bool}
     */
    public function execute(array $input): array
    {
        $token = trim($input['token']);
        $password = $input['password'];

        if ($token === '' || strlen($token) !== 64) {
            throw new \InvalidArgumentException('Token inválido.');
        }

        if (strlen($password) < 8) {
            throw new \InvalidArgumentException('La contraseña debe tener al menos 8 caracteres.');
        }

        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            throw new \InvalidArgumentException('La contraseña debe contener al menos una mayúscula, una minúscula y un número.');
        }

        $tokenHash = hash('sha256', $token);
        $user = $this->usuarioRepo->findUserByResetToken($tokenHash);

        if ($user === null) {
            throw new \InvalidArgumentException('El enlace de recuperación es inválido o ya fue utilizado.');
        }

        $expiresAt = $user->getResetTokenExpiresAt();
        if ($expiresAt !== null && strtotime($expiresAt) < time()) {
            throw new \InvalidArgumentException('El enlace de recuperación expiró. Solicitá uno nuevo.');
        }

        $userId = $user->getId() ?? 0;
        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $this->usuarioRepo->beginTransaction();
        try {
            $this->usuarioRepo->saveResetToken($userId, null, null);
            $this->usuarioRepo->updatePasswordHash($userId, $passwordHash);
            $this->usuarioRepo->commit();
        } catch (\Throwable $e) {
            $this->usuarioRepo->rollback();
            throw new \RuntimeException('Error al actualizar la contraseña.');
        }

        SessionManager::destroyAllSessionsForUser($userId);

        return ['success' => true];
    }
}
