<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\ValueObject;

use Elyra\Domain\ValueObject\CodigoQR;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CodigoQR::class)]
final class CodigoQRTest extends TestCase
{
    public function testGeneratesValidToken(): void
    {
        $qr = new CodigoQR();
        $value = $qr->value();

        $this->assertSame(64, strlen($value));
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $value);
    }

    public function testGeneratesUniqueValues(): void
    {
        $qr1 = new CodigoQR();
        $qr2 = new CodigoQR();
        $this->assertNotSame($qr1->value(), $qr2->value());
    }

    public function testToString(): void
    {
        $qr = new CodigoQR();
        $this->assertSame($qr->value(), (string) $qr);
    }

    public function testLength(): void
    {
        $qr = new CodigoQR();
        $this->assertSame(64, strlen($qr->value()));
    }
}
