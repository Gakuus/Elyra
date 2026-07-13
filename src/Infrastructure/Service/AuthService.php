<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Service;

use Elyra\Domain\Entity\Funcionario;
use Elyra\Domain\Entity\Paciente;
use Elyra\Domain\Repository\UsuarioRepositoryInterface;

class AuthService
{
    private UsuarioRepositoryInterface $usuarioRepo;

    public function __construct(UsuarioRepositoryInterface $usuarioRepo)
    {
        $this->usuarioRepo = $usuarioRepo;
    }

    /**
     * @return array{success: bool, error?: string, user?: mixed}
     */
    public function login(string $username, string $password): array
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (!is_string($ip)) {
            $ip = '127.0.0.1';
        }

        if (!RateLimiter::checkLoginAttempts($ip)) {
            return [
                'success' => false,
                'error' => 'Demasiados intentos. Intente de nuevo en 15 minutos.',
            ];
        }

        RateLimiter::incrementLoginAttempts($ip);

        $user = $this->usuarioRepo->findFuncionarioByUsername($username);

        if ($user === null) {
            $user = $this->usuarioRepo->findPacienteByUsername($username);
        }

        if ($user === null) {
            return [
                'success' => false,
                'error' => 'Credenciales inválidas',
            ];
        }

        if (!$user->isActivo()) {
            return [
                'success' => false,
                'error' => 'Usuario desactivado. Contacte al administrador.',
            ];
        }

        if (!RateLimiter::checkAccountLockout($username)) {
            return [
                'success' => false,
                'error' => 'Demasiados intentos. Intente de nuevo en 15 minutos.',
            ];
        }

        if (!$user->verificarPassword($password)) {
            RateLimiter::incrementAccountAttempts($username);
            return [
                'success' => false,
                'error' => 'Credenciales inválidas',
            ];
        }

        $hash = $user->getPasswordHash();
        if ($hash !== null && password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12])) {
            $user->setPasswordHash(password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]));
            if ($user instanceof Funcionario) {
                $this->usuarioRepo->updateFuncionario($user);
            } elseif ($user instanceof Paciente) {
                $this->usuarioRepo->updatePaciente($user);
            }
        }

        RateLimiter::resetLoginAttempts($ip);
        RateLimiter::resetAccountAttempts($username);

        $rol = $user instanceof Funcionario ? $user->getRol()->value() : 'paciente';
        $userId = $user->getId();
        if ($userId === null) {
            return ['success' => false, 'error' => 'Error interno'];
        }
        SessionManager::login($userId, $rol, $user->getNombreCompleto());

        return [
            'success' => true,
            'user' => $user,
        ];
    }

    public function logout(): void
    {
        SessionManager::logout();
    }

    public function isAuthenticated(): bool
    {
        return SessionManager::isAuthenticated();
    }

    public function getCurrentUserId(): ?int
    {
        return SessionManager::getUserId();
    }

    public function getCurrentUserRole(): ?string
    {
        return SessionManager::getUserRole();
    }

    public function requireRole(string ...$roles): bool
    {
        $userRole = SessionManager::getUserRole();
        if ($userRole === null) {
            return false;
        }
        return in_array($userRole, $roles, true);
    }
}
