<?php

declare(strict_types=1);

namespace Elyra\Domain\Repository;

use Elyra\Domain\Entity\Documento;

interface DocumentoRepositoryInterface
{
    public function findById(int $id): ?Documento;
    public function findByCodigoQr(int $codigoQrId): ?Documento;
    public function findByEncuesta(int $encuestaId): ?Documento;
    /** @return Documento[] */
    public function findByPaciente(int $pacienteId, ?int $categoriaId = null, ?string $busqueda = null, int $page = 1, int $perPage = 20): array;
    public function countByPaciente(int $pacienteId, ?int $categoriaId = null, ?string $busqueda = null): int;
    /** @return Documento[] */
    public function findAll(?int $categoriaId = null, ?string $busqueda = null, ?int $pacienteId = null, int $page = 1, int $perPage = 20): array;
    public function count(?int $categoriaId = null, ?string $busqueda = null, ?int $pacienteId = null): int;
    /** @return Documento[] */
    public function findGenerales(?int $categoriaId = null, ?string $busqueda = null, int $page = 1, int $perPage = 20): array;
    public function countGenerales(?int $categoriaId = null, ?string $busqueda = null): int;
    /** @return Documento[] */
    public function findDePacientes(?int $categoriaId = null, ?string $busqueda = null, ?int $pacienteId = null, int $page = 1, int $perPage = 20): array;
    public function countDePacientes(?int $categoriaId = null, ?string $busqueda = null, ?int $pacienteId = null): int;
    public function countTotal(): int;
    public function countActivos(): int;
    public function getArchivoContent(int $id): ?string;
    public function save(Documento $documento): Documento;
    public function update(Documento $documento): void;
    public function delete(int $id): void;
}
