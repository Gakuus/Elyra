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
    private ?string $archivoContenido = null;
    private ?int $codigoQrId;
    private ?string $qrPath;
    private int $categoriaId;
    private ?int $especialidadId;
    private ?int $encuestaId;
    private ?int $pacienteId;
    private ?string $pacienteNombre;
    private int $subidoPor;
    private bool $activo;
    private ?string $createdAt;
    private ?string $updatedAt;
    private ?string $categoriaNombre;
    private ?string $especialidadNombre;

    public function __construct(
        ?int $id,
        string $titulo,
        string $archivoPath,
        string $archivoNombre,
        ?int $codigoQrId = null,
        int $categoriaId,
        int $subidoPor,
        ?string $descripcion = null,
        ?string $qrPath = null,
        ?int $especialidadId = null,
        ?int $encuestaId = null,
        ?int $pacienteId = null,
        bool $activo = true,
        ?string $createdAt = null,
        ?string $updatedAt = null,
        ?string $categoriaNombre = null,
        ?string $especialidadNombre = null,
        ?string $pacienteNombre = null
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
        $this->especialidadId = $especialidadId;
        $this->encuestaId = $encuestaId;
        $this->pacienteId = $pacienteId;
        $this->activo = $activo;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->categoriaNombre = $categoriaNombre;
        $this->especialidadNombre = $especialidadNombre;
        $this->pacienteNombre = $pacienteNombre;
    }

    public function getId(): ?int { return $this->id; }
    public function getTitulo(): string { return $this->titulo; }
    public function getDescripcion(): ?string { return $this->descripcion; }
    public function getArchivoPath(): string { return $this->archivoPath; }
    public function getArchivoNombre(): string { return $this->archivoNombre; }
    public function getArchivoContenido(): ?string { return $this->archivoContenido; }
    public function setArchivoContenido(?string $contenido): void { $this->archivoContenido = $contenido; }
    public function getCodigoQrId(): ?int { return $this->codigoQrId; }
    public function getQrPath(): ?string { return $this->qrPath; }
    public function getCategoriaId(): int { return $this->categoriaId; }
    public function getEspecialidadId(): ?int { return $this->especialidadId; }
    public function getEncuestaId(): ?int { return $this->encuestaId; }
    public function getPacienteId(): ?int { return $this->pacienteId; }
    public function getPacienteNombre(): ?string { return $this->pacienteNombre; }
    public function getSubidoPor(): int { return $this->subidoPor; }
    public function isActivo(): bool { return $this->activo; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }

    public function setId(int $id): void { $this->id = $id; }
    public function setActivo(bool $activo): void { $this->activo = $activo; }

    public function getCategoriaNombre(): ?string { return $this->categoriaNombre; }
    public function getEspecialidadNombre(): ?string { return $this->especialidadNombre; }

    public function getExtension(): string
    {
        return strtolower(pathinfo($this->archivoNombre, PATHINFO_EXTENSION));
    }
}
