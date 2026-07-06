<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Service;

use Elyra\Domain\Repository\UsuarioRepositoryInterface;

class AuthService
{
    private UsuarioRepositoryInterface $usuarioRepo;

    public function __construct(UsuarioRepositoryInterface $usuarioRepo)
    {
        $this->usuarioRepo = $usuarioRepo;
    }

    public function login(string $username, string $password): array
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        if (!RateLimiter::checkLoginAttempts($ip)) {
            return [
                'success' => false,
                'error' => 'Demasiados intentos. Intente de nuevo en 15 minutos.',
            ];
        }

        RateLimiter::incrementLoginAttempts($ip);

        $funcionario = $this->usuarioRepo->findFuncionarioByUsername($username);

        if ($funcionario === null) {
            return [
                'success' => false,
                'error' => 'Credenciales inválidas',
            ];
        }

        if (!$funcionario->isActivo()) {
            return [
                'success' => false,
                'error' => 'Usuario desactivado. Contacte al administrador.',
            ];
        }

        if (!$funcionario->verificarPassword($password)) {
            return [
                'success' => false,
                'error' => 'Credenciales inválidas',
            ];
        }

        RateLimiter::resetLoginAttempts($ip);

        SessionManager::login($funcionario->getId(), $funcionario->getRol()->value());

        return [
            'success' => true,
            'user' => $funcionario,
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
