<?php

declare(strict_types=1);

namespace Elyra\Domain\ValueObject;

class EstadoTraslado
{
    private const VALIDOS = [
        'pendiente', 'en_curso', 'en_destino', 'en_retorno', 'completado', 'cancelado',
    ];

    private const TRANSICIONES = [
        'pendiente' => ['en_curso', 'cancelado'],
        'en_curso' => ['en_destino', 'cancelado'],
        'en_destino' => ['en_retorno', 'cancelado'],
        'en_retorno' => ['completado', 'cancelado'],
        'completado' => [],
        'cancelado' => [],
    ];

    private string $value;

    public function __construct(string $value)
    {
        $value = strtolower(trim($value));

        if (!in_array($value, self::VALIDOS, true)) {
            throw new \InvalidArgumentException(
                "Estado inválido: {$value}. Válidos: " . implode(', ', self::VALIDOS)
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

    public function puedeTransicionarA(self $nuevoEstado): bool
    {
        return in_array($nuevoEstado->value(), self::TRANSICIONES[$this->value] ?? [], true);
    }

    /** @return list<string> */
    public function transicionesPermitidas(): array
    {
        return self::TRANSICIONES[$this->value] ?? [];
    }

    public function esTerminal(): bool
    {
        return empty(self::TRANSICIONES[$this->value]);
    }

    public static function pendiente(): self
    {
        return new self('pendiente');
    }

    /** @return list<string> */
    public static function valores(): array
    {
        return self::VALIDOS;
    }
}
