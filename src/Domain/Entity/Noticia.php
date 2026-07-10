<?php

declare(strict_types=1);

namespace Elyra\Domain\Entity;

class Noticia
{
    public function __construct(
        private ?int $id,
        private string $titulo,
        private string $contenido,
        private ?string $imagen = null,
        private ?int $autorId = null,
        private ?string $autorNombre = null,
        private bool $activo = true,
        private ?string $createdAt = null,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitulo(): string
    {
        return $this->titulo;
    }

    public function getContenido(): string
    {
        return $this->contenido;
    }

    public function getImagen(): ?string
    {
        return $this->imagen;
    }

    public function getAutorId(): ?int
    {
        return $this->autorId;
    }

    public function getAutorNombre(): ?string
    {
        return $this->autorNombre;
    }

    public function isActivo(): bool
    {
        return $this->activo;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function setTitulo(string $titulo): void
    {
        $this->titulo = $titulo;
    }

    public function setContenido(string $contenido): void
    {
        $this->contenido = $contenido;
    }

    public function setImagen(?string $imagen): void
    {
        $this->imagen = $imagen;
    }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }
}
