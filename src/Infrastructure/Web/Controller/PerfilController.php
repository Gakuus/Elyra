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

        $ci = trim($_POST['documento_identidad'] ?? '');
        if ($ci !== '' && $user->getDocumentoIdentidad() === null) {
            if (!preg_match('/^\d{8}$/', $ci)) {
                $this->render('perfil/index', ['error' => 'La cédula debe tener exactamente 8 dígitos.', 'user' => $user]);
                return;
            }

            try {
                $user->setDocumentoIdentidad($ci);
            } catch (\Throwable $th) {
                $this->render('perfil/index', ['error' => 'Esa cédula ya está registrada por otro usuario.', 'user' => $user]);
                return;
            }
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
            $error = $this->validarFoto($_FILES['foto']);
            if ($error) {
                $this->render('perfil/index', ['error' => $error, 'user' => $user]);
                return;
            }

            $contenido = file_get_contents($_FILES['foto']['tmp_name']);
            $user->setFoto($contenido);
        }

        $esPaciente = $user->getTipo() === 'paciente';

        try {
            if ($esPaciente) {
                $this->usuarioRepo->updatePaciente($user);
            } else {
                $this->usuarioRepo->updateFuncionario($user);
            }
        } catch (\Throwable $th) {
            $this->render('perfil/index', ['error' => 'Error al guardar. Verificá que los datos no estén duplicados.', 'user' => $user]);
            return;
        }

        $_SESSION['user_nombre'] = $user->getNombreCompleto();

        $this->render('perfil/index', ['success' => 'Datos actualizados correctamente.', 'user' => $user]);
    }

    private function validarFoto(array $file): ?string
    {
        $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedMime, true)) {
            return 'La foto debe ser JPEG, PNG, GIF o WebP.';
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt, true)) {
            return 'Extensión de archivo no permitida.';
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            return 'La foto no debe superar los 2MB.';
        }

        $img = @imagecreatefromstring(file_get_contents($file['tmp_name']));
        if ($img === false) {
            return 'El archivo no es una imagen válida.';
        }
        imagedestroy($img);

        return null;
    }
}
