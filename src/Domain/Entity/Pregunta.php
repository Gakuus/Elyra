<?php

declare(strict_types=1);

namespace Elyra\Domain\Entity;

use Elyra\Domain\ValueObject\TipoPregunta;

class Pregunta
{
    private ?int $id;
    private int $encuestaId;
    private TipoPregunta $tipo;
    private string $texto;
    private ?array $opciones;
    private bool $requerida;
    private int $orden;
    private ?string $createdAt;

    public function __construct(
        ?int $id,
        int $encuestaId,
        TipoPregunta $tipo,
        string $texto,
        int $orden,
        ?array $opciones = null,
        bool $requerida = true,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->encuestaId = $encuestaId;
        $this->tipo = $tipo;
        $this->texto = $texto;
        $this->opciones = $opciones;
        $this->requerida = $requerida;
        $this->orden = $orden;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int { return $this->id; }
    public function getEncuestaId(): int { return $this->encuestaId; }
    public function getTipo(): TipoPregunta { return $this->tipo; }
    public function getTexto(): string { return $this->texto; }
    public function getOpciones(): ?array { return $this->opciones; }
    public function isRequerida(): bool { return $this->requerida; }
    public function getOrden(): int { return $this->orden; }
    public function getCreatedAt(): ?string { return $this->createdAt; }

    public function setId(int $id): void { $this->id = $id; }
}
