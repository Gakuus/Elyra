<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\ValueObject;

use Elyra\Domain\ValueObject\TipoPregunta;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TipoPregunta::class)]
final class TipoPreguntaTest extends TestCase
{
    #[DataProvider('validTipoProvider')]
    public function testValidTipo(string $value): void
    {
        $tipo = new TipoPregunta($value);
        $this->assertSame($value, $tipo->value());
    }

    public static function validTipoProvider(): array
    {
        return [
            'multiple_choice' => ['multiple_choice'],
            'escala' => ['escala'],
            'texto_libre' => ['texto_libre'],
        ];
    }

    public function testInvalidTipoThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new TipoPregunta('checkbox');
    }

    public function testEquals(): void
    {
        $a = new TipoPregunta('texto_libre');
        $b = new TipoPregunta('texto_libre');
        $c = new TipoPregunta('escala');

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    public function testToString(): void
    {
        $tipo = new TipoPregunta('multiple_choice');
        $this->assertSame('multiple_choice', (string) $tipo);
    }
}
