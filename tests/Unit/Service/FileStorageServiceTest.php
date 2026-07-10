<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\Service;

use Elyra\Infrastructure\Service\FileStorageService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileStorageService::class)]
final class FileStorageServiceTest extends TestCase
{
    private string $tempDir;
    private FileStorageService $service;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/elyra_test_' . bin2hex(random_bytes(4));
        mkdir($this->tempDir, 0777, true);
        $this->service = new FileStorageService($this->tempDir);
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursive($this->tempDir);
    }

    public function testStoreFromContent(): void
    {
        $path = $this->service->storeFromContent('test content', 'test.txt');
        $this->assertStringContainsString($this->tempDir, $path);
        $this->assertStringEndsWith('.txt', $path);
        $this->assertFileExists($path);
        $this->assertSame('test content', file_get_contents($path));
    }

    public function testStoreFromContentInSubdir(): void
    {
        $path = $this->service->storeFromContent('content', 'doc.pdf', 'pdfs');
        $this->assertStringContainsString('/pdfs/', $path);
        $this->assertFileExists($path);
    }

    public function testDeleteExistingFile(): void
    {
        $path = $this->service->storeFromContent('delete me', 'delete.txt');
        $this->assertFileExists($path);

        $result = $this->service->delete($path);
        $this->assertTrue($result);
        $this->assertFileDoesNotExist($path);
    }

    public function testDeleteNonExistentFile(): void
    {
        $result = $this->service->delete('/nonexistent/path.txt');
        $this->assertFalse($result);
    }

    public function testReadExistingFile(): void
    {
        $path = $this->service->storeFromContent('readable content', 'read.txt');
        $content = $this->service->read($path);
        $this->assertSame('readable content', $content);
    }

    public function testReadNonExistentFile(): void
    {
        $content = $this->service->read('/nonexistent/file.txt');
        $this->assertNull($content);
    }

    public function testGetMimeTypePdf(): void
    {
        $path = $this->service->storeFromContent('%PDF-1.4 content', 'doc.pdf');
        $mime = $this->service->getMimeType($path);
        $this->assertSame('application/pdf', $mime);
    }

    public function testGetMimeTypeJpg(): void
    {
        $path = $this->service->storeFromContent('fake jpeg', 'photo.jpg');
        $mime = $this->service->getMimeType($path);
        $this->assertSame('image/jpeg', $mime);
    }

    public function testGetMimeTypeTxt(): void
    {
        $path = $this->service->storeFromContent('text', 'notes.txt');
        $mime = $this->service->getMimeType($path);
        $this->assertSame('text/plain', $mime);
    }

    public function testGetMimeTypeNonExistent(): void
    {
        $mime = $this->service->getMimeType('/nonexistent/file.xyz');
        $this->assertSame('application/octet-stream', $mime);
    }

    public function testGetMimeTypePng(): void
    {
        $path = $this->service->storeFromContent('fake png', 'image.png');
        $mime = $this->service->getMimeType($path);
        $this->assertSame('image/png', $mime);
    }

    public function testDefaultMaxFileSize(): void
    {
        $service = new FileStorageService($this->tempDir);
        $path = $service->storeFromContent('small', 'small.txt');
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
