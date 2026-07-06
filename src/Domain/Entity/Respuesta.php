<?php

declare(strict_types=1);

namespace Elyra\Domain\Entity;

class Respuesta
{
    private ?int $id;
    private string $sesionToken;
    private int $encuestaId;
    private int $preguntaId;
    private ?string $tokenPaciente;
    private ?int $valorOpcion;
    private ?string $valorTexto;
    private ?int $valorNumerico;
    private ?string $createdAt;

    public function __construct(
        ?int $id,
        string $sesionToken,
        int $encuestaId,
        int $preguntaId,
        ?string $tokenPaciente = null,
        ?int $valorOpcion = null,
        ?string $valorTexto = null,
        ?int $valorNumerico = null,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->sesionToken = $sesionToken;
        $this->encuestaId = $encuestaId;
        $this->preguntaId = $preguntaId;
        $this->tokenPaciente = $tokenPaciente;
        $this->valorOpcion = $valorOpcion;
        $this->valorTexto = $valorTexto;
        $this->valorNumerico = $valorNumerico;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int { return $this->id; }
    public function getSesionToken(): string { return $this->sesionToken; }
    public function getEncuestaId(): int { return $this->encuestaId; }
    public function getPreguntaId(): int { return $this->preguntaId; }
    public function getTokenPaciente(): ?string { return $this->tokenPaciente; }
    public function getValorOpcion(): ?int { return $this->valorOpcion; }
    public function getValorTexto(): ?string { return $this->valorTexto; }
    public function getValorNumerico(): ?int { return $this->valorNumerico; }
    public function getCreatedAt(): ?string { return $this->createdAt; }

    public function setId(int $id): void { $this->id = $id; }
}
