<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Domain\Entity\Paciente;
use Elyra\Infrastructure\Persistence\MySQL\UsuarioRepository;
use Elyra\Infrastructure\Service\AuthService;
use Elyra\Infrastructure\Service\SessionManager;
use Elyra\Infrastructure\Service\Validator;

class AuthController extends BaseController
{
    private AuthService $authService;
    private UsuarioRepository $usuarioRepo;

    public function __construct()
    {
        $this->usuarioRepo = new UsuarioRepository();
        $this->authService = new AuthService($this->usuarioRepo);
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

        $this->render('auth/login', ['error' => $result['error'] ?? '']);
    }

    public function registro(): void
    {
        if ($this->authService->isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        $this->render('auth/registro', ['error' => null]);
    }

    public function doRegistro(): void
    {
        if ($this->authService->isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';

        $v = new Validator();
        $v->required('nombre', $nombre, 'Nombre')
          ->minLength('nombre', $nombre, 2, 'Nombre')
          ->maxLength('nombre', $nombre, 100, 'Nombre')
          ->required('apellido', $apellido, 'Apellido')
          ->minLength('apellido', $apellido, 2, 'Apellido')
          ->maxLength('apellido', $apellido, 100, 'Apellido')
          ->required('email', $email, 'Email')
          ->email('email', $email, 'Email')
          ->maxLength('email', $email, 150, 'Email')
           ->required('documento', $documento, 'Cédula')
           ->numeric('documento', $documento, 'Cédula')
           ->minLength('documento', $documento, 8, 'Cédula')
           ->maxLength('documento', $documento, 8, 'Cédula')
          ->required('username', $username, 'Usuario')
          ->minLength('username', $username, 3, 'Usuario')
          ->maxLength('username', $username, 50, 'Usuario')
           ->maxLength('telefono', $telefono, 9, 'Teléfono')
           ->required('password', $password, 'Contraseña')
           ->minLength('password', $password, 6, 'Contraseña');

        if ($password !== $password2) {
            $v->required('password2', null, 'Repetir contraseña');
            $_SESSION['error_fields'] = ['password2' => 'Las contraseñas no coinciden'];
        }

        if (!$v->isValid()) {
            $error = $v->getFirstError() ?? 'Las contraseñas no coinciden';
            $this->render('auth/registro', ['error' => $error]);
            return;
        }

        if ($telefono !== '' && !preg_match('/^[0-9]{8,9}$/', $telefono)) {
            $this->render('auth/registro', ['error' => 'El teléfono debe tener 8 o 9 dígitos']);
            return;
        }

        if ($this->usuarioRepo->findFuncionarioByUsername($username) || $this->usuarioRepo->findPacienteByUsername($username)) {
            $this->render('auth/registro', ['error' => 'El nombre de usuario ya está registrado']);
            return;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $token = bin2hex(random_bytes(16));

        $paciente = new Paciente(
            id: null,
            nombre: Validator::sanitize($nombre),
            apellido: Validator::sanitize($apellido),
            email: $email,
            documentoIdentidad: $documento,
            tokenAcceso: $token,
            username: $username,
            passwordHash: $hash,
            telefono: $telefono,
            activo: true
        );

        try {
            $this->usuarioRepo->savePaciente($paciente);
            SessionManager::login($paciente->getId(), 'paciente', $paciente->getNombreCompleto());
            $this->redirect('/dashboard');
        } catch (\Exception $e) {
            $this->render('auth/registro', ['error' => 'Error al registrar. Verificá que los datos no estén duplicados.']);
        }
    }

    public function logout(): void
    {
        $this->authService->logout();
        $this->redirect('/');
    }
}
