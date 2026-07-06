<?php

declare(strict_types=1);

namespace Elyra\Domain\ValueObject;

class Email
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Email inválido: {$value}");
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
}
