<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Domain\Entity\Funcionario;
use Elyra\Domain\ValueObject\RolUsuario;
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

        $this->render('auth/login', ['error' => $result['error']]);
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
          ->maxLength('documento', $documento, 20, 'Cédula')
          ->required('username', $username, 'Usuario')
          ->minLength('username', $username, 3, 'Usuario')
          ->maxLength('username', $username, 50, 'Usuario')
          ->maxLength('telefono', $telefono, 20, 'Teléfono')
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

        if ($this->usuarioRepo->findFuncionarioByUsername($username)) {
            $this->render('auth/registro', ['error' => 'El nombre de usuario ya está registrado']);
            return;
        }

        if ($this->usuarioRepo->findFuncionarioByEmail($email)) {
            $this->render('auth/registro', ['error' => 'El email ya está registrado']);
            return;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        $funcionario = new Funcionario(
            id: null,
            nombre: Validator::sanitize($nombre),
            apellido: Validator::sanitize($apellido),
            rol: new RolUsuario('conductor'),
            username: $username,
            passwordHash: $hash,
            email: $email,
            documentoIdentidad: $documento,
            telefono: $telefono,
            activo: true
        );

        try {
            $this->usuarioRepo->saveFuncionario($funcionario);
            SessionManager::login($funcionario->getId(), $funcionario->getRol()->value(), $funcionario->getNombreCompleto());
            $this->redirect('/dashboard');
        } catch (\Exception $e) {
            $this->render('auth/registro', ['error' => 'Error al registrar. Verificá que los datos no estén duplicados.']);
        }
    }

    public function logout(): void
    {
        $this->authService->logout();
        $this->redirect('/login');
    }
}
