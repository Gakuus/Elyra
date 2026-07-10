<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\Entity;

use Elyra\Domain\Entity\ElementoTraslado;
use Elyra\Domain\Entity\Paciente;
use Elyra\Domain\ValueObject\TipoElemento;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ElementoTraslado::class)]
final class ElementoTrasladoTest extends TestCase
{
    public function testCreateElementoPaciente(): void
    {
        $e = new ElementoTraslado(
            id: 1,
            trasladoId: 1,
            tipo: new TipoElemento('paciente'),
            cantidad: 1,
            pacienteId: 5,
            descripcion: 'Juan Pérez - Estable',
        );

        $this->assertSame(1, $e->getId());
        $this->assertSame(1, $e->getTrasladoId());
        $this->assertSame('paciente', $e->getTipo()->value());
        $this->assertSame(1, $e->getCantidad());
        $this->assertSame(5, $e->getPacienteId());
        $this->assertSame('Juan Pérez - Estable', $e->getDescripcion());
    }

    public function testCreateElementoEquipamiento(): void
    {
        $e = new ElementoTraslado(
            id: 2,
            trasladoId: 1,
            tipo: new TipoElemento('equipamiento'),
            cantidad: 3,
            descripcion: 'Ventilador portátil',
        );

        $this->assertSame('equipamiento', $e->getTipo()->value());
        $this->assertSame(3, $e->getCantidad());
        $this->assertNull($e->getPacienteId());
    }

    public function testCantidadDefaultUno(): void
    {
        $e = new ElementoTraslado(
            id: null,
            trasladoId: 1,
            tipo: new TipoElemento('insumo'),
        );

        $this->assertSame(1, $e->getCantidad());
    }

    public function testSetId(): void
    {
        $e = new ElementoTraslado(
            id: null, trasladoId: 1, tipo: new TipoElemento('insumo'),
        );
        $e->setId(99);
        $this->assertSame(99, $e->getId());
    }
}
