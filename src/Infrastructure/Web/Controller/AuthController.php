<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Application\UseCases\Auth\EjecutarResetPasswordUseCase;
use Elyra\Application\UseCases\Auth\SolicitarResetPasswordUseCase;
use Elyra\Domain\Entity\Funcionario;
use Elyra\Domain\Entity\Paciente;
use Elyra\Infrastructure\Persistence\MySQL\UsuarioRepository;
use Elyra\Infrastructure\Service\AuthService;
use Elyra\Infrastructure\Service\EmailServiceInterface;
use Elyra\Infrastructure\Service\ErrorHandler;
use Elyra\Infrastructure\Service\PhpMailEmailService;
use Elyra\Infrastructure\Service\RateLimiter;
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
        /** @var string $usernameInput */
        $usernameInput = $_POST['username'] ?? '';
        $username = trim($usernameInput);
        /** @var string $password */
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $this->render('auth/login', ['error' => 'Ingrese usuario y contraseña']);
            return;
        }

        $result = $this->authService->login($username, $password);

        if ($result['success']) {
            $user = $result['user'] ?? null;
            $rol = $user instanceof \Elyra\Domain\Entity\Funcionario ? $user->getRol()->value() : 'paciente';
            \Elyra\Infrastructure\Service\AuditLogger::logLogin(
                SessionManager::getUserId() ?? 0,
                $rol,
                $username,
            );
            $this->redirect('/dashboard');
        }

        \Elyra\Infrastructure\Service\AuditLogger::log('login_failed', 'auth', null, ['username' => $username, 'reason' => $result['error'] ?? '']);
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

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (!is_string($ip)) {
            $ip = '127.0.0.1';
        }
        if (!RateLimiter::checkRegistrationAttempts($ip)) {
            ErrorHandler::log('WARNING', "Registro bloqueado por rate limit desde IP: {$ip}");
            $this->render('auth/registro', ['error' => 'Demasiados registros desde esta IP. Intente de nuevo más tarde.']);
            return;
        }
        RateLimiter::incrementRegistrationAttempts($ip);

        /** @var string $nombreInput */
        $nombreInput = $_POST['nombre'] ?? '';
        $nombre = trim($nombreInput);
        /** @var string $apellidoInput */
        $apellidoInput = $_POST['apellido'] ?? '';
        $apellido = trim($apellidoInput);
        /** @var string $emailInput */
        $emailInput = $_POST['email'] ?? '';
        $email = trim($emailInput);
        /** @var string $documentoInput */
        $documentoInput = $_POST['documento'] ?? '';
        $documento = trim($documentoInput);
        /** @var string $usernameInput */
        $usernameInput = $_POST['username'] ?? '';
        $username = trim($usernameInput);
        /** @var string $telefonoInput */
        $telefonoInput = $_POST['telefono'] ?? '';
        $telefono = trim($telefonoInput);
        /** @var string $password */
        $password = $_POST['password'] ?? '';
        /** @var string $password2 */
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
           ->minLength('password', $password, 8, 'Contraseña');

        if ($password !== $password2) {
            $v->required('password2', null, 'Repetir contraseña');
            $_SESSION['error_fields'] = ['password2' => 'Las contraseñas no coinciden'];
        }

        if (!$v->isValid()) {
            $error = $v->getFirstError() ?? 'Las contraseñas no coinciden';
            ErrorHandler::log('INFO', "Registro falló validación desde IP: {$ip}, username: {$username}, error: {$error}");
            $this->render('auth/registro', ['error' => $error]);
            return;
        }

        if ($telefono !== '' && !preg_match('/^[0-9]{8,9}$/', $telefono)) {
            ErrorHandler::log('INFO', "Registro falló por teléfono inválido desde IP: {$ip}, username: {$username}");
            $this->render('auth/registro', ['error' => 'El teléfono debe tener 8 o 9 dígitos']);
            return;
        }

        if ($this->usuarioRepo->findFuncionarioByUsername($username) || $this->usuarioRepo->findPacienteByUsername($username)) {
            ErrorHandler::log('INFO', "Registro falló: username duplicado '{$username}' desde IP: {$ip}");
            $this->render('auth/registro', ['error' => 'El nombre de usuario ya está registrado']);
            return;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
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
            $userId = $paciente->getId();
            ErrorHandler::log('INFO', "Registro exitoso: username '{$username}', IP: {$ip}");
            \Elyra\Infrastructure\Service\AuditLogger::logCreate(
                'paciente',
                $userId !== null ? (string) $userId : null,
                ['username' => $username],
            );
            SessionManager::login($userId !== null ? $userId : 0, 'paciente', $paciente->getNombreCompleto());
            $this->redirect('/dashboard');
        } catch (\Exception $e) {
            ErrorHandler::log('ERROR', "Registro falló con excepción: {$e->getMessage()}, IP: {$ip}, username: {$username}");
            $this->render('auth/registro', ['error' => 'Error al registrar. Verificá que los datos no estén duplicados.']);
        }
    }

    public function logout(): void
    {
        \Elyra\Infrastructure\Service\AuditLogger::logLogout();
        $this->authService->logout();
        $this->redirect('/');
    }

    public function solicitarResetPassword(): void
    {
        if ($this->authService->isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->render('auth/solicitar-reset');
            return;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        /** @var string $ip */
        if (!RateLimiter::checkResetAttempts($ip)) {
            $this->render('auth/solicitar-reset', ['error' => 'Demasiadas solicitudes. Esper&aacute; unos minutos.']);
            return;
        }
        RateLimiter::incrementResetAttempts($ip);

        /** @var string $csrfTokenRaw */
        $csrfTokenRaw = $_POST['_csrf_token'] ?? '';
        $csrf = trim($csrfTokenRaw);
        /** @var string $csrfSession */
        $csrfSession = $_SESSION['_csrf_token'] ?? '';
        if (!hash_equals($csrfSession, $csrf)) {
            $this->render('auth/solicitar-reset', ['error' => 'Sesi&oacute;n inv&aacute;lida.']);
            return;
        }

        /** @var string $emailInput */
        $emailInput = $_POST['email'] ?? '';
        $email = trim($emailInput);

        $useCase = new SolicitarResetPasswordUseCase($this->usuarioRepo, new PhpMailEmailService());

        try {
            $result = $useCase->execute(['email' => $email]);
        } catch (\InvalidArgumentException $e) {
            $this->render('auth/solicitar-reset', ['error' => $e->getMessage()]);
            return;
        }

        if ($result['success']) {
            $this->render('auth/solicitar-reset', [
                'exito' => 'Si el email existe en nuestro sistema, se envi&oacute; un enlace de recuperaci&oacute;n. Revis&aacute; tu bandeja de entrada.',
            ]);
        }
    }

    public function resetPassword(): void
    {
        if ($this->authService->isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        /** @var string $tokenRaw */
        $tokenRaw = $_GET['token'] ?? $_POST['token'] ?? '';
        $token = trim($tokenRaw);

        if ($token === '') {
            $this->render('auth/reset-password', ['error' => 'Token inválido.']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->render('auth/reset-password', ['token' => $token]);
            return;
        }

        /** @var string $csrfTokenRaw */
        $csrfTokenRaw = $_POST['_csrf_token'] ?? '';
        $csrf = trim($csrfTokenRaw);
        /** @var string $csrfSession */
        $csrfSession = $_SESSION['_csrf_token'] ?? '';
        if (!hash_equals($csrfSession, $csrf)) {
            $this->render('auth/reset-password', ['token' => $token, 'error' => 'Sesi&oacute;n inv&aacute;lida.']);
            return;
        }

        /** @var string $password */
        $password = $_POST['password'] ?? '';
        /** @var string $password2 */
        $password2 = $_POST['password2'] ?? '';

        if (!hash_equals($password, $password2)) {
            $this->render('auth/reset-password', ['token' => $token, 'error' => 'Las contraseñas no coinciden.']);
            return;
        }

        $useCase = new EjecutarResetPasswordUseCase($this->usuarioRepo);

        try {
            $result = $useCase->execute(['token' => $token, 'password' => $password]);
        } catch (\InvalidArgumentException $e) {
            $this->render('auth/reset-password', ['token' => $token, 'error' => $e->getMessage()]);
            return;
        }

        if ($result['success']) {
            $this->render('auth/reset-password', [
                'exito' => 'Contraseña actualizada correctamente. Ya podés iniciar sesión.',
            ]);
        }
    }
}
