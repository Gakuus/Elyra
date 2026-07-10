<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\Entity;

use Elyra\Domain\Entity\Categoria;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Categoria::class)]
final class CategoriaTest extends TestCase
{
    public function testCreateCategoria(): void
    {
        $c = new Categoria(
            id: 1,
            nombre: 'Cardiología',
            descripcion: 'Documentos del área de cardiología',
            tipo: 'especialidad',
        );

        $this->assertSame(1, $c->getId());
        $this->assertSame('Cardiología', $c->getNombre());
        $this->assertSame('Documentos del área de cardiología', $c->getDescripcion());
        $this->assertSame('especialidad', $c->getTipo());
    }

    public function testTipoDefaultTipoDocumento(): void
    {
        $c = new Categoria(id: null, nombre: 'General');
        $this->assertSame('tipo_documento', $c->getTipo());
    }

    public function testSetId(): void
    {
        $c = new Categoria(id: null, nombre: 'Test');
        $c->setId(5);
        $this->assertSame(5, $c->getId());
    }
}
