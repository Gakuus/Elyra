<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Infrastructure\Persistence\MySQL\UsuarioRepository;
use Elyra\Infrastructure\Service\AuthService;

class AuthController extends BaseController
{
    private AuthService $authService;

    public function __construct()
    {
        $usuarioRepo = new UsuarioRepository();
        $this->authService = new AuthService($usuarioRepo);
    }

    public function login(): void
    {
        if ($this->authService->isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);

        $this->render('auth/login', ['error' => $error]);
    }

    public function doLogin(): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $this->render('auth/login', ['error' => 'Ingrese usuario y contraseña']);
            return;
        }

        $result = $this->authService->login($username, $password);

        if ($result['success']) {
            $this->redirect('/dashboard');
        }

        $this->render('auth/login', ['error' => $result['error']]);
    }

    public function logout(): void
    {
        $this->authService->logout();
        $this->redirect('/login');
    }
}
