<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Documento;

use Elyra\Domain\Entity\Documento;
use Elyra\Domain\Repository\DocumentoRepositoryInterface;

final class EditarDocumentoUseCase
{
    public function __construct(
        private DocumentoRepositoryInterface $documentoRepo,
    ) {
    }

    /**
     * @param array{
     *     id: int,
     *     titulo: string,
     *     descripcion?: string,
     *     categoriaId: int,
     *     especialidadId?: int,
     *     pacienteId?: int,
     * } $input
     */
    public function execute(array $input): void
    {
        $doc = $this->documentoRepo->findById($input['id']);
        if ($doc === null) {
            throw new \DomainException('Documento no encontrado.');
        }

        $titulo = trim($input['titulo']);
        if (strlen($titulo) < 3 || strlen($titulo) > 200) {
            throw new \InvalidArgumentException('El título debe tener entre 3 y 200 caracteres.');
        }

        $categoriaId = $input['categoriaId'];
        if ($categoriaId <= 0) {
            throw new \InvalidArgumentException('Tipo de documento inválido.');
        }

        $updated = new Documento(
            id: $doc->getId(),
            titulo: $titulo,
            archivoPath: $doc->getArchivoPath(),
            archivoNombre: $doc->getArchivoNombre(),
            codigoQrId: $doc->getCodigoQrId(),
            categoriaId: $categoriaId,
            subidoPor: $doc->getSubidoPor(),
            descripcion: $input['descripcion'] ?? $doc->getDescripcion(),
            qrPath: $doc->getQrPath(),
            especialidadId: $input['especialidadId'] ?? $doc->getEspecialidadId(),
            encuestaId: $doc->getEncuestaId(),
            pacienteId: $input['pacienteId'] ?? $doc->getPacienteId(),
            activo: $doc->isActivo(),
            createdAt: $doc->getCreatedAt(),
        );

        $this->documentoRepo->update($updated);
    }
}
