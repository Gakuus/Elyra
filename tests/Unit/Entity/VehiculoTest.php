<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\Entity;

use Elyra\Domain\Entity\Vehiculo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Vehiculo::class)]
final class VehiculoTest extends TestCase
{
    public function testCreateVehiculo(): void
    {
        $v = new Vehiculo(
            id: 1,
            patente: 'ABC1234',
            modelo: 'Toyota Hiace',
            anio: '2020',
        );

        $this->assertSame(1, $v->getId());
        $this->assertSame('ABC1234', $v->getPatente());
        $this->assertSame('Toyota Hiace', $v->getModelo());
        $this->assertSame('2020', $v->getAnio());
    }

    public function testSetId(): void
    {
        $v = new Vehiculo(id: null, patente: 'DEF5678');
        $v->setId(42);
        $this->assertSame(42, $v->getId());
    }

    public function testModeloAnioNullables(): void
    {
        $v = new Vehiculo(id: 1, patente: 'GHI9012');
        $this->assertNull($v->getModelo());
        $this->assertNull($v->getAnio());
    }
}
