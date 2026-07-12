<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\ValueObject;

use Elyra\Domain\ValueObject\RolUsuario;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(RolUsuario::class)]
final class RolUsuarioTest extends TestCase
{
    #[DataProvider('validRolProvider')]
    public function testValidRol(string $value): void
    {
        $rol = new RolUsuario($value);
        $this->assertSame($value, $rol->value());
    }

    /** @return array<string, array{string}> */
    public static function validRolProvider(): array
    {
        return [
            'admin' => ['admin'],
            'superadmin' => ['superadmin'],
            'conductor' => ['conductor'],
            'copiloto' => ['copiloto'],
        ];
    }

    #[DataProvider('invalidRolProvider')]
    public function testInvalidRolThrowsException(string $invalidValue): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new RolUsuario($invalidValue);
    }

    /** @return array<string, array{string}> */
    public static function invalidRolProvider(): array
    {
        return [
            'paciente' => ['paciente'],
            'vacio' => [''],
            'inventado' => ['supervisor'],
        ];
    }

    #[DataProvider('roleCheckProvider')]
    public function testRoleChecks(string $rol, bool $esAdmin, bool $esSuperadmin, bool $esConductor): void
    {
        $r = new RolUsuario($rol);
        $this->assertSame($esAdmin, $r->esAdmin());
        $this->assertSame($esSuperadmin, $r->esSuperadmin());
        $this->assertSame($esConductor, $r->esConductor());
    }

    /** @return array<string, array{string, bool, bool, bool}> */
    public static function roleCheckProvider(): array
    {
        return [
            'admin' => ['admin', true, false, false],
            'superadmin' => ['superadmin', true, true, false],
            'conductor' => ['conductor', false, false, true],
            'copiloto' => ['copiloto', false, false, false],
        ];
    }

    public function testEquals(): void
    {
        $a = new RolUsuario('admin');
        $b = new RolUsuario('admin');
        $c = new RolUsuario('superadmin');

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    public function testToString(): void
    {
        $rol = new RolUsuario('conductor');
        $this->assertSame('conductor', (string) $rol);
    }

    public function testValores(): void
    {
        $this->assertSame(['admin', 'superadmin', 'conductor', 'copiloto'], RolUsuario::valores());
    }
}
