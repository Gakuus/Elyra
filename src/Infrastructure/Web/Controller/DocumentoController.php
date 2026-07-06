<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

class DocumentoController extends BaseController
{
    private array $categorias = [
        ['id' => 1, 'nombre' => 'Cardiología'],
        ['id' => 2, 'nombre' => 'Nefrología'],
        ['id' => 3, 'nombre' => 'Imagenología'],
        ['id' => 4, 'nombre' => 'Ginecología'],
        ['id' => 5, 'nombre' => 'Cirugía'],
        ['id' => 6, 'nombre' => 'Enfermería'],
        ['id' => 7, 'nombre' => 'Nutrición'],
        ['id' => 8, 'nombre' => 'Infectología'],
    ];

    public function index(): void
    {
        $this->requireAuth();

        $documentos = $this->mockDocumentos();

        $search = trim($_GET['q'] ?? '');
        $categoriaFiltro = trim($_GET['categoria'] ?? '');
        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
        $porPagina = 5;

        if ($search) {
            $documentos = array_values(array_filter($documentos, function ($d) use ($search) {
                return stripos($d['titulo'], $search) !== false;
            }));
        }

        if ($categoriaFiltro) {
            $documentos = array_values(array_filter($documentos, function ($d) use ($categoriaFiltro) {
                return $d['categoria'] === $categoriaFiltro;
            }));
        }

        $total = count($documentos);
        $totalPaginas = max(1, (int) ceil($total / $porPagina));
        $pagina = min($pagina, $totalPaginas);
        $offset = ($pagina - 1) * $porPagina;
        $paginaDocs = array_slice($documentos, $offset, $porPagina);

        $this->render('documentos/index', [
            'documentos' => $paginaDocs,
            'categorias' => $this->categorias,
            'total' => $total,
            'pagina' => $pagina,
            'totalPaginas' => $totalPaginas,
            'search' => $search,
            'categoriaFiltro' => $categoriaFiltro,
        ]);
    }

    public function subir(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUpload();
            return;
        }

        $this->render('documentos/subir', [
            'categorias' => $this->categorias,
        ]);
    }

    private function handleUpload(): void
    {
        $titulo = trim($_POST['titulo'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $archivo = $_FILES['archivo'] ?? null;

        if (strlen($titulo) < 3 || strlen($titulo) > 200) {
            $this->render('documentos/subir', ['error' => 'El t&iacute;tulo debe tener entre 3 y 200 caracteres.', 'categorias' => $this->categorias]);
            return;
        }

        $valida = false;
        foreach ($this->categorias as $cat) {
            if ($cat['nombre'] === $categoria) { $valida = true; break; }
        }
        if (!$valida) {
            $this->render('documentos/subir', ['error' => 'Seleccion&aacute; una categor&iacute;a v&aacute;lida.', 'categorias' => $this->categorias]);
            return;
        }

        if (!$archivo || $archivo['error'] !== UPLOAD_ERR_OK) {
            $errorMsg = match ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El archivo supera el tama&ntilde;o m&aacute;ximo permitido.',
                UPLOAD_ERR_NO_FILE => 'Seleccion&aacute; un archivo PDF para subir.',
                default => 'Error al subir el archivo. Intentalo de nuevo.',
            };
            $this->render('documentos/subir', ['error' => $errorMsg, 'categorias' => $this->categorias]);
            return;
        }

        $mimeType = mime_content_type($archivo['tmp_name']);
        if ($mimeType !== 'application/pdf') {
            $this->render('documentos/subir', ['error' => 'El archivo debe ser un PDF v&aacute;lido.', 'categorias' => $this->categorias]);
            return;
        }

        if ($archivo['size'] > 10 * 1024 * 1024) {
            $this->render('documentos/subir', ['error' => 'El archivo supera el tama&ntilde;o m&aacute;ximo de 10 MB.', 'categorias' => $this->categorias]);
            return;
        }

        $this->redirect('/documentos?subido=1');
    }

    public function editar(): void
    {
        $this->requireAuth();
        $this->render('documentos/editar');
    }

    public function eliminar(): void
    {
        $this->requireAuth();
        $this->redirect('/documentos');
    }

    public function ver(): void
    {
        $this->requireAuth();
        $this->render('documentos/ver');
    }

    private function mockDocumentos(): array
    {
        return [
            ['id' => 1, 'titulo' => 'Indicaciones pre-operatorias', 'categoria' => 'Cirugía', 'subido' => '15/05/2026', 'activo' => true],
            ['id' => 2, 'titulo' => 'Preparación para estudios imagenológicos', 'categoria' => 'Imagenología', 'subido' => '14/05/2026', 'activo' => true],
            ['id' => 3, 'titulo' => 'Plan de alta enfermería - Nefrología', 'categoria' => 'Nefrología', 'subido' => '12/05/2026', 'activo' => true],
            ['id' => 4, 'titulo' => 'Cuidados post-operatorios cardiovasculares', 'categoria' => 'Cardiología', 'subido' => '10/05/2026', 'activo' => true],
            ['id' => 5, 'titulo' => 'Guía de preparación para cirugía ginecológica', 'categoria' => 'Ginecología', 'subido' => '08/05/2026', 'activo' => true],
            ['id' => 6, 'titulo' => 'Indicaciones ecocardiograma con dobutamina', 'categoria' => 'Cardiología', 'subido' => '06/05/2026', 'activo' => true],
            ['id' => 7, 'titulo' => 'Prevención de infecciones intrahospitalarias', 'categoria' => 'Enfermería', 'subido' => '04/05/2026', 'activo' => false],
            ['id' => 8, 'titulo' => 'Indicaciones para ingreso a centro de nefrología', 'categoria' => 'Nefrología', 'subido' => '02/05/2026', 'activo' => true],
        ];
    }
}
