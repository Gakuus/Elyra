<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\Entity;

use Elyra\Domain\Entity\Funcionario;
use Elyra\Domain\ValueObject\RolUsuario;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Funcionario::class)]
final class FuncionarioTest extends TestCase
{
    public function testCreateAdmin(): void
    {
        $f = new Funcionario(
            id: 1,
            nombre: 'María',
            apellido: 'García',
            rol: new RolUsuario('admin'),
            username: 'mgarcia',
            passwordHash: password_hash('secret123', PASSWORD_BCRYPT),
        );

        $this->assertSame(1, $f->getId());
        $this->assertSame('María García', $f->getNombreCompleto());
        $this->assertSame('mgarcia', $f->getUsername());
        $this->assertTrue($f->esAdmin());
        $this->assertFalse($f->esSuperadmin());
        $this->assertFalse($f->esConductor());
    }

    public function testCreateSuperadmin(): void
    {
        $f = new Funcionario(
            id: 2,
            nombre: 'Carlos',
            apellido: 'Rodríguez',
            rol: new RolUsuario('superadmin'),
            username: 'crodriguez',
            passwordHash: password_hash('admin123', PASSWORD_BCRYPT),
        );

        $this->assertTrue($f->esAdmin());
        $this->assertTrue($f->esSuperadmin());
    }

    public function testCreateConductor(): void
    {
        $f = new Funcionario(
            id: 3,
            nombre: 'Luis',
            apellido: 'Fernández',
            rol: new RolUsuario('conductor'),
            username: 'lfernandez',
            passwordHash: password_hash('driver123', PASSWORD_BCRYPT),
            licencia: 'A-12345',
            telefono: '099123456',
        );

        $this->assertTrue($f->esConductor());
        $this->assertFalse($f->esAdmin());
        $this->assertSame('A-12345', $f->getLicencia());
        $this->assertSame('099123456', $f->getTelefono());
    }

    public function testVerificarPassword(): void
    {
        $f = new Funcionario(
            id: 1,
            nombre: 'Test',
            apellido: 'User',
            rol: new RolUsuario('admin'),
            username: 'tuser',
            passwordHash: password_hash('correct-password', PASSWORD_BCRYPT),
        );

        $this->assertTrue($f->verificarPassword('correct-password'));
        $this->assertFalse($f->verificarPassword('wrong-password'));
    }

    public function testVerificarPasswordSinHash(): void
    {
        $f = new Funcionario(
            id: 1,
            nombre: 'Test',
            apellido: 'User',
            rol: new RolUsuario('admin'),
        );

        $this->assertFalse($f->verificarPassword('any'));
    }

    public function testIsActivoDefaultTrue(): void
    {
        $f = new Funcionario(
            id: 1,
            nombre: 'Test',
            apellido: 'User',
            rol: new RolUsuario('admin'),
        );

        $this->assertTrue($f->isActivo());
    }

    public function testSetActivo(): void
    {
        $f = new Funcionario(
            id: 1,
            nombre: 'Test',
            apellido: 'User',
            rol: new RolUsuario('admin'),
            activo: true,
        );

        $this->assertTrue($f->isActivo());
        $f->setActivo(false);
        $this->assertFalse($f->isActivo());
    }

    public function testSetPasswordHash(): void
    {
        $f = new Funcionario(
            id: 1,
            nombre: 'Test',
            apellido: 'User',
            rol: new RolUsuario('admin'),
            passwordHash: password_hash('old', PASSWORD_BCRYPT),
        );

        $newHash = password_hash('new-password', PASSWORD_BCRYPT);
        $f->setPasswordHash($newHash);
        $this->assertTrue($f->verificarPassword('new-password'));
    }

    public function testSetTelefono(): void
    {
        $f = new Funcionario(
            id: 1, nombre: 'Test', apellido: 'User',
            rol: new RolUsuario('admin'), telefono: '099111111',
        );
        $f->setTelefono('099222222');
        $this->assertSame('099222222', $f->getTelefono());
    }

    public function testHerenciaDeUsuario(): void
    {
        $f = new Funcionario(
            id: 1,
            nombre: 'Test',
            apellido: 'User',
            rol: new RolUsuario('admin'),
            email: 'test@hospital.com',
            documentoIdentidad: '12345678',
        );

        $this->assertSame('test@hospital.com', $f->getEmail());
        $this->assertSame('12345678', $f->getDocumentoIdentidad());
        $this->assertSame('funcionario', $f->getTipo());
    }

    public function testSetUsername(): void
    {
        $f = new Funcionario(
            id: 1, nombre: 'Test', apellido: 'User',
            rol: new RolUsuario('admin'), username: 'oldname',
        );
        $f->setUsername('newname');
        $this->assertSame('newname', $f->getUsername());
    }
}
