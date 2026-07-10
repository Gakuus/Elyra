<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\Entity;

use Elyra\Domain\Entity\Documento;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Documento::class)]
final class DocumentoTest extends TestCase
{
    public function testCreateDocumento(): void
    {
        $d = new Documento(
            id: 1,
            titulo: 'Indicaciones post-operatorias',
            archivoPath: '/storage/docs/abc123.pdf',
            archivoNombre: 'indicaciones.pdf',
            codigoQrId: 3,
            categoriaId: 2,
            subidoPor: 1,
            descripcion: 'Indicaciones para el paciente',
            pacienteId: 5,
            activo: true,
        );

        $this->assertSame(1, $d->getId());
        $this->assertSame('Indicaciones post-operatorias', $d->getTitulo());
        $this->assertSame('/storage/docs/abc123.pdf', $d->getArchivoPath());
        $this->assertSame('indicaciones.pdf', $d->getArchivoNombre());
        $this->assertSame(2, $d->getCategoriaId());
        $this->assertSame(1, $d->getSubidoPor());
        $this->assertSame('pdf', $d->getExtension());
    }

    public function testSetId(): void
    {
        $d = new Documento(
            id: null, titulo: 'Test', archivoPath: '/a.pdf',
            archivoNombre: 'a.pdf', codigoQrId: null, categoriaId: 1, subidoPor: 1,
        );
        $d->setId(42);
        $this->assertSame(42, $d->getId());
    }

    public function testSetActivo(): void
    {
        $d = new Documento(
            id: 1, titulo: 'Test', archivoPath: '/a.pdf',
            archivoNombre: 'a.pdf', codigoQrId: null, categoriaId: 1, subidoPor: 1,
        );
        $this->assertTrue($d->isActivo());
        $d->setActivo(false);
        $this->assertFalse($d->isActivo());
    }

    public function testGetExtension(): void
    {
        $d = new Documento(
            id: 1, titulo: 'Doc', archivoPath: '/doc.PDF',
            archivoNombre: 'informe.PDF', codigoQrId: null, categoriaId: 1, subidoPor: 1,
        );
        $this->assertSame('pdf', $d->getExtension());
    }

    public function testGetExtensionJpg(): void
    {
        $d = new Documento(
            id: 1, titulo: 'Img', archivoPath: '/img.JPG',
            archivoNombre: 'foto.JPG', codigoQrId: null, categoriaId: 1, subidoPor: 1,
        );
        $this->assertSame('jpg', $d->getExtension());
    }

    public function testArchivoContenido(): void
    {
        $d = new Documento(
            id: 1, titulo: 'Test', archivoPath: '/a.pdf',
            archivoNombre: 'a.pdf', codigoQrId: null, categoriaId: 1, subidoPor: 1,
        );
        $this->assertNull($d->getArchivoContenido());
        $d->setArchivoContenido('pdf_binary_content');
        $this->assertSame('pdf_binary_content', $d->getArchivoContenido());
    }
}
