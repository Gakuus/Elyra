<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\Service;

use Elyra\Infrastructure\Service\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Validator::class)]
final class ValidatorTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        $this->validator = new Validator();
    }

    public function testRequiredPasses(): void
    {
        $this->validator->required('nombre', 'Juan');
        $this->assertTrue($this->validator->isValid());
    }

    public function testRequiredFailsNull(): void
    {
        $this->validator->required('campo', null);
        $this->assertFalse($this->validator->isValid());
        $this->assertStringContainsString('es requerido', $this->validator->getErrors()['campo'][0]);
    }

    public function testRequiredFailsEmptyString(): void
    {
        $this->validator->required('campo', '');
        $this->assertFalse($this->validator->isValid());
        $this->assertStringContainsString('es requerido', $this->validator->getErrors()['campo'][0]);
    }

    public function testRequiredPassesForSpaces(): void
    {
        $this->validator->required('campo', '   ');
        $this->assertTrue($this->validator->isValid());
    }

    public function testEmailPasses(): void
    {
        $this->validator->email('email', 'user@example.com');
        $this->assertTrue($this->validator->isValid());
    }

    public function testEmailFails(): void
    {
        $this->validator->email('email', 'not-an-email');
        $this->assertFalse($this->validator->isValid());
    }

    public function testEmailAcceptsNull(): void
    {
        $this->validator->email('email', null);
        $this->assertTrue($this->validator->isValid());
    }

    public function testMinLengthPasses(): void
    {
        $this->validator->minLength('nombre', 'Juan Pérez', 3);
        $this->assertTrue($this->validator->isValid());
    }

    public function testMinLengthFails(): void
    {
        $this->validator->minLength('nombre', 'ab', 3);
        $this->assertFalse($this->validator->isValid());
    }

    public function testMinLengthAcceptsNull(): void
    {
        $this->validator->minLength('nombre', null, 3);
        $this->assertTrue($this->validator->isValid());
    }

    public function testMaxLengthPasses(): void
    {
        $this->validator->maxLength('nombre', 'Juan', 100);
        $this->assertTrue($this->validator->isValid());
    }

    public function testMaxLengthFails(): void
    {
        $this->validator->maxLength('nombre', str_repeat('a', 101), 100);
        $this->assertFalse($this->validator->isValid());
    }

    public function testMultipleValidations(): void
    {
        $this->validator
            ->required('nombre', 'Juan')
            ->required('email', 'juan@example.com')
            ->email('email', 'juan@example.com');

        $this->assertTrue($this->validator->isValid());
        $this->assertEmpty($this->validator->getErrors());
    }

    public function testMultipleErrors(): void
    {
        $this->validator
            ->required('nombre', '')
            ->required('email', '')
            ->email('email', 'invalid');

        $this->assertFalse($this->validator->isValid());
        $errors = $this->validator->getErrors();
        $this->assertArrayHasKey('nombre', $errors);
        $this->assertArrayHasKey('email', $errors);
    }

    public function testResetErrors(): void
    {
        $this->validator->required('nombre', '');
        $this->assertFalse($this->validator->isValid());

        $this->validator->reset();
        $this->assertTrue($this->validator->isValid());
        $this->assertEmpty($this->validator->getErrors());
    }

    public function testNumericPasses(): void
    {
        $this->validator->numeric('edad', 25);
        $this->assertTrue($this->validator->isValid());
    }

    public function testNumericFailsString(): void
    {
        $this->validator->numeric('edad', 'abc');
        $this->assertFalse($this->validator->isValid());
    }

    public function testNumericPassesForNull(): void
    {
        $this->validator->numeric('edad', null);
        $this->assertTrue($this->validator->isValid());
    }

    public function testNumericPassesForEmptyString(): void
    {
        $this->validator->numeric('edad', '');
        $this->assertTrue($this->validator->isValid());
    }

    public function testInArrayPasses(): void
    {
        $this->validator->inArray('rol', 'admin', ['admin', 'user']);
        $this->assertTrue($this->validator->isValid());
    }

    public function testInArrayFails(): void
    {
        $this->validator->inArray('rol', 'supervisor', ['admin', 'user']);
        $this->assertFalse($this->validator->isValid());
    }
}
