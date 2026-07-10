<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\Entity;

use Elyra\Domain\Entity\Encuesta;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Encuesta::class)]
final class EncuestaTest extends TestCase
{
    public function testCreateEncuesta(): void
    {
        $e = new Encuesta(
            id: 1,
            titulo: 'Encuesta de Satisfacción',
            creadaPor: 1,
            descripcion: 'Evalúe su experiencia',
            activa: true,
        );

        $this->assertSame(1, $e->getId());
        $this->assertSame('Encuesta de Satisfacción', $e->getTitulo());
        $this->assertSame(1, $e->getCreadaPor());
        $this->assertSame('Evalúe su experiencia', $e->getDescripcion());
        $this->assertTrue($e->isActiva());
    }

    public function testSetId(): void
    {
        $e = new Encuesta(id: null, titulo: 'Test', creadaPor: 1);
        $e->setId(10);
        $this->assertSame(10, $e->getId());
    }

    public function testDescripcionNullable(): void
    {
        $e = new Encuesta(id: 1, titulo: 'Test', creadaPor: 1);
        $this->assertNull($e->getDescripcion());
    }

    public function testActivaDefaultTrue(): void
    {
        $e = new Encuesta(id: 1, titulo: 'Test', creadaPor: 1, activa: true);
        $this->assertTrue($e->isActiva());
    }
}
