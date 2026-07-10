<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\ValueObject;

use Elyra\Domain\ValueObject\Email;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Email::class)]
final class EmailTest extends TestCase
{
    public function testValidEmail(): void
    {
        $email = new Email('test@example.com');
        $this->assertSame('test@example.com', $email->value());
    }

    public function testEmailWithPlusSign(): void
    {
        $email = new Email('user+tag@example.com');
        $this->assertSame('user+tag@example.com', $email->value());
    }

    public function testEmailWithSubdomain(): void
    {
        $email = new Email('user@sub.example.com');
        $this->assertSame('user@sub.example.com', $email->value());
    }

    #[DataProvider('invalidEmailProvider')]
    public function testInvalidEmailThrowsException(string $invalidEmail): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Email($invalidEmail);
    }

    /** @return array<string, array{string}> */
    public static function invalidEmailProvider(): array
    {
        return [
            'sin arroba' => ['invalido'],
            'sin dominio' => ['usuario@'],
            'sin usuario' => ['@dominio.com'],
            'doble arroba' => ['user@domain@example.com'],
            'espacios' => ['user @example.com'],
            'vacío' => [''],
            'solo puntos' => ['...@example.com'],
        ];
    }

    public function testEquals(): void
    {
        $a = new Email('same@example.com');
        $b = new Email('same@example.com');
        $c = new Email('other@example.com');

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    public function testToString(): void
    {
        $email = new Email('test@example.com');
        $this->assertSame('test@example.com', (string) $email);
    }
}
