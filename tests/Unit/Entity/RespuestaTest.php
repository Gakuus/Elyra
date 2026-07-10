<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\Entity;

use Elyra\Domain\Entity\Respuesta;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Respuesta::class)]
final class RespuestaTest extends TestCase
{
    public function testCreateRespuestaMultipleChoice(): void
    {
        $r = new Respuesta(
            id: 1,
            sesionToken: 'sess-abc-123',
            encuestaId: 1,
            preguntaId: 1,
            tokenPaciente: null,
            valorOpcion: 2,
        );

        $this->assertSame(1, $r->getId());
        $this->assertSame('sess-abc-123', $r->getSesionToken());
        $this->assertSame(1, $r->getEncuestaId());
        $this->assertSame(1, $r->getPreguntaId());
        $this->assertNull($r->getTokenPaciente());
        $this->assertSame(2, $r->getValorOpcion());
    }

    public function testCreateRespuestaEscala(): void
    {
        $r = new Respuesta(
            id: 2,
            sesionToken: 'sess-xyz-789',
            encuestaId: 1,
            preguntaId: 2,
            valorNumerico: 4,
        );

        $this->assertSame(4, $r->getValorNumerico());
    }

    public function testCreateRespuestaTextoLibre(): void
    {
        $r = new Respuesta(
            id: 3,
            sesionToken: 'sess-def-456',
            encuestaId: 1,
            preguntaId: 3,
            valorTexto: 'Excelente atención',
        );

        $this->assertSame('Excelente atención', $r->getValorTexto());
    }

    public function testSetId(): void
    {
        $r = new Respuesta(
            id: null, sesionToken: 'sess-1', encuestaId: 1, preguntaId: 1,
        );
        $r->setId(50);
        $this->assertSame(50, $r->getId());
    }

    public function testTokenPacienteNullable(): void
    {
        $r = new Respuesta(
            id: 1, sesionToken: 'sess-1', encuestaId: 1, preguntaId: 1,
            tokenPaciente: 'token-paciente-abc',
        );
        $this->assertSame('token-paciente-abc', $r->getTokenPaciente());
    }
}
