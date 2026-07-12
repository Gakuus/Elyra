<?php

declare(strict_types=1);

namespace Elyra\Domain\ValueObject;

class LicenciaProfesional
{
    /** @var array<string, string> Codigo => Descripcion */
    private const LICENCIAS = [
        'MG'  => 'Médico General',
        'MC'  => 'Médico Clínico',
        'CAR' => 'Cardiología',
        'CG'  => 'Cirugía General',
        'PED' => 'Pediatría',
        'GYN' => 'Ginecología y Obstetricia',
        'ANE' => 'Anestesiología',
        'TRA' => 'Traumatología',
        'NEU' => 'Neurología',
        'NEF' => 'Nefrología',
        'ONC' => 'Oncología',
        'RAD' => 'Radiología',
        'MIE' => 'Medicina Interna',
        'EME' => 'Emergenciología',
        'INF' => 'Infectología',
        'ENF' => 'Enfermería General',
        'ENE' => 'Enfermería Especializada',
        'TA'  => 'Técnico en Ambulancia',
        'TE'  => 'Técnico en Emergencia',
        'BQ'  => 'Bioquímico',
        'FAR' => 'Farmacia',
        'KIN' => 'Kinesiología',
        'NUT' => 'Nutrición',
        'TS'  => 'Trabajo Social',
        'PSI' => 'Psicología',
        'ADM' => 'Administración Hospitalaria',
        'SEC' => 'Secretaría Médica',
        'CA'  => 'Conductor de Ambulancia',
        'AA'  => 'Auxiliar de Ambulancia',
    ];

    /** @return array<string, string> */
    public static function obtenerTodas(): array
    {
        return self::LICENCIAS;
    }

    public static function esValida(string $codigo): bool
    {
        return array_key_exists(strtoupper($codigo), self::LICENCIAS);
    }

    public static function obtenerDescripcion(string $codigo): string
    {
        return self::LICENCIAS[strtoupper($codigo)] ?? $codigo;
    }

    /** @return list<string> */
    public static function obtenerCodigos(): array
    {
        return array_keys(self::LICENCIAS);
    }
}
