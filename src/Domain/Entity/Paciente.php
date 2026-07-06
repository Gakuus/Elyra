<?php

declare(strict_types=1);

namespace Elyra\Domain\Entity;

class Paciente extends Usuario
{
    private ?string $tokenAcceso;
    private ?int $codigoQrId;

    public function __construct(
        ?int $id,
        string $nombre,
        string $apellido,
        ?string $email = null,
        ?string $documentoIdentidad = null,
        ?string $tokenAcceso = null,
        ?int $codigoQrId = null,
        ?string $createdAt = null
    ) {
        parent::__construct($id, 'paciente', $nombre, $apellido, $email, $documentoIdentidad, $createdAt);
        $this->tokenAcceso = $tokenAcceso;
        $this->codigoQrId = $codigoQrId;
    }

    public function getTokenAcceso(): ?string
    {
        return $this->tokenAcceso;
    }

    public function getCodigoQrId(): ?int
    {
        return $this->codigoQrId;
    }
}
