<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Domain\Entity\Funcionario;
use Elyra\Domain\Entity\Paciente;
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
        if ($userId === null) {
            $this->redirect('/login');
            return;
        }
        $user = $this->usuarioRepo->findById($userId);

        $this->render('perfil/index', [
            'user' => $user,
        ]);
    }

    public function actualizar(): void
    {
        $this->requireAuth();

        $userId = SessionManager::getUserId();
        if ($userId === null) {
            $this->redirect('/login');
            return;
        }
        $user = $this->usuarioRepo->findById($userId);

        if (!$user) {
            $this->redirect('/perfil');
            return;
        }

        /** @var string $email */
        $email = $_POST['email'] ?? '';
        $email = trim($email);
        /** @var string $telefono */
        $telefono = $_POST['telefono'] ?? '';
        $telefono = trim($telefono);

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

        /** @var string $ci */
        $ci = $_POST['documento_identidad'] ?? '';
        $ci = trim($ci);
        if ($ci !== '' && $user->getDocumentoIdentidad() === null) {
            if (!preg_match('/^\d{8}$/', $ci)) {
                $this->render('perfil/index', ['error' => 'La cédula debe tener exactamente 8 dígitos.', 'user' => $user]);
                return;
            }
            $user->setDocumentoIdentidad($ci);
        }

        /** @var string $password */
        $password = $_POST['password'] ?? '';
        /** @var string $password2 */
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
            if ($user instanceof Paciente || $user instanceof Funcionario) {
                $user->setPasswordHash(password_hash($password, PASSWORD_BCRYPT));
            }
        }

        try {
            if ($user instanceof Paciente) {
                $this->usuarioRepo->updatePaciente($user);
            } elseif ($user instanceof Funcionario) {
                $this->usuarioRepo->updateFuncionario($user);
            }
        } catch (\Throwable $th) {
            $msg = 'Error al guardar.';
            if (str_contains($th->getMessage(), 'Duplicate entry')) {
                $msg = 'Ese valor ya está registrado por otro usuario (cédula o email duplicado).';
            }
            $this->render('perfil/index', ['error' => $msg, 'user' => $user]);
            return;
        }

        if (isset($_FILES['foto']) && is_array($_FILES['foto'])) {
            /** @var array{name: string, tmp_name: string, error: int, size: int} $fotoFile */
            $fotoFile = $_FILES['foto'];
            if ($fotoFile['error'] === UPLOAD_ERR_OK) {
                $error = $this->validarFoto($fotoFile);
                if ($error) {
                    $this->render('perfil/index', ['error' => $error, 'user' => $user]);
                    return;
                }

                $contenido = file_get_contents($fotoFile['tmp_name']);
                if ($contenido !== false) {
                    $this->usuarioRepo->updateFoto((int) $user->getId(), $contenido);
                }
            }
        }

        $_SESSION['user_nombre'] = $user->getNombreCompleto();

        $this->render('perfil/index', ['success' => 'Datos actualizados correctamente.', 'user' => $user]);
    }

    /**
     * @param array{name: string, tmp_name: string, error: int, size: int} $file
     */
    private function validarFoto(array $file): ?string
    {
        $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return 'No se pudo validar la imagen.';
        }
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

        $imageContent = file_get_contents($file['tmp_name']);
        if ($imageContent === false) {
            return 'No se pudo leer la imagen.';
        }
        $img = @imagecreatefromstring($imageContent);
        if ($img === false) {
            return 'El archivo no es una imagen válida.';
        }
        imagedestroy($img);

        return null;
    }
}
