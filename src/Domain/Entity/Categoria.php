<?php

declare(strict_types=1);

namespace Elyra\Domain\Entity;

class Categoria
{
    private ?int $id;
    private string $nombre;
    private ?string $descripcion;
    private string $tipo;

    public function __construct(?int $id, string $nombre, ?string $descripcion = null, string $tipo = 'tipo_documento')
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->tipo = $tipo;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
