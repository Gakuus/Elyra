<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Documento;

use Elyra\Domain\Entity\Documento;
use Elyra\Domain\Repository\DocumentoRepositoryInterface;
use Elyra\Infrastructure\Service\FileStorageService;
use Elyra\Infrastructure\Service\QRGeneratorService;

final class SubirDocumentoUseCase
{
    public function __construct(
        private DocumentoRepositoryInterface $documentoRepo,
        private FileStorageService $fileStorage,
        private QRGeneratorService $qrService,
    ) {
    }

    /**
     * @param array{
     *     titulo: string,
     *     categoriaId: int,
     *     subidoPor: int,
     *     descripcion?: string,
     *     especialidadId?: int,
     *     pacienteId?: int,
     *     archivoTmp?: string,
     *     archivoNombre?: string,
     *     archivoContenido?: string,
     * } $input
     *
     * @return array{success: bool, documentoId: int}
     */
    public function execute(array $input): array
    {
        $titulo = trim($input['titulo']);
        if (strlen($titulo) < 3 || strlen($titulo) > 200) {
            throw new \InvalidArgumentException('El título debe tener entre 3 y 200 caracteres.');
        }

        $categoriaId = $input['categoriaId'];
        if ($categoriaId <= 0) {
            throw new \InvalidArgumentException('Tipo de documento inválido.');
        }

        $archivoPath = '';
        $archivoNombre = '';
        $archivoContenido = null;

        if (isset($input['archivoTmp'], $input['archivoNombre'])) {
            $tmpName = $input['archivoTmp'];
            $origName = $input['archivoNombre'];

            $mimeType = mime_content_type($tmpName);
            if ($mimeType !== 'application/pdf') {
                throw new \InvalidArgumentException('El archivo debe ser un PDF válido.');
            }

            $fileSize = filesize($tmpName);
            if ($fileSize === false || $fileSize > 10 * 1024 * 1024) {
                throw new \InvalidArgumentException('El archivo supera el tamaño máximo de 10 MB.');
            }

            $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($origName, PATHINFO_FILENAME));
            $safeName = mb_substr(is_string($safeName) ? $safeName : '', 0, 80);
            $filename = $safeName . '_' . time() . '.pdf';

            $storageDir = __DIR__ . '/../../../../storage/docs';
            if (!is_dir($storageDir)) {
                mkdir($storageDir, 0775, true);
            }

            $destPath = $storageDir . '/' . $filename;
            if (!move_uploaded_file($tmpName, $destPath)) {
                throw new \RuntimeException('Error al guardar el archivo. Verificá los permisos del servidor.');
            }

            $archivoPath = $destPath;
            $archivoNombre = $filename;
        } elseif (isset($input['archivoContenido'], $input['archivoNombre'])) {
            $archivoNombre = $input['archivoNombre'];
            $archivoPath = $this->fileStorage->storeFromContent(
                $input['archivoContenido'],
                $archivoNombre,
                'docs'
            );
            $archivoContenido = $input['archivoContenido'];
        } else {
            throw new \InvalidArgumentException('Seleccioná un archivo PDF para subir.');
        }

        $doc = new Documento(
            id: null,
            titulo: $titulo,
            archivoPath: $archivoPath,
            archivoNombre: $archivoNombre,
            categoriaId: $categoriaId,
            subidoPor: $input['subidoPor'],
            descripcion: $input['descripcion'] ?? null,
            especialidadId: $input['especialidadId'] ?? null,
            pacienteId: $input['pacienteId'] ?? null,
            activo: true,
        );

        if ($archivoContenido !== null) {
            $doc->setArchivoContenido($archivoContenido);
        }

        $saved = $this->documentoRepo->save($doc);

        $bp = rtrim(parse_url((string)($_ENV['APP_URL'] ?? ''), PHP_URL_PATH) ?: '', '/');
        $qrData = $bp . '/publico/doc?token=' . ($saved->getId() ?? 0);
        $qrFilename = 'qr_' . ($saved->getId() ?? 0) . '_' . bin2hex(random_bytes(4)) . '.png';
        $qrPath = $this->qrService->generate($qrData, $qrFilename);

        $saved->setId($saved->getId() ?? 0);
        $this->documentoRepo->update($saved);

        return ['success' => true, 'documentoId' => $saved->getId() ?? 0];
    }
}
