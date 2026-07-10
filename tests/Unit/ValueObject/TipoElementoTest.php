<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\ValueObject;

use Elyra\Domain\ValueObject\TipoElemento;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TipoElemento::class)]
final class TipoElementoTest extends TestCase
{
    #[DataProvider('validTipoProvider')]
    public function testValidTipo(string $value): void
    {
        $tipo = new TipoElemento($value);
        $this->assertSame($value, $tipo->value());
    }

    public static function validTipoProvider(): array
    {
        return [
            'paciente' => ['paciente'],
            'equipamiento' => ['equipamiento'],
            'insumo' => ['insumo'],
        ];
    }

    public function testInvalidTipoThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new TipoElemento('pieza');
    }

    public function testEquals(): void
    {
        $a = new TipoElemento('paciente');
        $b = new TipoElemento('paciente');
        $c = new TipoElemento('equipamiento');

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    public function testToString(): void
    {
        $tipo = new TipoElemento('insumo');
        $this->assertSame('insumo', (string) $tipo);
    }
}
