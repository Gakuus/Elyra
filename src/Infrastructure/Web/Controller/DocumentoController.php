<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Domain\Entity\Categoria;
use Elyra\Domain\Entity\Documento;
use Elyra\Infrastructure\Persistence\MySQL\CategoriaRepository;
use Elyra\Infrastructure\Persistence\MySQL\DocumentoRepository;
use Elyra\Infrastructure\Service\SessionManager;
use Elyra\Infrastructure\Service\Validator;

class DocumentoController extends BaseController
{
    private DocumentoRepository $docRepo;
    private CategoriaRepository $categoriaRepo;
    private string $storageDir;

    public function __construct()
    {
        $this->docRepo = new DocumentoRepository();
        $this->categoriaRepo = new CategoriaRepository();
        $this->storageDir = __DIR__ . '/../../../../storage/docs';
    }

    public function index(): void
    {
        $this->requireAuth();

        $search = trim($_GET['q'] ?? '');
        $categoriaId = isset($_GET['categoria']) && $_GET['categoria'] !== '' ? (int) $_GET['categoria'] : null;
        $page = max(1, (int) ($_GET['pagina'] ?? 1));
        $perPage = 5;

        $documentos = $this->docRepo->findAll($categoriaId, $search ?: null, $page, $perPage);
        $total = $this->docRepo->count($categoriaId, $search ?: null);
        $totalPaginas = max(1, (int) ceil($total / $perPage));

        $this->render('documentos/index', [
            'documentos' => array_map(fn (Documento $d) => $this->docToArray($d), $documentos),
            'tiposDocumento' => $this->categoriaArray($this->categoriaRepo->findByTipo('tipo_documento')),
            'total' => $total,
            'pagina' => $page,
            'totalPaginas' => $totalPaginas,
            'search' => $search,
            'categoriaFiltro' => $categoriaId,
        ]);
    }

    public function subir(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUpload();
            return;
        }

        $this->render('documentos/subir', $this->viewCategorias());
    }

    private function handleUpload(): void
    {
        $isJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

        $v = new Validator();
        $v->required('titulo', $_POST['titulo'] ?? '', 'Título')
          ->minLength('titulo', $_POST['titulo'] ?? '', 3, 'Título')
          ->maxLength('titulo', $_POST['titulo'] ?? '', 200, 'Título')
          ->numeric('categoria', $_POST['categoria'] ?? '', 'Tipo de documento');

        $categoriaId = (int) ($_POST['categoria'] ?? 0);
        if (!$this->categoriaRepo->findById($categoriaId)) {
            $v->required('categoria', null, 'Tipo de documento');
        }

        $especialidadId = null;
        if (!empty($_POST['especialidad'])) {
            $especialidadId = (int) $_POST['especialidad'];
            if (!$this->categoriaRepo->findById($especialidadId)) {
                $v->required('especialidad', null, 'Especialidad');
            }
        }

        $archivo = $_FILES['archivo'] ?? null;
        $uploadOk = $archivo && $archivo['error'] === UPLOAD_ERR_OK;

        if (!$v->isValid() || !$uploadOk) {
            $msg = $v->getFirstError() ?? match ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El archivo supera el tamaño máximo permitido.',
                UPLOAD_ERR_NO_FILE => 'Seleccioná un archivo PDF para subir.',
                default => 'Error al subir el archivo.',
            };
            if ($isJson) { $this->json(['error' => $msg], 422); return; }
            $this->render('documentos/subir', ['error' => $msg] + $this->viewCategorias());
            return;
        }

        $mimeType = mime_content_type($archivo['tmp_name']);
        if ($mimeType !== 'application/pdf' || $archivo['size'] > 10 * 1024 * 1024) {
            $msg = $mimeType !== 'application/pdf' ? 'El archivo debe ser un PDF válido.' : 'El archivo supera el tamaño máximo de 10 MB.';
            if ($isJson) { $this->json(['error' => $msg], 422); return; }
            $this->render('documentos/subir', ['error' => $msg] + $this->viewCategorias());
            return;
        }

        $contenidoPdf = file_get_contents($archivo['tmp_name']);

        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($archivo['name'], PATHINFO_FILENAME));
        $safeName = mb_substr($safeName, 0, 80);
        $filename = $safeName . '_' . time() . '.pdf';
        $destPath = $this->storageDir . '/' . $filename;

        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0775, true);
        }

        if (!move_uploaded_file($archivo['tmp_name'], $destPath)) {
            $msg = 'Error al guardar el archivo. Verificá los permisos del servidor.';
            if ($isJson) { $this->json(['error' => $msg], 500); return; }
            $this->render('documentos/subir', ['error' => $msg] + $this->viewCategorias());
            return;
        }

        $doc = new Documento(
            id: null,
            titulo: Validator::sanitize($_POST['titulo']),
            archivoPath: $destPath,
            archivoNombre: $filename,
            codigoQrId: null,
            categoriaId: $categoriaId,
            subidoPor: SessionManager::getUserId() ?? 0,
            descripcion: Validator::sanitize($_POST['descripcion'] ?? ''),
            especialidadId: $especialidadId,
            activo: true
        );
        $doc->setArchivoContenido($contenidoPdf);

        $this->docRepo->save($doc);

        if ($isJson) {
            $this->json(['success' => true, 'redirect' => '/documentos']);
        } else {
            $this->redirect('/documentos?subido=1');
        }
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

        $doc = $this->docRepo->findById($id);
        if (!$doc) {
            $this->render('documentos/editar', ['error' => 'Documento no encontrado.', 'doc' => null] + $this->viewCategorias());
            return;
        }

        $this->render('documentos/editar', [
            'doc' => $this->docToArray($doc),
        ] + $this->viewCategorias());
    }

    private function handleEdit(int $id): void
    {
        $isJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

        $v = new Validator();
        $v->required('titulo', $_POST['titulo'] ?? '', 'Título')
          ->minLength('titulo', $_POST['titulo'] ?? '', 3, 'Título')
          ->maxLength('titulo', $_POST['titulo'] ?? '', 200, 'Título')
          ->maxLength('descripcion', $_POST['descripcion'] ?? '', 500, 'Descripción')
          ->numeric('categoria', $_POST['categoria'] ?? '', 'Tipo de documento');

        $categoriaId = (int) ($_POST['categoria'] ?? 0);
        if (!$this->categoriaRepo->findById($categoriaId)) {
            $v->required('categoria', null, 'Tipo de documento');
        }

        $especialidadId = null;
        if (!empty($_POST['especialidad'])) {
            $especialidadId = (int) $_POST['especialidad'];
            if (!$this->categoriaRepo->findById($especialidadId)) {
                $v->required('especialidad', null, 'Especialidad');
            }
        }

        if (!$v->isValid()) {
            $msg = $v->getFirstError();
            if ($isJson) { $this->json(['error' => $msg], 422); return; }
            $this->render('documentos/editar', ['error' => $msg, 'doc' => $this->docToArray($this->docRepo->findById($id))] + $this->viewCategorias());
            return;
        }

        $doc = $this->docRepo->findById($id);
        if (!$doc) {
            $msg = 'Documento no encontrado.';
            if ($isJson) { $this->json(['error' => $msg], 404); return; }
            $this->redirect('/documentos');
            return;
        }

        $updated = new Documento(
            id: $doc->getId(),
            titulo: Validator::sanitize($_POST['titulo']),
            archivoPath: $doc->getArchivoPath(),
            archivoNombre: $doc->getArchivoNombre(),
            codigoQrId: $doc->getCodigoQrId(),
            categoriaId: $categoriaId,
            subidoPor: $doc->getSubidoPor(),
            descripcion: Validator::sanitize($_POST['descripcion'] ?? ''),
            qrPath: $doc->getQrPath(),
            especialidadId: $especialidadId,
            encuestaId: $doc->getEncuestaId(),
            activo: true,
            createdAt: $doc->getCreatedAt()
        );

        $this->docRepo->update($updated);

        if ($isJson) {
            $this->json(['success' => true, 'redirect' => '/documentos']);
        } else {
            $this->redirect('/documentos?editado=1');
        }
    }

    public function eliminar(): void
    {
        $this->requireAuth();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('/documentos');
            return;
        }

        $this->docRepo->delete($id);
        $this->redirect('/documentos?eliminado=1');
    }

    public function ver(): void
    {
        $this->requireAuth();
        $id = (int) ($_GET['id'] ?? 0);

        $doc = $id > 0 ? $this->docRepo->findById($id) : null;

        $this->render('documentos/ver', [
            'doc' => $doc ? $this->docToArray($doc) : null,
        ] + $this->viewCategorias());
    }

    public function archivo(): void
    {
        $this->requireAuth();
        $id = (int) ($_GET['id'] ?? 0);

        $doc = $id > 0 ? $this->docRepo->findById($id) : null;
        if (!$doc) {
            http_response_code(404);
            exit;
        }

        $contenido = $this->docRepo->getArchivoContent($id);
        if ($contenido === null) {
            http_response_code(404);
            exit;
        }

        $mime = 'application/pdf';

        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . $doc->getArchivoNombre() . '"');
        header('Content-Length: ' . strlen($contenido));
        header('Cache-Control: private, max-age=3600');
        echo $contenido;
        exit;
    }

    private function docToArray(Documento $d): array
    {
        $created = $d->getCreatedAt();

        return [
            'id' => $d->getId(),
            'titulo' => $d->getTitulo(),
            'categoria' => $d->getCategoriaNombre() ?? '',
            'categoria_id' => $d->getCategoriaId(),
            'descripcion' => $d->getDescripcion() ?? '',
            'filename' => $d->getArchivoNombre(),
            'subido' => $created ? date('d/m/Y', strtotime($created)) : '',
            'activo' => $d->isActivo(),
            'especialidad' => $d->getEspecialidadNombre() ?? '',
            'especialidad_id' => $d->getEspecialidadId(),
        ];
    }

    private function viewCategorias(): array
    {
        return [
            'especialidades' => $this->categoriaArray($this->categoriaRepo->findByTipo('especialidad')),
            'tiposDocumento' => $this->categoriaArray($this->categoriaRepo->findByTipo('tipo_documento')),
        ];
    }

    private function categoriaArray(array $categorias): array
    {
        return array_map(fn (Categoria $c) => [
            'id' => $c->getId(),
            'nombre' => $c->getNombre(),
        ], $categorias);
    }
}
