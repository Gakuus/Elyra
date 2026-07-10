<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\Entity;

use Elyra\Domain\Entity\CodigoQR;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CodigoQR::class)]
final class CodigoQREntityTest extends TestCase
{
    public function testCreateCodigoQR(): void
    {
        $qr = new CodigoQR(
            id: 1,
            nombre: 'QR-Doc-001',
            descripcion: 'Documento indicaciones post-operatorias',
        );

        $this->assertSame(1, $qr->getId());
        $this->assertSame('QR-Doc-001', $qr->getNombre());
        $this->assertSame('Documento indicaciones post-operatorias', $qr->getDescripcion());
    }

    public function testSetId(): void
    {
        $qr = new CodigoQR(id: null, nombre: 'QR-Test');
        $qr->setId(99);
        $this->assertSame(99, $qr->getId());
    }

    public function testDescripcionNullable(): void
    {
        $qr = new CodigoQR(id: 1, nombre: 'QR-Test');
        $this->assertNull($qr->getDescripcion());
    }

    public function testGenerarToken(): void
    {
        $token = CodigoQR::generarToken();
        $this->assertSame(64, strlen($token));
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $token);
    }

    public function testGenerarTokenUnico(): void
    {
        $t1 = CodigoQR::generarToken();
        $t2 = CodigoQR::generarToken();
        $this->assertNotSame($t1, $t2);
    }
}
