<?php

declare(strict_types=1);

namespace Elyra\Domain\Entity;

class Paciente extends Usuario
{
    private ?string $tokenAcceso;
    private ?int $codigoQrId;
    private ?string $username;
    private ?string $passwordHash;
    private ?string $telefono;
    private bool $activo;
    private ?string $resetToken;
    private ?string $resetTokenExpiresAt;

    public function __construct(
        ?int $id,
        string $nombre,
        string $apellido,
        ?string $email = null,
        ?string $documentoIdentidad = null,
        ?string $tokenAcceso = null,
        ?int $codigoQrId = null,
        ?string $username = null,
        ?string $passwordHash = null,
        ?string $telefono = null,
        bool $activo = true,
        ?string $foto = null,
        ?string $createdAt = null,
        ?string $resetToken = null,
        ?string $resetTokenExpiresAt = null
    ) {
        parent::__construct($id, 'paciente', $nombre, $apellido, $email, $documentoIdentidad, $foto, $createdAt);
        $this->tokenAcceso = $tokenAcceso;
        $this->codigoQrId = $codigoQrId;
        $this->username = $username;
        $this->passwordHash = $passwordHash;
        $this->telefono = $telefono;
        $this->activo = $activo;
        $this->resetToken = $resetToken;
        $this->resetTokenExpiresAt = $resetTokenExpiresAt;
    }

    public function getTokenAcceso(): ?string
    {
        return $this->tokenAcceso;
    }

    public function getCodigoQrId(): ?int
    {
        return $this->codigoQrId;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getPasswordHash(): ?string
    {
        return $this->passwordHash;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function isActivo(): bool
    {
        return $this->activo;
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

    public function setTelefono(?string $telefono): void
    {
        $this->telefono = $telefono;
    }

    public function verificarPassword(string $password): bool
    {
        if ($this->passwordHash === null) {
            return false;
        }
        return password_verify($password, $this->passwordHash);
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $token): void
    {
        $this->resetToken = $token;
    }

    public function getResetTokenExpiresAt(): ?string
    {
        return $this->resetTokenExpiresAt;
    }

    public function setResetTokenExpiresAt(?string $expiresAt): void
    {
        $this->resetTokenExpiresAt = $expiresAt;
    }
}
