<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\Entity;

use Elyra\Domain\Entity\Pregunta;
use Elyra\Domain\ValueObject\TipoPregunta;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Pregunta::class)]
final class PreguntaTest extends TestCase
{
    public function testCreatePreguntaMultipleChoice(): void
    {
        $p = new Pregunta(
            id: 1,
            encuestaId: 1,
            tipo: new TipoPregunta('multiple_choice'),
            texto: '¿Cómo califica la atención?',
            orden: 0,
            opciones: ['Muy buena', 'Buena', 'Regular', 'Mala'],
            requerida: true,
        );

        $this->assertSame(1, $p->getId());
        $this->assertSame(1, $p->getEncuestaId());
        $this->assertSame('multiple_choice', $p->getTipo()->value());
        $this->assertSame('¿Cómo califica la atención?', $p->getTexto());
        $this->assertSame(0, $p->getOrden());
        $this->assertSame(['Muy buena', 'Buena', 'Regular', 'Mala'], $p->getOpciones());
        $this->assertTrue($p->isRequerida());
    }

    public function testCreatePreguntaTextoLibre(): void
    {
        $p = new Pregunta(
            id: 2,
            encuestaId: 1,
            tipo: new TipoPregunta('texto_libre'),
            texto: 'Comentarios adicionales',
            orden: 1,
            requerida: false,
        );

        $this->assertFalse($p->isRequerida());
        $this->assertNull($p->getOpciones());
    }

    public function testSetId(): void
    {
        $p = new Pregunta(
            id: null, encuestaId: 1, tipo: new TipoPregunta('texto_libre'),
            texto: 'Test', orden: 0,
        );
        $p->setId(99);
        $this->assertSame(99, $p->getId());
    }
}
