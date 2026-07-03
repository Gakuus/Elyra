<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

class AuthController extends BaseController
{
    public function login(): void
    {
        if (isset($_SESSION['user'])) {
            $this->redirect('/dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            if ($username === 'admin' && $password === 'admin') {
                $_SESSION['user'] = ['username' => $username, 'nombre' => 'Administrador'];
                $this->redirect('/dashboard');
            }

            $this->render('auth/login', ['error' => 'Credenciales inválidas']);
            return;
        }

        $this->render('auth/login');
    }

    public function logout(): void
    {
        session_destroy();
        $this->redirect('/');
    }
}
