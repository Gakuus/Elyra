<?php

declare(strict_types=1);

namespace Elyra\Domain\Entity;

class Documento
{
    private ?int $id;
    private string $titulo;
    private ?string $descripcion;
    private string $archivoPath;
    private string $archivoNombre;
    private int $codigoQrId;
    private ?string $qrPath;
    private int $categoriaId;
    private ?int $encuestaId;
    private int $subidoPor;
    private bool $activo;
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct(
        ?int $id,
        string $titulo,
        string $archivoPath,
        string $archivoNombre,
        int $codigoQrId,
        int $categoriaId,
        int $subidoPor,
        ?string $descripcion = null,
        ?string $qrPath = null,
        ?int $encuestaId = null,
        bool $activo = true,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->titulo = $titulo;
        $this->archivoPath = $archivoPath;
        $this->archivoNombre = $archivoNombre;
        $this->codigoQrId = $codigoQrId;
        $this->categoriaId = $categoriaId;
        $this->subidoPor = $subidoPor;
        $this->descripcion = $descripcion;
        $this->qrPath = $qrPath;
        $this->encuestaId = $encuestaId;
        $this->activo = $activo;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): ?int { return $this->id; }
    public function getTitulo(): string { return $this->titulo; }
    public function getDescripcion(): ?string { return $this->descripcion; }
    public function getArchivoPath(): string { return $this->archivoPath; }
    public function getArchivoNombre(): string { return $this->archivoNombre; }
    public function getCodigoQrId(): int { return $this->codigoQrId; }
    public function getQrPath(): ?string { return $this->qrPath; }
    public function getCategoriaId(): int { return $this->categoriaId; }
    public function getEncuestaId(): ?int { return $this->encuestaId; }
    public function getSubidoPor(): int { return $this->subidoPor; }
    public function isActivo(): bool { return $this->activo; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }

    public function setId(int $id): void { $this->id = $id; }
    public function setActivo(bool $activo): void { $this->activo = $activo; }

    public function getExtension(): string
    {
        return strtolower(pathinfo($this->archivoNombre, PATHINFO_EXTENSION));
    }
}
