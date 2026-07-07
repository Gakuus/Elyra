<?php

declare(strict_types=1);

namespace Elyra\Domain\Entity;

class Usuario
{
    private ?int $id;
    private string $tipo;
    private string $nombre;
    private string $apellido;
    private ?string $email;
    private ?string $documentoIdentidad;
    private ?string $foto;
    private ?string $createdAt;

    public function __construct(
        ?int $id,
        string $tipo,
        string $nombre,
        string $apellido,
        ?string $email = null,
        ?string $documentoIdentidad = null,
        ?string $foto = null,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->tipo = $tipo;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->email = $email;
        $this->documentoIdentidad = $documentoIdentidad;
        $this->foto = $foto;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getApellido(): string
    {
        return $this->apellido;
    }

    public function getNombreCompleto(): string
    {
        return "{$this->nombre} {$this->apellido}";
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getDocumentoIdentidad(): ?string
    {
        return $this->documentoIdentidad;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getFoto(): ?string
    {
        return $this->foto;
    }

    public function setFoto(?string $foto): void
    {
        $this->foto = $foto;
    }

    public function getFotoBase64(): ?string
    {
        if ($this->foto === null) return null;
        return 'data:image/jpeg;base64,' . base64_encode($this->foto);
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function setDocumentoIdentidad(?string $documentoIdentidad): void
    {
        $this->documentoIdentidad = $documentoIdentidad;
    }
}
