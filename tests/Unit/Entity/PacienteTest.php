<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\Entity;

use Elyra\Domain\Entity\Paciente;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Paciente::class)]
final class PacienteTest extends TestCase
{
    public function testCreatePaciente(): void
    {
        $p = new Paciente(
            id: 1,
            nombre: 'Juan',
            apellido: 'Pérez',
            email: 'juan@email.com',
            documentoIdentidad: '12345678',
            tokenAcceso: '550e8400-e29b-41d4-a716-446655440000',
            username: 'jperez',
            passwordHash: password_hash('pass123', PASSWORD_BCRYPT),
            telefono: '099111111',
        );

        $this->assertSame(1, $p->getId());
        $this->assertSame('Juan', $p->getNombre());
        $this->assertSame('Pérez', $p->getApellido());
        $this->assertSame('Juan Pérez', $p->getNombreCompleto());
        $this->assertSame('paciente', $p->getTipo());
        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $p->getTokenAcceso());
        $this->assertSame('jperez', $p->getUsername());
    }

    public function testIsActivoDefaultTrue(): void
    {
        $p = new Paciente(id: 1, nombre: 'Juan', apellido: 'Pérez');
        $this->assertTrue($p->isActivo());
    }

    public function testSetActivo(): void
    {
        $p = new Paciente(id: 1, nombre: 'Juan', apellido: 'Pérez', activo: true);
        $p->setActivo(false);
        $this->assertFalse($p->isActivo());
    }

    public function testVerificarPassword(): void
    {
        $p = new Paciente(
            id: 1, nombre: 'Juan', apellido: 'Pérez',
            passwordHash: password_hash('secret', PASSWORD_BCRYPT),
        );

        $this->assertTrue($p->verificarPassword('secret'));
        $this->assertFalse($p->verificarPassword('wrong'));
    }

    public function testVerificarPasswordSinHash(): void
    {
        $p = new Paciente(id: 1, nombre: 'Juan', apellido: 'Pérez');
        $this->assertFalse($p->verificarPassword('any'));
    }

    public function testSetPasswordHash(): void
    {
        $p = new Paciente(
            id: 1, nombre: 'Juan', apellido: 'Pérez',
            passwordHash: password_hash('old', PASSWORD_BCRYPT),
        );
        $p->setPasswordHash(password_hash('new', PASSWORD_BCRYPT));
        $this->assertTrue($p->verificarPassword('new'));
    }

    public function testSetTelefono(): void
    {
        $p = new Paciente(id: 1, nombre: 'Juan', apellido: 'Pérez', telefono: '099111111');
        $p->setTelefono('099222222');
        $this->assertSame('099222222', $p->getTelefono());
    }

    public function testCodigoQrIdNullable(): void
    {
        $p = new Paciente(id: 1, nombre: 'Juan', apellido: 'Pérez');
        $this->assertNull($p->getCodigoQrId());

        $p2 = new Paciente(id: 1, nombre: 'Juan', apellido: 'Pérez', codigoQrId: 5);
        $this->assertSame(5, $p2->getCodigoQrId());
    }

    public function testTokenAccesoNullable(): void
    {
        $p = new Paciente(id: 1, nombre: 'Juan', apellido: 'Pérez');
        $this->assertNull($p->getTokenAcceso());
    }
}
