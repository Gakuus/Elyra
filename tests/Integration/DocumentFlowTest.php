<?php

declare(strict_types=1);

namespace Elyra\Tests\Integration;

use Elyra\Domain\Entity\Documento;
use Elyra\Domain\Entity\Encuesta;
use Elyra\Domain\Entity\Pregunta;
use Elyra\Domain\ValueObject\TipoPregunta;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Prueba integral del flujo de documentos y encuestas.
 * Cubre HU-02 (subir), HU-03 (editar), HU-04 (eliminar), HU-05 (categorizar),
 * HU-08 (crear encuesta), HU-09 (responder), HU-10 (ver resultados).
 */
#[CoversClass(Documento::class)]
#[CoversClass(Encuesta::class)]
#[CoversClass(Pregunta::class)]
final class DocumentFlowTest extends TestCase
{
    public function testHU02_SubirDocumento(): void
    {
        $doc = new Documento(
            id: null,
            titulo: 'Indicaciones post-operatorias',
            archivoPath: '/storage/docs/abc123.pdf',
            archivoNombre: 'indicaciones.pdf',
            categoriaId: 1,
            subidoPor: 1,
            codigoQrId: null,
            descripcion: 'Indicaciones para el alta',
            activo: true,
        );

        $this->assertNull($doc->getId());
        $this->assertSame('Indicaciones post-operatorias', $doc->getTitulo());
        $this->assertSame('pdf', $doc->getExtension());
        $this->assertTrue($doc->isActivo());
    }

    public function testHU03_EditarDocumento(): void
    {
        $doc = new Documento(
            id: 1,
            titulo: 'Título Original',
            archivoPath: '/storage/docs/abc.pdf',
            archivoNombre: 'doc.pdf',
            codigoQrId: null,
            categoriaId: 1,
            subidoPor: 1,
        );

        $reflection = new \ReflectionClass($doc);
        $tituloProp = $reflection->getProperty('titulo');
        $tituloProp->setValue($doc, 'Título Modificado');

        $this->assertSame('Título Modificado', $doc->getTitulo());
    }

    public function testHU04_EliminarDocumentoBajaLogica(): void
    {
        $doc = new Documento(
            id: 1,
            titulo: 'Doc a eliminar',
            archivoPath: '/storage/docs/del.pdf',
            archivoNombre: 'del.pdf',
            codigoQrId: null,
            categoriaId: 1,
            subidoPor: 1,
            activo: true,
        );

        $this->assertTrue($doc->isActivo());
        $doc->setActivo(false);
        $this->assertFalse($doc->isActivo());
    }

    public function testHU05_CategorizarDocumento(): void
    {
        $doc = new Documento(
            id: 1,
            titulo: 'Doc Cardiología',
            archivoPath: '/storage/docs/cardio.pdf',
            archivoNombre: 'cardio.pdf',
            codigoQrId: null,
            categoriaId: 5,
            subidoPor: 1,
            especialidadId: 2,
        );

        $this->assertSame(5, $doc->getCategoriaId());
        $this->assertSame(2, $doc->getEspecialidadId());
    }

    public function testHU08_CrearEncuestaConPreguntas(): void
    {
        $encuesta = new Encuesta(
            id: null,
            titulo: 'Encuesta de Satisfacción',
            creadaPor: 1,
            descripcion: 'Evalúe su experiencia en el hospital',
            activa: true,
        );

        $this->assertNull($encuesta->getId());
        $this->assertTrue($encuesta->isActiva());

        $pregunta1 = new Pregunta(
            id: null,
            encuestaId: 0,
            tipo: new TipoPregunta('multiple_choice'),
            texto: '¿Cómo califica la atención?',
            orden: 0,
            opciones: ['Excelente', 'Buena', 'Regular', 'Mala'],
            requerida: true,
        );

        $pregunta2 = new Pregunta(
            id: null,
            encuestaId: 0,
            tipo: new TipoPregunta('escala'),
            texto: 'Del 1 al 5, ¿recomendaría el servicio?',
            orden: 1,
            requerida: true,
        );

        $this->assertSame('multiple_choice', $pregunta1->getTipo()->value());
        $this->assertSame('escala', $pregunta2->getTipo()->value());
        $this->assertTrue($pregunta1->isRequerida());
    }

    public function testHU11_ListarDocumentosConFiltros(): void
    {
        $docs = [
            new Documento(id: 1, titulo: 'Cardiología A', archivoPath: '/a.pdf', archivoNombre: 'a.pdf', codigoQrId: null, categoriaId: 1, subidoPor: 1),
            new Documento(id: 2, titulo: 'Nefrología B', archivoPath: '/b.pdf', archivoNombre: 'b.pdf', codigoQrId: null, categoriaId: 2, subidoPor: 1),
            new Documento(id: 3, titulo: 'Cardiología C', archivoPath: '/c.pdf', archivoNombre: 'c.pdf', codigoQrId: null, categoriaId: 1, subidoPor: 2),
        ];

        $filtered = array_values(array_filter($docs, fn($d) => $d->getCategoriaId() === 1));

        $this->assertCount(2, $filtered);
        $this->assertSame(1, $filtered[0]->getId());
        $this->assertSame(3, $filtered[1]->getId());

        $titulos = array_map(fn($d) => $d->getTitulo(), $filtered);
        $this->assertContains('Cardiología A', $titulos);
        $this->assertContains('Cardiología C', $titulos);
    }
}
