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

        if (method_exists($user, 'isActivo') && !$user->isActivo()) {
            return [
                'success' => false,
                'error' => 'Usuario desactivado. Contacte al administrador.',
            ];
        }

        if (!method_exists($user, 'verificarPassword') || !$user->verificarPassword($password)) {
            return [
                'success' => false,
                'error' => 'Credenciales inválidas',
            ];
        }

        RateLimiter::resetLoginAttempts($ip);

        $rol = $user instanceof \Elyra\Domain\Entity\Funcionario ? $user->getRol()->value() : 'paciente';
        SessionManager::login($user->getId(), $rol, $user->getNombreCompleto());

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
