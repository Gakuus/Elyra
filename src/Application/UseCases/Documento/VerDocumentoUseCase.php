<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Documento;

use Elyra\Domain\Entity\Documento;
use Elyra\Domain\Repository\DocumentoRepositoryInterface;

final class VerDocumentoUseCase
{
    public function __construct(
        private DocumentoRepositoryInterface $documentoRepo,
    ) {
    }

    /**
     * @param array{id: int} $input
     *
     * @return Documento|null
     */
    public function execute(array $input): ?Documento
    {
        $id = $input['id'];
        if ($id <= 0) {
            return null;
        }

        return $this->documentoRepo->findById($id);
    }
}
