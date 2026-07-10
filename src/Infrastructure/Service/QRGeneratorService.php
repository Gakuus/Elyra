<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Service;

class QRGeneratorService
{
    private string $storageDir;

    public function __construct(?string $storageDir = null)
    {
        $this->storageDir = $storageDir ?? __DIR__ . '/../../../storage/qrcodes';
    }

    public function generate(string $data, string $filename): string
    {
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0775, true);
        }

        $filePath = $this->storageDir . '/' . $filename;

        $chl = urlencode($data);
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={$chl}";

        $qrData = @file_get_contents($qrUrl);
        if ($qrData === false) {
            $this->generateFallbackPng($data, $filePath);
        } else {
            file_put_contents($filePath, $qrData);
        }

        return $filePath;
    }

    public function generateBase64(string $data): string
    {
        $chl = urlencode($data);
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={$chl}";

        $qrData = @file_get_contents($qrUrl);
        if ($qrData !== false) {
            return 'data:image/png;base64,' . base64_encode($qrData);
        }

        return '';
    }

    public function delete(string $filename): bool
    {
        $filePath = $this->storageDir . '/' . $filename;
        if (is_file($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    private function generateFallbackPng(string $data, string $filePath): void
    {
        $size = 200;
        $img = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);
        imagefill($img, 0, 0, $white);

        $chars = str_split($data);
        $cols = 20;
        $rows = 20;
        $cellSize = (int) floor($size / max($cols, $rows));
        $offsetX = (int) (($size - $cellSize * $cols) / 2);
        $offsetY = (int) (($size - $cellSize * $rows) / 2);

        for ($r = 0; $r < $rows; $r++) {
            for ($c = 0; $c < $cols; $c++) {
                $idx = ($r * $cols + $c) % count($chars);
                $char = ord($chars[$idx]);
                if ($char % 2 === 0) {
                    imagefilledrectangle(
                        $img,
                        $offsetX + $c * $cellSize,
                        $offsetY + $r * $cellSize,
                        $offsetX + ($c + 1) * $cellSize - 1,
                        $offsetY + ($r + 1) * $cellSize - 1,
                        $black
                    );
                }
            }
        }

        imagepng($img, $filePath);
        imagedestroy($img);
    }
}
