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

        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        if ($telefono !== '' && !preg_match('/^[0-9]{8,9}$/', $telefono)) {
            $this->render('perfil/index', ['error' => 'El teléfono debe tener 8 o 9 dígitos.', 'user' => $user]);
            return;
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->render('perfil/index', ['error' => 'Email inválido.', 'user' => $user]);
            return;
        }

        $user->setEmail($email !== '' ? $email : null);

        if (method_exists($user, 'setTelefono')) {
            $user->setTelefono($telefono !== '' ? $telefono : null);
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

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['foto']['tmp_name']);
            finfo_close($finfo);

            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($mime, $allowed)) {
                $this->render('perfil/index', ['error' => 'La foto debe ser JPEG, PNG, GIF o WebP.', 'user' => $user]);
                return;
            }

            if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
                $this->render('perfil/index', ['error' => 'La foto no debe superar los 2MB.', 'user' => $user]);
                return;
            }

            $contenido = file_get_contents($_FILES['foto']['tmp_name']);
            $user->setFoto($contenido);
        }

        $esPaciente = $user->getTipo() === 'paciente';

        if ($esPaciente) {
            $this->usuarioRepo->updatePaciente($user);
        } else {
            $this->usuarioRepo->updateFuncionario($user);
        }

        $_SESSION['user_nombre'] = $user->getNombreCompleto();

        $this->render('perfil/index', ['success' => 'Datos actualizados correctamente.', 'user' => $user]);
    }
}
