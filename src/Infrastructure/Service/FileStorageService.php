<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Service;

class FileStorageService
{
    private string $storageDir;
    private int $maxFileSize;

    public function __construct(?string $storageDir = null, int $maxFileSize = 10485760)
    {
        $this->storageDir = $storageDir ?? __DIR__ . '/../../../storage/uploads';
        $this->maxFileSize = $maxFileSize;
    }

    /**
     * @param array{tmp_name: string, name: string, size: int, error: int} $file
     */
    public function store(array $file, ?string $subdir = null): string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Error al subir el archivo: código ' . $file['error']);
        }

        if ($file['size'] > $this->maxFileSize) {
            throw new \RuntimeException('El archivo excede el tamaño máximo permitido de ' . ($this->maxFileSize / 1048576) . ' MB');
        }

        $targetDir = $this->storageDir;
        if ($subdir !== null) {
            $targetDir .= '/' . trim($subdir, '/');
        }

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx', 'xls', 'xlsx', 'txt'];

        if (!in_array($extension, $allowedExtensions, true)) {
            throw new \RuntimeException("Extensión no permitida: {$extension}");
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $filePath = $targetDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new \RuntimeException('Error al mover el archivo subido');
        }

        return $filePath;
    }

    public function storeFromContent(string $content, string $originalName, ?string $subdir = null): string
    {
        $targetDir = $this->storageDir;
        if ($subdir !== null) {
            $targetDir .= '/' . trim($subdir, '/');
        }

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $filePath = $targetDir . '/' . $filename;

        file_put_contents($filePath, $content);

        return $filePath;
    }

    public function delete(string $path): bool
    {
        if (is_file($path)) {
            return unlink($path);
        }
        return false;
    }

    public function read(string $path): ?string
    {
        if (is_file($path)) {
            $content = file_get_contents($path);
            return $content === false ? null : $content;
        }
        return null;
    }

    public function getMimeType(string $path): string
    {
        if (!is_file($path)) {
            return 'application/octet-stream';
        }
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return match ($extension) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'txt' => 'text/plain',
            default => 'application/octet-stream',
        };
    }
}
