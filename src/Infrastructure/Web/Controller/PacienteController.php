<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Domain\Entity\Paciente;
use Elyra\Infrastructure\Persistence\MySQL\UsuarioRepository;

class PacienteController extends BaseController
{
    private UsuarioRepository $usuarioRepo;

    public function __construct()
    {
        $this->usuarioRepo = new UsuarioRepository();
    }

    public function editar(): void
    {
        $this->requireAuth();
        $this->denyPaciente();
        $this->requireRole('admin', 'superadmin');

        /** @var string $idRaw */
        $idRaw = $_GET['id'] ?? '0';
        $id = (int) $idRaw;

        if ($id <= 0) {
            $this->redirect('/funcionarios');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEditar($id);
            return;
        }

        $existing = $this->usuarioRepo->findById($id);
        if ($existing === null || $existing->getTipo() !== 'paciente') {
            $this->redirect('/funcionarios');
            return;
        }

        /** @var Paciente $paciente */
        $paciente = $existing;

        $this->render('pacientes/editar', [
            'paciente' => [
                'id' => $paciente->getId(),
                'nombre' => $paciente->getNombre(),
                'apellido' => $paciente->getApellido(),
                'username' => $paciente->getUsername() ?? '',
                'email' => $paciente->getEmail() ?? '',
                'telefono' => $paciente->getTelefono() ?? '',
                'documento_identidad' => $paciente->getDocumentoIdentidad() ?? '',
            ],
        ]);
    }

    private function handleEditar(int $id): void
    {
        /** @var string $csrfTokenRaw */
        $csrfTokenRaw = $_POST['_csrf_token'] ?? '';
        $csrf = trim($csrfTokenRaw);
        /** @var string $csrfSession */
        $csrfSession = $_SESSION['_csrf_token'] ?? '';
        if ($csrf !== $csrfSession) {
            $this->redirect('/pacientes/editar?id=' . $id . '&error=csrf');
            return;
        }

        /** @var string $nombre */
        $nombre = $_POST['nombre'] ?? '';
        /** @var string $apellido */
        $apellido = $_POST['apellido'] ?? '';
        /** @var string $username */
        $username = $_POST['username'] ?? '';
        /** @var string $email */
        $email = $_POST['email'] ?? '';
        /** @var string $documento */
        $documento = $_POST['documento_identidad'] ?? '';
        /** @var string $telefono */
        $telefono = $_POST['telefono'] ?? '';
        /** @var string $password */
        $password = $_POST['password'] ?? '';

        if (strlen($nombre) < 2) {
            $this->redirect('/pacientes/editar?id=' . $id . '&error=' . urlencode('El nombre debe tener al menos 2 caracteres.'));
            return;
        }
        if (strlen($apellido) < 2) {
            $this->redirect('/pacientes/editar?id=' . $id . '&error=' . urlencode('El apellido debe tener al menos 2 caracteres.'));
            return;
        }
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirect('/pacientes/editar?id=' . $id . '&error=' . urlencode('El email no es válido.'));
            return;
        }
        if (!empty($documento) && preg_match('/^\d{8}$/', $documento) !== 1) {
            $this->redirect('/pacientes/editar?id=' . $id . '&error=' . urlencode('La cédula debe tener 8 dígitos.'));
            return;
        }
        if (!empty($password) && strlen($password) < 6) {
            $this->redirect('/pacientes/editar?id=' . $id . '&error=' . urlencode('La contraseña debe tener al menos 6 caracteres.'));
            return;
        }

        $existing = $this->usuarioRepo->findById($id);
        if ($existing === null || $existing->getTipo() !== 'paciente') {
            $this->redirect('/funcionarios');
            return;
        }

        /** @var Paciente $paciente */
        $paciente = $existing;
        $paciente->setNombre($nombre);
        $paciente->setApellido($apellido);
        $paciente->setEmail($email !== '' ? $email : null);
        $paciente->setDocumentoIdentidad($documento !== '' ? $documento : null);
        $paciente->setTelefono($telefono !== '' ? $telefono : null);

        if ($username !== ($paciente->getUsername() ?? '')) {
            $dup = $this->usuarioRepo->findPacienteByUsername($username);
            if ($dup !== null && $dup->getId() !== $id) {
                $this->redirect('/pacientes/editar?id=' . $id . '&error=' . urlencode('El nombre de usuario ya está registrado.'));
                return;
            }
            $paciente->setUsername($username);
        }

        if (!empty($password)) {
            $paciente->setPasswordHash(password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]));
        }

        $this->usuarioRepo->updatePaciente($paciente);

        $this->redirect('/funcionarios?actualizado=1');
    }
}
