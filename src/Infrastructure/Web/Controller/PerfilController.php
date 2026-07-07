<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Infrastructure\Persistence\MySQL\UsuarioRepository;
use Elyra\Infrastructure\Service\SessionManager;

class PerfilController extends BaseController
{
    private UsuarioRepository $usuarioRepo;

    public function __construct()
    {
        $this->usuarioRepo = new UsuarioRepository();
    }

    public function index(): void
    {
        $this->requireAuth();

        $userId = SessionManager::getUserId();
        $user = $this->usuarioRepo->findById($userId);

        $this->render('perfil/index', [
            'user' => $user,
        ]);
    }

    public function actualizar(): void
    {
        $this->requireAuth();

        $userId = SessionManager::getUserId();
        $user = $this->usuarioRepo->findById($userId);

        if (!$user) {
            $this->redirect('/perfil');
            return;
        }

        $telefono = trim($_POST['telefono'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($telefono !== '' && !preg_match('/^[0-9]{8,9}$/', $telefono)) {
            $this->render('perfil/index', ['error' => 'El teléfono debe tener 8 o 9 dígitos.', 'user' => $user]);
            return;
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->render('perfil/index', ['error' => 'Email inválido.', 'user' => $user]);
            return;
        }

        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';

        if ($password !== '') {
            if (strlen($password) < 6) {
                $this->render('perfil/index', ['error' => 'La contraseña debe tener al menos 6 caracteres.', 'user' => $user]);
                return;
            }
            if ($password !== $password2) {
                $this->render('perfil/index', ['error' => 'Las contraseñas no coinciden.', 'user' => $user]);
                return;
            }
            $user->setPasswordHash(password_hash($password, PASSWORD_BCRYPT));
        }

        $esPaciente = $user->getTipo() === 'paciente';

        if ($esPaciente) {
            if ($telefono !== '') {
                $user->setTelefono($telefono);
            }
            if ($email !== '') {
                $user->setEmail($email);
            }
            $this->usuarioRepo->updatePaciente($user);
        } else {
            if ($email !== '') {
                $user->setEmail($email);
            }
            $this->usuarioRepo->updateFuncionario($user);
        }

        $_SESSION['user_nombre'] = $user->getNombreCompleto();

        $this->render('perfil/index', ['success' => 'Datos actualizados correctamente.', 'user' => $user]);
    }
}
