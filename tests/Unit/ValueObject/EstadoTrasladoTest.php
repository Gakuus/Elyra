<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\ValueObject;

use Elyra\Domain\ValueObject\EstadoTraslado;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(EstadoTraslado::class)]
final class EstadoTrasladoTest extends TestCase
{
    #[DataProvider('validEstadoProvider')]
    public function testValidEstado(string $value): void
    {
        $estado = new EstadoTraslado($value);
        $this->assertSame($value, $estado->value());
    }

    /** @return array<string, array{string}> */
    public static function validEstadoProvider(): array
    {
        return [
            'pendiente' => ['pendiente'],
            'en_curso' => ['en_curso'],
            'en_destino' => ['en_destino'],
            'en_retorno' => ['en_retorno'],
            'completado' => ['completado'],
            'cancelado' => ['cancelado'],
        ];
    }

    #[DataProvider('invalidEstadoProvider')]
    public function testInvalidEstadoThrowsException(string $invalidValue): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new EstadoTraslado($invalidValue);
    }

    /** @return array<string, array{string}> */
    public static function invalidEstadoProvider(): array
    {
        return [
            'vacío' => [''],
            'inventado' => ['en_espera'],
            'número' => ['123'],
            'inexistente' => ['inexistente'],
        ];
    }

    public function testCaseInsensitive(): void
    {
        $estado = new EstadoTraslado('PENDIENTE');
        $this->assertSame('pendiente', $estado->value());
    }

    #[DataProvider('transicionProvider')]
    public function testTransicionesValidas(string $de, string $a, bool $deberiaPoder): void
    {
        $desde = new EstadoTraslado($de);
        $hasta = new EstadoTraslado($a);

        $this->assertSame($deberiaPoder, $desde->puedeTransicionarA($hasta));
    }

    /** @return array<string, array{string, string, bool}> */
    public static function transicionProvider(): array
    {
        return [
            'pendiente → en_curso' => ['pendiente', 'en_curso', true],
            'pendiente → cancelado' => ['pendiente', 'cancelado', true],
            'pendiente → completado (invalido)' => ['pendiente', 'completado', false],
            'en_curso → en_destino' => ['en_curso', 'en_destino', true],
            'en_curso → cancelado' => ['en_curso', 'cancelado', true],
            'en_curso → pendiente (retroceder)' => ['en_curso', 'pendiente', false],
            'en_destino → en_retorno' => ['en_destino', 'en_retorno', true],
            'en_destino → cancelado' => ['en_destino', 'cancelado', true],
            'en_retorno → completado' => ['en_retorno', 'completado', true],
            'en_retorno → cancelado' => ['en_retorno', 'cancelado', true],
            'completado → cualquiera (terminal)' => ['completado', 'pendiente', false],
            'cancelado → cualquiera (terminal)' => ['cancelado', 'en_curso', false],
        ];
    }

    public function testTransicionesPermitidas(): void
    {
        $pendiente = new EstadoTraslado('pendiente');
        $this->assertSame(['en_curso', 'cancelado'], $pendiente->transicionesPermitidas());

        $completado = new EstadoTraslado('completado');
        $this->assertSame([], $completado->transicionesPermitidas());
    }

    #[DataProvider('terminalProvider')]
    public function testEstadosTerminales(string $estado, bool $esTerminal): void
    {
        $e = new EstadoTraslado($estado);
        $this->assertSame($esTerminal, $e->esTerminal());
    }

    /** @return array<string, array{string, bool}> */
    public static function terminalProvider(): array
    {
        return [
            'pendiente no terminal' => ['pendiente', false],
            'en_curso no terminal' => ['en_curso', false],
            'completado terminal' => ['completado', true],
            'cancelado terminal' => ['cancelado', true],
        ];
    }

    public function testPendienteStaticFactory(): void
    {
        $this->assertSame('pendiente', EstadoTraslado::pendiente()->value());
    }

    public function testValores(): void
    {
        $this->assertSame(
            ['pendiente', 'en_curso', 'en_destino', 'en_retorno', 'completado', 'cancelado'],
            EstadoTraslado::valores()
        );
    }

    public function testEquals(): void
    {
        $a = new EstadoTraslado('pendiente');
        $b = new EstadoTraslado('pendiente');
        $c = new EstadoTraslado('en_curso');

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    public function testToString(): void
    {
        $estado = new EstadoTraslado('en_curso');
        $this->assertSame('en_curso', (string) $estado);
    }
}
