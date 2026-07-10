<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\Entity;

use Elyra\Domain\Entity\HistorialEstado;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HistorialEstado::class)]
final class HistorialEstadoTest extends TestCase
{
    public function testCreateHistorial(): void
    {
        $h = new HistorialEstado(
            id: 1,
            trasladoId: 1,
            estadoNuevo: 'en_curso',
            actualizadoPor: 2,
            estadoAnterior: 'pendiente',
            observacion: 'Cambio manual',
        );

        $this->assertSame(1, $h->getId());
        $this->assertSame(1, $h->getTrasladoId());
        $this->assertSame('en_curso', $h->getEstadoNuevo());
        $this->assertSame('pendiente', $h->getEstadoAnterior());
        $this->assertSame(2, $h->getActualizadoPor());
        $this->assertSame('Cambio manual', $h->getObservacion());
    }

    public function testEstadoAnteriorObservacionNullables(): void
    {
        $h = new HistorialEstado(
            id: null,
            trasladoId: 1,
            estadoNuevo: 'pendiente',
            actualizadoPor: 1,
        );

        $this->assertNull($h->getEstadoAnterior());
        $this->assertNull($h->getObservacion());
    }

    public function testSetId(): void
    {
        $h = new HistorialEstado(
            id: null, trasladoId: 1, estadoNuevo: 'completado', actualizadoPor: 1,
        );
        $h->setId(42);
        $this->assertSame(42, $h->getId());
    }
}
