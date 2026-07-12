<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Documento;

use Elyra\Domain\Entity\Documento;
use Elyra\Domain\Repository\DocumentoRepositoryInterface;

final class ListarDocumentosUseCase
{
    public function __construct(
        private DocumentoRepositoryInterface $documentoRepo,
    ) {
    }

    /**
     * @param array{
     *     categoriaId?: int|null,
     *     busqueda?: string|null,
     *     pacienteId?: int|null,
     *     page?: int,
     *     perPage?: int,
     * } $input
     *
     * @return array{documentos: list<Documento>, total: int, page: int, totalPages: int}
     */
    public function execute(array $input): array
    {
        $page = max(1, $input['page'] ?? 1);
        $perPage = min(100, max(1, $input['perPage'] ?? 20));
        $categoriaId = $input['categoriaId'] ?? null;
        $busqueda = ($input['busqueda'] ?? '') !== '' ? $input['busqueda'] : null;
        $pacienteId = $input['pacienteId'] ?? null;

        /** @var list<\Elyra\Domain\Entity\Documento> $documentos */
        $documentos = array_values($this->documentoRepo->findAll($categoriaId, $busqueda, $pacienteId, $page, $perPage));
        $total = $this->documentoRepo->count($categoriaId, $busqueda, $pacienteId);
        $totalPages = max(1, (int) ceil($total / $perPage));

        return [
            'documentos' => $documentos,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
        ];
    }
}
