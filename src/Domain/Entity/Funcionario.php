<?php

declare(strict_types=1);

namespace Elyra\Domain\Entity;

use Elyra\Domain\ValueObject\RolUsuario;

class Funcionario extends Usuario
{
    private ?string $username;
    private ?string $passwordHash;
    private ?string $licencia;
    private ?string $telefono;
    private bool $activo;
    private RolUsuario $rol;

    public function __construct(
        ?int $id,
        string $nombre,
        string $apellido,
        RolUsuario $rol,
        ?string $username = null,
        ?string $passwordHash = null,
        ?string $email = null,
        ?string $documentoIdentidad = null,
        ?string $licencia = null,
        ?string $telefono = null,
        bool $activo = true,
        ?string $createdAt = null
    ) {
        parent::__construct($id, 'funcionario', $nombre, $apellido, $email, $documentoIdentidad, $createdAt);
        $this->username = $username;
        $this->passwordHash = $passwordHash;
        $this->licencia = $licencia;
        $this->telefono = $telefono;
        $this->activo = $activo;
        $this->rol = $rol;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getPasswordHash(): ?string
    {
        return $this->passwordHash;
    }

    public function getLicencia(): ?string
    {
        return $this->licencia;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function isActivo(): bool
    {
        return $this->activo;
    }

    public function getRol(): RolUsuario
    {
        return $this->rol;
    }

    public function esAdmin(): bool
    {
        return $this->rol->esAdmin();
    }

    public function esSuperadmin(): bool
    {
        return $this->rol->esSuperadmin();
    }

    public function esConductor(): bool
    {
        return $this->rol->esConductor();
    }

    public function verificarPassword(string $password): bool
    {
        if ($this->passwordHash === null) {
            return false;
        }
        return password_verify($password, $this->passwordHash);
    }

    public function setPasswordHash(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }
}
