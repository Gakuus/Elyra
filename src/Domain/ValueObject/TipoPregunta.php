<?php

declare(strict_types=1);

namespace Elyra\Domain\ValueObject;

class TipoPregunta
{
    private const VALIDOS = ['multiple_choice', 'escala', 'texto_libre'];

    private string $value;

    public function __construct(string $value)
    {
        $value = strtolower(trim($value));

        if (!in_array($value, self::VALIDOS, true)) {
            throw new \InvalidArgumentException(
                "Tipo de pregunta inválido: {$value}. Válidos: " . implode(', ', self::VALIDOS)
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

    public function requiereOpciones(): bool
    {
        return $this->value === 'multiple_choice';
    }

    public static function valores(): array
    {
        return self::VALIDOS;
    }
}
