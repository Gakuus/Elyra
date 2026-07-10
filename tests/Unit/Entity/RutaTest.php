<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\Entity;

use Elyra\Domain\Entity\Ruta;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Ruta::class)]
final class RutaTest extends TestCase
{
    public function testCreateRuta(): void
    {
        $r = new Ruta(
            id: 1,
            nombre: 'Ruta 1',
            origen: 'Hospital de Clínicas',
            destino: 'Sanatorio Español',
            distanciaKm: 5.5,
            descripcion: 'Ruta directa por Av. Italia',
        );

        $this->assertSame(1, $r->getId());
        $this->assertSame('Ruta 1', $r->getNombre());
        $this->assertSame('Hospital de Clínicas', $r->getOrigen());
        $this->assertSame('Sanatorio Español', $r->getDestino());
        $this->assertSame(5.5, $r->getDistanciaKm());
        $this->assertSame('Ruta directa por Av. Italia', $r->getDescripcion());
    }

    public function testSetId(): void
    {
        $r = new Ruta(id: null, nombre: 'Ruta 2', origen: 'A', destino: 'B');
        $r->setId(10);
        $this->assertSame(10, $r->getId());
    }

    public function testDistanciaDescripcionNullables(): void
    {
        $r = new Ruta(id: 1, nombre: 'Ruta X', origen: 'A', destino: 'B');
        $this->assertNull($r->getDistanciaKm());
        $this->assertNull($r->getDescripcion());
    }
}
