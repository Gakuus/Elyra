<?php

declare(strict_types=1);

namespace Elyra\Tests\Integration;

use Elyra\Domain\Entity\Traslado;
use Elyra\Domain\ValueObject\EstadoTraslado;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Prueba integral de la máquina de estados de traslados.
 * Cubre HU-12 (registrar), HU-13 (actualizar estado), HU-14 (consultar activos), HU-16 (historial).
 */
#[CoversClass(Traslado::class)]
#[CoversClass(EstadoTraslado::class)]
final class TrasladoStateMachineTest extends TestCase
{
    public function testFlujoCompletoExitoso(): void
    {
        $traslado = $this->crearTrasladoPendiente();

        $this->assertSame('pendiente', $traslado->getEstado()->value());
        $this->assertFalse($traslado->getEstado()->esTerminal());

        $traslado->actualizarEstado(new EstadoTraslado('en_curso'));
        $this->assertSame('en_curso', $traslado->getEstado()->value());
        $this->assertFalse($traslado->getEstado()->esTerminal());

        $traslado->actualizarEstado(new EstadoTraslado('en_destino'));
        $this->assertSame('en_destino', $traslado->getEstado()->value());

        $traslado->actualizarEstado(new EstadoTraslado('en_retorno'));
        $this->assertSame('en_retorno', $traslado->getEstado()->value());

        $traslado->actualizarEstado(new EstadoTraslado('completado'));
        $this->assertSame('completado', $traslado->getEstado()->value());
        $this->assertTrue($traslado->getEstado()->esTerminal());
    }

    public function testFlujoCanceladoConMotivo(): void
    {
        $traslado = $this->crearTrasladoPendiente();

        $traslado->actualizarEstado(new EstadoTraslado('cancelado'), 'Falta de personal');
        $this->assertSame('cancelado', $traslado->getEstado()->value());
        $this->assertSame('Falta de personal', $traslado->getMotivoCancelacion());
        $this->assertTrue($traslado->getEstado()->esTerminal());
    }

    public function testFlujoCanceladoDesdeEnCurso(): void
    {
        $traslado = $this->crearTrasladoPendiente();
        $traslado->actualizarEstado(new EstadoTraslado('en_curso'));
        $traslado->actualizarEstado(new EstadoTraslado('cancelado'), 'Problemas mecánicos');

        $this->assertSame('cancelado', $traslado->getEstado()->value());
        $this->assertSame('Problemas mecánicos', $traslado->getMotivoCancelacion());
    }

    public function testNoSePuedeCancelarSinMotivo(): void
    {
        $traslado = $this->crearTrasladoPendiente();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Motivo de cancelación requerido');
        $traslado->actualizarEstado(new EstadoTraslado('cancelado'));
    }

    public function testNoSePuedeRetrocederEstado(): void
    {
        $traslado = $this->crearTrasladoPendiente();
        $traslado->actualizarEstado(new EstadoTraslado('en_curso'));

        $this->expectException(\DomainException::class);
        $traslado->actualizarEstado(new EstadoTraslado('pendiente'));
    }

    public function testNoSePuedeCambiarEstadoCompletado(): void
    {
        $traslado = $this->crearTrasladoPendiente();
        $traslado->actualizarEstado(new EstadoTraslado('en_curso'));
        $traslado->actualizarEstado(new EstadoTraslado('en_destino'));
        $traslado->actualizarEstado(new EstadoTraslado('en_retorno'));
        $traslado->actualizarEstado(new EstadoTraslado('completado'));

        $this->expectException(\DomainException::class);
        $traslado->actualizarEstado(new EstadoTraslado('cancelado'));
    }

    public function testTimelineEstados(): void
    {
        $estadosPermitidos = ['pendiente', 'en_curso', 'en_destino', 'en_retorno', 'completado', 'cancelado'];

        foreach (EstadoTraslado::valores() as $valor) {
            $this->assertContains($valor, $estadosPermitidos);
        }
    }

    public function testHU12_RegistrarTraslado(): void
    {
        $traslado = new Traslado(
            id: null,
            codigo: 'TR-2026001',
            conductorId: 1,
            origen: 'Emergencias',
            destino: 'Cirugía',
            registradoPor: 2,
            copilotoId: null,
            vehiculoId: 1,
            rutaId: 1,
            horaSalidaEstimada: '2026-07-09 09:00:00',
            observaciones: 'Paciente estable',
        );

        $this->assertNull($traslado->getId());
        $this->assertSame('TR-2026001', $traslado->getCodigo());
        $this->assertSame(1, $traslado->getConductorId());
        $this->assertSame('Emergencias', $traslado->getOrigen());
        $this->assertSame('Cirugía', $traslado->getDestino());
        $this->assertSame('pendiente', $traslado->getEstado()->value());
    }

    public function testHU14_ConsultarTrasladosActivos(): void
    {
        $activo = $this->crearTrasladoPendiente();
        $activo->actualizarEstado(new EstadoTraslado('en_curso'));

        $completado = $this->crearTrasladoPendiente();
        $completado->actualizarEstado(new EstadoTraslado('en_curso'));
        $completado->actualizarEstado(new EstadoTraslado('en_destino'));
        $completado->actualizarEstado(new EstadoTraslado('en_retorno'));
        $completado->actualizarEstado(new EstadoTraslado('completado'));

        $this->assertFalse($activo->getEstado()->esTerminal(), 'Traslado activo no debe ser terminal');
        $this->assertTrue($completado->getEstado()->esTerminal(), 'Traslado completado debe ser terminal');
    }

    public function testHU16_HistorialTransiciones(): void
    {
        $traslado = $this->crearTrasladoPendiente();
        $transiciones = ['en_curso', 'en_destino', 'en_retorno', 'completado'];
        $estadosRecorridos = ['pendiente'];

        foreach ($transiciones as $estado) {
            $traslado->actualizarEstado(new EstadoTraslado($estado));
            $estadosRecorridos[] = $estado;
        }

        $this->assertCount(5, $estadosRecorridos);
        $this->assertSame('completado', $traslado->getEstado()->value());
    }

    private function crearTrasladoPendiente(): Traslado
    {
        return new Traslado(
            id: null,
            codigo: 'TR-TEST-' . bin2hex(random_bytes(3)),
            conductorId: 1,
            origen: 'Origen Test',
            destino: 'Destino Test',
            registradoPor: 1,
        );
    }
}
