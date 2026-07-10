<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\Entity;

use Elyra\Domain\Entity\Traslado;
use Elyra\Domain\ValueObject\EstadoTraslado;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Traslado::class)]
final class TrasladoTest extends TestCase
{
    public function testCreateTraslado(): void
    {
        $traslado = new Traslado(
            id: null,
            codigo: 'TR-001',
            conductorId: 1,
            origen: 'Emergencias',
            destino: 'Cirugía',
            registradoPor: 2,
        );

        $this->assertNull($traslado->getId());
        $this->assertSame('TR-001', $traslado->getCodigo());
        $this->assertSame(1, $traslado->getConductorId());
        $this->assertSame('Emergencias', $traslado->getOrigen());
        $this->assertSame('Cirugía', $traslado->getDestino());
        $this->assertSame(2, $traslado->getRegistradoPor());
        $this->assertSame('pendiente', $traslado->getEstado()->value());
    }

    public function testSetId(): void
    {
        $traslado = new Traslado(
            id: null,
            codigo: 'TR-001',
            conductorId: 1,
            origen: 'A',
            destino: 'B',
            registradoPor: 1,
        );
        $traslado->setId(5);
        $this->assertSame(5, $traslado->getId());
    }

    public function testCreateWithFullData(): void
    {
        $traslado = new Traslado(
            id: 1,
            codigo: 'TR-001',
            conductorId: 1,
            origen: 'Emergencias',
            destino: 'Cirugía',
            registradoPor: 2,
            copilotoId: 3,
            vehiculoId: 1,
            rutaId: 2,
            horaSalidaEstimada: '2026-07-09 09:00:00',
            horaSalidaEfectiva: '2026-07-09 09:10:00',
            horaLlegadaDestino: '2026-07-09 09:30:00',
            horaInicioRetorno: '2026-07-09 10:00:00',
            horaLlegadaHospital: '2026-07-09 10:20:00',
            estado: 'en_curso',
            motivoCancelacion: null,
            observaciones: 'Paciente estable',
            createdAt: '2026-07-09 08:00:00',
            updatedAt: '2026-07-09 09:10:00',
        );

        $this->assertSame(1, $traslado->getId());
        $this->assertSame(3, $traslado->getCopilotoId());
        $this->assertSame(1, $traslado->getVehiculoId());
        $this->assertSame(2, $traslado->getRutaId());
        $this->assertSame('2026-07-09 09:00:00', $traslado->getHoraSalidaEstimada());
        $this->assertSame('en_curso', $traslado->getEstado()->value());
        $this->assertSame('Paciente estable', $traslado->getObservaciones());
    }

    public function testActualizarEstadoValido(): void
    {
        $traslado = new Traslado(
            id: 1, codigo: 'TR-001', conductorId: 1,
            origen: 'A', destino: 'B', registradoPor: 1,
        );

        $traslado->actualizarEstado(new EstadoTraslado('en_curso'));
        $this->assertSame('en_curso', $traslado->getEstado()->value());

        $traslado->actualizarEstado(new EstadoTraslado('en_destino'));
        $this->assertSame('en_destino', $traslado->getEstado()->value());
    }

    public function testActualizarEstadoInvalidoLanzaExcepcion(): void
    {
        $traslado = new Traslado(
            id: 1, codigo: 'TR-001', conductorId: 1,
            origen: 'A', destino: 'B', registradoPor: 1,
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('No se puede cambiar de pendiente a completado');
        $traslado->actualizarEstado(new EstadoTraslado('completado'));
    }

    public function testCancelarRequiereMotivo(): void
    {
        $traslado = new Traslado(
            id: 1, codigo: 'TR-001', conductorId: 1,
            origen: 'A', destino: 'B', registradoPor: 1,
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Motivo de cancelación requerido');
        $traslado->actualizarEstado(new EstadoTraslado('cancelado'));
    }

    public function testCancelarConMotivo(): void
    {
        $traslado = new Traslado(
            id: 1, codigo: 'TR-001', conductorId: 1,
            origen: 'A', destino: 'B', registradoPor: 1,
        );

        $traslado->actualizarEstado(new EstadoTraslado('cancelado'), 'Falta de conductor');
        $this->assertSame('cancelado', $traslado->getEstado()->value());
        $this->assertSame('Falta de conductor', $traslado->getMotivoCancelacion());
    }

    #[DataProvider('fullCycleProvider')]
    public function testFullCycleSinCancelar(array $estados): void
    {
        $traslado = new Traslado(
            id: 1, codigo: 'TR-001', conductorId: 1,
            origen: 'A', destino: 'B', registradoPor: 1,
        );

        foreach ($estados as $estado) {
            $traslado->actualizarEstado(new EstadoTraslado($estado));
            $this->assertSame($estado, $traslado->getEstado()->value());
        }
    }

    public static function fullCycleProvider(): array
    {
        return [
            'ciclo completo' => [['en_curso', 'en_destino', 'en_retorno', 'completado']],
        ];
    }

    public function testCancelarDesdePendiente(): void
    {
        $traslado = new Traslado(
            id: 1, codigo: 'TR-001', conductorId: 1,
            origen: 'A', destino: 'B', registradoPor: 1,
        );

        $traslado->actualizarEstado(new EstadoTraslado('cancelado'), 'Motivo válido');
        $this->assertSame('cancelado', $traslado->getEstado()->value());
        $this->assertSame('Motivo válido', $traslado->getMotivoCancelacion());
    }

    public function testCancelarDesdeEnCurso(): void
    {
        $traslado = new Traslado(
            id: 1, codigo: 'TR-001', conductorId: 1,
            origen: 'A', destino: 'B', registradoPor: 1,
        );

        $traslado->actualizarEstado(new EstadoTraslado('en_curso'));
        $traslado->actualizarEstado(new EstadoTraslado('cancelado'), 'Problemas mecánicos');
        $this->assertSame('cancelado', $traslado->getEstado()->value());
        $this->assertSame('Problemas mecánicos', $traslado->getMotivoCancelacion());
    }

    public function testEstadoPendientePorDefecto(): void
    {
        $traslado = new Traslado(
            id: null, codigo: 'TR-001', conductorId: 1,
            origen: 'A', destino: 'B', registradoPor: 1,
        );
        $this->assertSame('pendiente', $traslado->getEstado()->value());
    }

    public function testCopilotoVehiculoRutaNullables(): void
    {
        $traslado = new Traslado(
            id: null, codigo: 'TR-001', conductorId: 1,
            origen: 'A', destino: 'B', registradoPor: 1,
        );

        $this->assertNull($traslado->getCopilotoId());
        $this->assertNull($traslado->getVehiculoId());
        $this->assertNull($traslado->getRutaId());
    }
}
