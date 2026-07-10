<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\Service;

use Elyra\Infrastructure\Service\QRGeneratorService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(QRGeneratorService::class)]
final class QRGeneratorServiceTest extends TestCase
{
    private string $tempDir;
    private QRGeneratorService $service;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/elyra_qr_test_' . bin2hex(random_bytes(4));
        mkdir($this->tempDir, 0777, true);
        $this->service = new QRGeneratorService($this->tempDir);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursive($this->tempDir);
    }

    public function testGenerateReturnsFilePath(): void
    {
        $path = $this->service->generate('test-data', 'test_qr.png');
        $this->assertStringContainsString($this->tempDir, $path);
        $this->assertStringEndsWith('.png', $path);
    }

    public function testGenerateCreatesFile(): void
    {
        $path = $this->service->generate('qr-data', 'qr_file.png');
        $this->assertFileExists($path);
    }

    public function testGenerateWithLongData(): void
    {
        $longData = 'https://hospital.com/documento/' . bin2hex(random_bytes(32));
        $path = $this->service->generate($longData, 'long_qr.png');
        $this->assertFileExists($path);
    }

    public function testGenerateBase64ReturnsString(): void
    {
        $result = $this->service->generateBase64('test-data');
        // @phpstan-ignore-next-line staticMethod.alreadyNarrowedType
        $this->assertIsString($result);
        $this->assertStringStartsWith('data:image/png;base64,', $result);
    }

    public function testGenerateBase64WithEmptyData(): void
    {
        $result = $this->service->generateBase64('');
        // @phpstan-ignore-next-line staticMethod.alreadyNarrowedType
        $this->assertIsString($result);
    }

    public function testDeleteExistingFile(): void
    {
        $path = $this->service->generate('data', 'delete_test.png');
        $this->assertFileExists($path);

        $result = $this->service->delete('delete_test.png');
        $this->assertTrue($result);
        $this->assertFileDoesNotExist($path);
    }

    public function testDeleteNonExistentFile(): void
    {
        $result = $this->service->delete('nonexistent.png');
        $this->assertFalse($result);
    }

    public function testGenerateCreatesDirectoryAutomatically(): void
    {
        $newDir = sys_get_temp_dir() . '/elyra_auto_dir_' . bin2hex(random_bytes(4));
        $service = new QRGeneratorService($newDir);

        $path = $service->generate('data', 'auto_dir.png');
        $this->assertFileExists($path);

        $this->rmdirRecursive($newDir);
    }

    public function testGenerateWithSpecialChars(): void
    {
        $data = 'ñoño & café = 100% bueno';
        $path = $this->service->generate($data, 'special_qr.png');
        $this->assertFileExists($path);
    }

    private function rmdirRecursive(string $dir): void
    {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->rmdirRecursive($path) : unlink($path);
        }
        rmdir($dir);
    }
}
