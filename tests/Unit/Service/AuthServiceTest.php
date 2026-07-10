<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\Service;

use Elyra\Domain\Entity\Funcionario;
use Elyra\Domain\Entity\Paciente;
use Elyra\Domain\Repository\UsuarioRepositoryInterface;
use Elyra\Domain\ValueObject\RolUsuario;
use Elyra\Infrastructure\Service\AuthService;
use Elyra\Infrastructure\Service\SessionManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(AuthService::class)]
final class AuthServiceTest extends TestCase
{
    private MockObject&UsuarioRepositoryInterface $usuarioRepo;
    private AuthService $authService;

    protected function setUp(): void
    {
        $this->usuarioRepo = $this->createMock(UsuarioRepositoryInterface::class);
        $this->authService = new AuthService($this->usuarioRepo);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public function testLoginSuccess(): void
    {
        $funcionario = new Funcionario(
            id: 1,
            nombre: 'Admin',
            apellido: 'User',
            rol: new RolUsuario('admin'),
            username: 'admin',
            passwordHash: password_hash('secret', PASSWORD_BCRYPT),
            activo: true,
        );

        $this->usuarioRepo->method('findFuncionarioByUsername')
            ->with('admin') // @phpstan-ignore method.notFound
            ->willReturn($funcionario);

        $result = $this->authService->login('admin', 'secret');

        $this->assertTrue($result['success']);
        $this->assertArrayNotHasKey('error', $result);
    }

    public function testLoginFailsWithWrongPassword(): void
    {
        $funcionario = new Funcionario(
            id: 1,
            nombre: 'Admin',
            apellido: 'User',
            rol: new RolUsuario('admin'),
            username: 'admin',
            passwordHash: password_hash('secret', PASSWORD_BCRYPT),
            activo: true,
        );

        $this->usuarioRepo->method('findFuncionarioByUsername')
            ->with('admin') // @phpstan-ignore method.notFound
            ->willReturn($funcionario);

        $result = $this->authService->login('admin', 'wrongpassword');

        $this->assertFalse($result['success']);
        $this->assertSame('Credenciales inválidas', $result['error'] ?? '');
    }

    public function testLoginFailsWithNonExistentUser(): void
    {
        $this->usuarioRepo->method('findFuncionarioByUsername')
            ->with('noexiste') // @phpstan-ignore method.notFound
            ->willReturn(null);

        $this->usuarioRepo->method('findPacienteByUsername')
            ->with('noexiste') // @phpstan-ignore method.notFound
            ->willReturn(null);

        $result = $this->authService->login('noexiste', 'anypass');

        $this->assertFalse($result['success']);
        $this->assertSame('Credenciales inválidas', $result['error'] ?? '');
    }

    public function testLoginFailsWithInactiveUser(): void
    {
        $funcionario = new Funcionario(
            id: 1,
            nombre: 'Inactive',
            apellido: 'User',
            rol: new RolUsuario('admin'),
            username: 'inactive',
            passwordHash: password_hash('secret', PASSWORD_BCRYPT),
            activo: false,
        );

        $this->usuarioRepo->method('findFuncionarioByUsername')
            ->with('inactive') // @phpstan-ignore method.notFound
            ->willReturn($funcionario);

        $result = $this->authService->login('inactive', 'secret');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('desactivado', $result['error'] ?? '');
    }

    public function testLoginFailsWithInactivePaciente(): void
    {
        $paciente = new Paciente(
            id: 1,
            nombre: 'Paciente',
            apellido: 'Test',
            username: 'paciente1',
            passwordHash: password_hash('pass', PASSWORD_BCRYPT),
            activo: false,
        );

        $this->usuarioRepo->method('findFuncionarioByUsername')
            ->with('jperez') // @phpstan-ignore method.notFound
            ->willReturn(null);

        $this->usuarioRepo->method('findPacienteByUsername')
            ->with('jperez') // @phpstan-ignore method.notFound
            ->willReturn($paciente);

        $result = $this->authService->login('paciente1', 'pass');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('desactivado', $result['error'] ?? '');
    }

    public function testLoginSuccessForPaciente(): void
    {
        $paciente = new Paciente(
            id: 1,
            nombre: 'Juan',
            apellido: 'Pérez',
            username: 'jperez',
            passwordHash: password_hash('mypass', PASSWORD_BCRYPT),
            activo: true,
        );

        $this->usuarioRepo->method('findFuncionarioByUsername')
            ->with('jperez') // @phpstan-ignore method.notFound
            ->willReturn(null);

        $this->usuarioRepo->method('findPacienteByUsername')
            ->with('jperez') // @phpstan-ignore method.notFound
            ->willReturn($paciente);

        $result = $this->authService->login('jperez', 'mypass');

        $this->assertTrue($result['success']);
    }

    public function testLoginPacienteWithoutPasswordFails(): void
    {
        $paciente = new Paciente(
            id: 1,
            nombre: 'Juan',
            apellido: 'Pérez',
            username: 'jperez',
        );

        $this->usuarioRepo->method('findFuncionarioByUsername')
            ->with('jperez') // @phpstan-ignore method.notFound
            ->willReturn(null);

        $this->usuarioRepo->method('findPacienteByUsername')
            ->with('jperez') // @phpstan-ignore method.notFound
            ->willReturn($paciente);

        $result = $this->authService->login('jperez', 'anypass');

        $this->assertFalse($result['success']);
    }

    public function testLogout(): void
    {
        SessionManager::login(1, 'admin', 'Admin User');
        $this->assertTrue(SessionManager::isAuthenticated());

        $this->authService->logout();
        $this->assertFalse(SessionManager::isAuthenticated());
    }

    public function testIsAuthenticated(): void
    {
        $this->assertFalse($this->authService->isAuthenticated());

        SessionManager::login(1, 'admin', 'Admin');

        $this->assertTrue($this->authService->isAuthenticated());
    }

    public function testGetCurrentUserId(): void
    {
        $this->assertNull($this->authService->getCurrentUserId());

        SessionManager::login(42, 'admin', 'Admin');

        $this->assertSame(42, $this->authService->getCurrentUserId());
    }

    public function testGetCurrentUserRole(): void
    {
        $this->assertNull($this->authService->getCurrentUserRole());

        SessionManager::login(1, 'superadmin', 'Super');

        $this->assertSame('superadmin', $this->authService->getCurrentUserRole());
    }

    public function testRequireRoleSuccess(): void
    {
        SessionManager::login(1, 'admin', 'Admin');

        $this->assertTrue($this->authService->requireRole('admin'));
        $this->assertTrue($this->authService->requireRole('admin', 'superadmin'));
        $this->assertFalse($this->authService->requireRole('superadmin'));
    }

    public function testRequireRoleReturnsFalseWhenNotAuthenticated(): void
    {
        $this->assertFalse($this->authService->requireRole('admin'));
    }

    public function testRequireRoleReturnsFalseForPaciente(): void
    {
        SessionManager::login(1, 'paciente', 'Paciente');

        $this->assertFalse($this->authService->requireRole('admin'));
    }
}
