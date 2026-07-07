<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Domain\Entity\Paciente;
use Elyra\Infrastructure\Persistence\MySQL\UsuarioRepository;
use Elyra\Infrastructure\Service\SessionManager;
use Elyra\Infrastructure\Service\Validator;

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
        $this->requireRole('paciente');

        $userId = SessionManager::getUserId();
        $paciente = $this->usuarioRepo->findById($userId);

        if (!$paciente || !($paciente instanceof Paciente)) {
            $this->render('perfil/index', ['error' => 'Paciente no encontrado.', 'paciente' => null]);
            return;
        }

        $this->render('perfil/index', [
            'paciente' => $paciente,
        ]);
    }

    public function actualizar(): void
    {
        $this->requireAuth();
        $this->requireRole('paciente');

        $userId = SessionManager::getUserId();
        $paciente = $this->usuarioRepo->findById($userId);

        if (!$paciente || !($paciente instanceof Paciente)) {
            $this->redirect('/perfil');
            return;
        }

        $telefono = trim($_POST['telefono'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($telefono !== '' && !preg_match('/^[0-9]{8,9}$/', $telefono)) {
            $this->render('perfil/index', ['error' => 'El teléfono debe tener 8 o 9 dígitos.', 'paciente' => $paciente]);
            return;
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->render('perfil/index', ['error' => 'Email inválido.', 'paciente' => $paciente]);
            return;
        }

        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';

        if ($password !== '') {
            if (strlen($password) < 6) {
                $this->render('perfil/index', ['error' => 'La contraseña debe tener al menos 6 caracteres.', 'paciente' => $paciente]);
                return;
            }
            if ($password !== $password2) {
                $this->render('perfil/index', ['error' => 'Las contraseñas no coinciden.', 'paciente' => $paciente]);
                return;
            }
            $paciente->setPasswordHash(password_hash($password, PASSWORD_BCRYPT));
        }

        if ($telefono !== '') {
            $paciente->setTelefono($telefono);
        }

        if ($email !== '') {
            $paciente->setEmail($email);
        }

        $this->usuarioRepo->updatePaciente($paciente);

        $_SESSION['user_nombre'] = $paciente->getNombreCompleto();

        $this->render('perfil/index', ['success' => 'Datos actualizados correctamente.', 'paciente' => $paciente]);
    }
}
