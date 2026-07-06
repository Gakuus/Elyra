<?php

declare(strict_types=1);

namespace Elyra\Domain\ValueObject;

class RolUsuario
{
    private const VALIDOS = ['admin', 'superadmin', 'conductor'];

    private string $value;

    public function __construct(string $value)
    {
        $value = strtolower(trim($value));

        if (!in_array($value, self::VALIDOS, true)) {
            throw new \InvalidArgumentException(
                "Rol inválido: {$value}. Válidos: " . implode(', ', self::VALIDOS)
            );
        }

        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function esAdmin(): bool
    {
        return in_array($this->value, ['admin', 'superadmin'], true);
    }

    public function esSuperadmin(): bool
    {
        return $this->value === 'superadmin';
    }

    public function esConductor(): bool
    {
        return $this->value === 'conductor';
    }

    public static function valores(): array
    {
        return self::VALIDOS;
    }
}
