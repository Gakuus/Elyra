<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Documento;

use Elyra\Domain\Repository\DocumentoRepositoryInterface;

final class EliminarDocumentoUseCase
{
    public function __construct(
        private DocumentoRepositoryInterface $documentoRepo,
    ) {
    }

    /**
     * @param array{id: int} $input
     */
    public function execute(array $input): void
    {
        $id = $input['id'];
        if ($id <= 0) {
            throw new \InvalidArgumentException('ID de documento inválido.');
        }

        $doc = $this->documentoRepo->findById($id);
        if ($doc === null) {
            throw new \DomainException('Documento no encontrado.');
        }

        $this->documentoRepo->delete($id);
    }
}
