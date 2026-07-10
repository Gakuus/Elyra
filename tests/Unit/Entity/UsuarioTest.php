<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\Entity;

use Elyra\Domain\Entity\Usuario;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Usuario::class)]
final class UsuarioTest extends TestCase
{
    public function testCreateUsuario(): void
    {
        $u = new Usuario(
            id: 1,
            tipo: 'funcionario',
            nombre: 'María',
            apellido: 'García',
            email: 'maria@hospital.com',
            documentoIdentidad: '12345678',
        );

        $this->assertSame(1, $u->getId());
        $this->assertSame('funcionario', $u->getTipo());
        $this->assertSame('María', $u->getNombre());
        $this->assertSame('García', $u->getApellido());
        $this->assertSame('María García', $u->getNombreCompleto());
        $this->assertSame('maria@hospital.com', $u->getEmail());
        $this->assertSame('12345678', $u->getDocumentoIdentidad());
    }

    public function testSetId(): void
    {
        $u = new Usuario(id: null, tipo: 'funcionario', nombre: 'A', apellido: 'B');
        $u->setId(10);
        $this->assertSame(10, $u->getId());
    }

    public function testSetEmail(): void
    {
        $u = new Usuario(id: 1, tipo: 'funcionario', nombre: 'A', apellido: 'B');
        $u->setEmail('new@email.com');
        $this->assertSame('new@email.com', $u->getEmail());
    }

    public function testSetDocumentoIdentidad(): void
    {
        $u = new Usuario(id: 1, tipo: 'funcionario', nombre: 'A', apellido: 'B');
        $u->setDocumentoIdentidad('87654321');
        $this->assertSame('87654321', $u->getDocumentoIdentidad());
    }

    public function testFotoNullable(): void
    {
        $u = new Usuario(id: 1, tipo: 'funcionario', nombre: 'A', apellido: 'B');
        $this->assertNull($u->getFoto());

        $u->setFoto('binary_data');
        $this->assertNotNull($u->getFoto());
    }

    public function testGetFotoBase64SinFoto(): void
    {
        $u = new Usuario(id: 1, tipo: 'funcionario', nombre: 'A', apellido: 'B');
        $this->assertNull($u->getFotoBase64());
    }

    public function testGetFotoBase64ConFoto(): void
    {
        $u = new Usuario(id: 1, tipo: 'funcionario', nombre: 'A', apellido: 'B');
        $u->setFoto('fake_jpeg_bytes');
        $expected = 'data:image/jpeg;base64,' . base64_encode('fake_jpeg_bytes');
        $this->assertSame($expected, $u->getFotoBase64());
    }

    public function testEmailDocIdentidadCreatedAtNullables(): void
    {
        $u = new Usuario(id: 1, tipo: 'funcionario', nombre: 'A', apellido: 'B');
        $this->assertNull($u->getEmail());
        $this->assertNull($u->getDocumentoIdentidad());
        $this->assertNull($u->getCreatedAt());
    }
}
