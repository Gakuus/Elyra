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

    private string $storageDir;

    public function __construct()
    {
        $this->storageDir = __DIR__ . '/../../../../storage/uploads/documents';
    }

    public function index(): void
    {
        $this->requireAuth();

        $documentos = array_merge($this->mockDocumentos(), $this->uploadedDocs());

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
        $isJson = str_starts_with($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

        $csrf = trim($_POST['_csrf_token'] ?? '');
        if ($csrf !== ($_SESSION['_csrf_token'] ?? '')) {
            $msg = 'Sesi&oacute;n inv&aacute;lida. Recarg&aacute; e intent&aacute; de nuevo.';
            if ($isJson) { $this->json(['error' => $msg], 403); return; }
            $this->render('documentos/subir', ['error' => $msg, 'categorias' => $this->categorias]);
            return;
        }

        $titulo = trim($_POST['titulo'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $archivo = $_FILES['archivo'] ?? null;

        if (strlen($titulo) < 3 || strlen($titulo) > 200) {
            $msg = 'El t&iacute;tulo debe tener entre 3 y 200 caracteres.';
            if ($isJson) { $this->json(['error' => $msg], 422); return; }
            $this->render('documentos/subir', ['error' => $msg, 'categorias' => $this->categorias]);
            return;
        }

        $valida = false;
        foreach ($this->categorias as $cat) {
            if ($cat['nombre'] === $categoria) { $valida = true; break; }
        }
        if (!$valida) {
            $msg = 'Seleccion&aacute; una categor&iacute;a v&aacute;lida.';
            if ($isJson) { $this->json(['error' => $msg], 422); return; }
            $this->render('documentos/subir', ['error' => $msg, 'categorias' => $this->categorias]);
            return;
        }

        if (!$archivo || $archivo['error'] !== UPLOAD_ERR_OK) {
            $msg = match ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El archivo supera el tama&ntilde;o m&aacute;ximo permitido.',
                UPLOAD_ERR_NO_FILE => 'Seleccion&aacute; un archivo PDF para subir.',
                default => 'Error al subir el archivo. Intentalo de nuevo.',
            };
            if ($isJson) { $this->json(['error' => $msg], 422); return; }
            $this->render('documentos/subir', ['error' => $msg, 'categorias' => $this->categorias]);
            return;
        }

        $mimeType = mime_content_type($archivo['tmp_name']);
        if ($mimeType !== 'application/pdf') {
            $msg = 'El archivo debe ser un PDF v&aacute;lido.';
            if ($isJson) { $this->json(['error' => $msg], 422); return; }
            $this->render('documentos/subir', ['error' => $msg, 'categorias' => $this->categorias]);
            return;
        }

        if ($archivo['size'] > 10 * 1024 * 1024) {
            $msg = 'El archivo supera el tama&ntilde;o m&aacute;ximo de 10 MB.';
            if ($isJson) { $this->json(['error' => $msg], 422); return; }
            $this->render('documentos/subir', ['error' => $msg, 'categorias' => $this->categorias]);
            return;
        }

        $ext = 'pdf';
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($archivo['name'], PATHINFO_FILENAME));
        $safeName = mb_substr($safeName, 0, 80);
        $filename = $safeName . '_' . time() . '.' . $ext;
        $destPath = $this->storageDir . '/' . $filename;

        if (!move_uploaded_file($archivo['tmp_name'], $destPath)) {
            $msg = 'Error al guardar el archivo. Verific&aacute; los permisos del servidor.';
            if ($isJson) { $this->json(['error' => $msg], 500); return; }
            $this->render('documentos/subir', ['error' => $msg, 'categorias' => $this->categorias]);
            return;
        }

        $metaFile = $this->storageDir . '/.meta.json';
        $meta = [];
        if (is_file($metaFile)) {
            $meta = json_decode(file_get_contents($metaFile), true) ?? [];
        }

        $nextId = 1;
        if (!empty($meta)) {
            $ids = array_column($meta, 'id');
            $nextId = max($ids) + 1;
        }

        $meta[] = [
            'id' => $nextId,
            'titulo' => $titulo,
            'categoria' => $categoria,
            'descripcion' => $descripcion,
            'filename' => $filename,
            'subido' => date('d/m/Y'),
            'activo' => true,
        ];

        file_put_contents($metaFile, json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        if ($isJson) {
            $this->json(['success' => true, 'redirect' => '/documentos']);
        } else {
            $this->redirect('/documentos?subido=1');
        }
    }

    private function uploadedDocs(): array
    {
        $metaFile = $this->storageDir . '/.meta.json';
        if (!is_file($metaFile)) return [];
        return json_decode(file_get_contents($metaFile), true) ?? [];
    }

    public function editar(): void
    {
        $this->requireAuth();

        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('/documentos');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEdit($id);
            return;
        }

        $doc = $this->findDocById($id);
        if (!$doc) {
            $this->render('documentos/editar', ['error' => 'Documento no encontrado.', 'doc' => ['id' => 0, 'titulo' => '', 'categoria' => '', 'descripcion' => '', 'filename' => '', 'subido' => ''], 'categorias' => $this->categorias]);
            return;
        }

        $this->render('documentos/editar', [
            'doc' => $doc,
            'categorias' => $this->categorias,
        ]);
    }

    public function eliminar(): void
    {
        $this->requireAuth();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('/documentos');
            return;
        }

        $metaFile = $this->storageDir . '/.meta.json';
        if (is_file($metaFile)) {
            $meta = json_decode(file_get_contents($metaFile), true) ?? [];
            foreach ($meta as &$m) {
                if ($m['id'] === $id) {
                    $m['activo'] = false;
                    break;
                }
            }
            unset($m);
            file_put_contents($metaFile, json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }

        $this->redirect('/documentos?eliminado=1');
    }

    private function handleEdit(int $id): void
    {
        $isJson = str_starts_with($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

        $csrf = trim($_POST['_csrf_token'] ?? '');
        if ($csrf !== ($_SESSION['_csrf_token'] ?? '')) {
            $msg = 'Sesi&oacute;n inv&aacute;lida. Recarg&aacute; e intent&aacute; de nuevo.';
            if ($isJson) { $this->json(['error' => $msg], 403); return; }
            $this->render('documentos/editar', ['error' => $msg, 'doc' => $this->docFallback($id), 'categorias' => $this->categorias]);
            return;
        }

        $titulo = trim($_POST['titulo'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (strlen($titulo) < 3 || strlen($titulo) > 200) {
            $msg = 'El t&iacute;tulo debe tener entre 3 y 200 caracteres.';
            if ($isJson) { $this->json(['error' => $msg], 422); return; }
            $this->render('documentos/editar', ['error' => $msg, 'doc' => $this->docFallback($id), 'categorias' => $this->categorias]);
            return;
        }

        if (strlen($descripcion) > 500) {
            $msg = 'La descripci&oacute;n no puede superar los 500 caracteres.';
            if ($isJson) { $this->json(['error' => $msg], 422); return; }
            $this->render('documentos/editar', ['error' => $msg, 'doc' => $this->docFallback($id), 'categorias' => $this->categorias]);
            return;
        }

        $valida = false;
        foreach ($this->categorias as $cat) {
            if ($cat['nombre'] === $categoria) { $valida = true; break; }
        }
        if (!$valida) {
            $msg = 'Seleccion&aacute; una categor&iacute;a v&aacute;lida.';
            if ($isJson) { $this->json(['error' => $msg], 422); return; }
            $this->render('documentos/editar', ['error' => $msg, 'doc' => $this->docFallback($id), 'categorias' => $this->categorias]);
            return;
        }

        $metaFile = $this->storageDir . '/.meta.json';
        $meta = [];
        $updated = false;

        if (is_file($metaFile)) {
            $meta = json_decode(file_get_contents($metaFile), true) ?? [];
            foreach ($meta as &$m) {
                if ($m['id'] === $id) {
                    $m['titulo'] = $titulo;
                    $m['categoria'] = $categoria;
                    $m['descripcion'] = $descripcion;
                    $updated = true;
                    break;
                }
            }
            unset($m);
        }

        if ($updated) {
            file_put_contents($metaFile, json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }

        if ($isJson) {
            $this->json(['success' => true, 'redirect' => '/documentos']);
        } else {
            $this->redirect('/documentos?editado=1');
        }
    }

    private function findDocById(int $id): ?array
    {
        $all = array_merge($this->mockDocumentos(), $this->uploadedDocs());
        foreach ($all as $d) {
            if ($d['id'] === $id) return $d;
        }
        return null;
    }

    private function docFallback(int $id): array
    {
        return ['id' => $id, 'titulo' => '', 'categoria' => '', 'descripcion' => '', 'filename' => '', 'subido' => ''];
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
