<?php

declare(strict_types=1);

namespace Elyra\Domain\ValueObject;

/**
 * Categorías de licencia de conducir relevantes para el ámbito hospitalario.
 * Basado en la normativa uruguaya (Ministerio de Transporte y Obras Públicas).
 */
enum CategoriaLicenciaConducir: string
{
    /** Automóviles y ambulancias tipo van (hasta 3.500 kg) */
    case B1 = 'B1';

    /** Camionetas y utilitarios (hasta 3.500 kg, remolque hasta 750 kg) */
    case B2 = 'B2';

    /** Camiones livianos (hasta 3.500 kg) */
    case C1 = 'C1';

    /** Camiones pesados (más de 3.500 kg) */
    case C2 = 'C2';

    /** Microbuses (hasta 8 pasajeros) */
    case D1 = 'D1';

    /** Ómnibus (más de 8 pasajeros) */
    case D2 = 'D2';

    /**
     * Descripción legible de cada categoría.
     */
    public function descripcion(): string
    {
        return match ($this) {
            self::B1 => 'Automóviles y ambulancias (van hasta 3.500 kg)',
            self::B2 => 'Camionetas y utilitarios (hasta 3.500 kg)',
            self::C1 => 'Camiones livianos (hasta 3.500 kg)',
            self::C2 => 'Camiones pesados (más de 3.500 kg)',
            self::D1 => 'Microbuses (hasta 8 pasajeros)',
            self::D2 => 'Ómnibus (más de 8 pasajeros)',
        };
    }

    /**
     * Label para formularios: "B1 — Automóviles y ambulancias (van hasta 3.500 kg)".
     */
    public function label(): string
    {
        return $this->value . ' — ' . $this->descripcion();
    }

    /**
     * Retorna todas las categorías como array [codigo => descripcion].
     *
     * @return array<string, string>
     */
    public static function todas(): array
    {
        $resultado = [];
        foreach (self::cases() as $cat) {
            $resultado[$cat->value] = $cat->descripcion();
        }
        return $resultado;
    }

    /**
     * Valida que una cadena CSV contenga solo categorías válidas.
     * Ejemplo: "B1,C1" → true, "B1,XX" → false.
     */
    public static function esCsvValido(string $csv): bool
    {
        if ($csv === '') {
            return true;
        }
        $partes = array_map('trim', explode(',', $csv));
        foreach ($partes as $parte) {
            if ($parte === '' || !self::tryFrom($parte)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Convierte un CSV a array de enum values.
     * "B1,C1" → [CategoriaLicenciaConducir::B1, CategoriaLicenciaConducir::C1]
     *
     * @return list<self>
     */
    public static function fromCsv(string $csv): array
    {
        if ($csv === '') {
            return [];
        }
        $partes = array_map('trim', explode(',', $csv));
        $resultado = [];
        foreach ($partes as $parte) {
            if ($parte !== '') {
                $enum = self::tryFrom($parte);
                if ($enum !== null) {
                    $resultado[] = $enum;
                }
            }
        }
        return $resultado;
    }

    /**
     * Convierte un array de enums a CSV.
     *
     * @param list<self> $categorias
     */
    public static function toCsv(array $categorias): string
    {
        return implode(',', array_map(fn(self $c) => $c->value, $categorias));
    }
}
