<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Domain\Entity\Categoria;
use Elyra\Domain\Entity\Documento;
use Elyra\Domain\Entity\Paciente;
use Elyra\Infrastructure\Persistence\MySQL\CategoriaRepository;
use Elyra\Infrastructure\Persistence\MySQL\DocumentoRepository;
use Elyra\Infrastructure\Persistence\MySQL\UsuarioRepository;
use Elyra\Infrastructure\Service\SessionManager;
use Elyra\Infrastructure\Service\Validator;

class DocumentoController extends BaseController
{
    private DocumentoRepository $docRepo;
    private CategoriaRepository $categoriaRepo;
    private UsuarioRepository $usuarioRepo;
    private string $storageDir;

    public function __construct()
    {
        $this->docRepo = new DocumentoRepository();
        $this->categoriaRepo = new CategoriaRepository();
        $this->usuarioRepo = new UsuarioRepository();
        $this->storageDir = __DIR__ . '/../../../../storage/docs';
    }

    public function index(): void
    {
        $this->requireAuth();

        if (!SessionManager::isPaciente()) {
            $this->redirect('/dashboard');
            return;
        }

        /** @var string $searchRaw */
        $searchRaw = $_GET['q'] ?? '';
        $search = trim($searchRaw);
        /** @var string $catRaw */
        $catRaw = $_GET['categoria'] ?? '';
        $categoriaId = ($catRaw !== '' && isset($_GET['categoria'])) ? (int) $catRaw : null;
        $pacienteId = SessionManager::getUserId();
        /** @var string $paginaRaw */
        $paginaRaw = $_GET['pagina'] ?? '1';
        $page = max(1, (int) $paginaRaw);
        $perPage = 20;

        $documentos = $this->docRepo->findAll($categoriaId, $search ?: null, $pacienteId, $page, $perPage);
        $total = $this->docRepo->count($categoriaId, $search ?: null, $pacienteId);
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

    public function generales(): void
    {
        $this->requireAuth();
        $this->denyPaciente();

        /** @var string $searchRaw */
        $searchRaw = $_GET['q'] ?? '';
        $search = trim($searchRaw);
        /** @var string $catRaw */
        $catRaw = $_GET['categoria'] ?? '';
        $categoriaId = ($catRaw !== '' && isset($_GET['categoria'])) ? (int) $catRaw : null;
        /** @var string $paginaRaw */
        $paginaRaw = $_GET['pagina'] ?? '1';
        $page = max(1, (int) $paginaRaw);
        $perPage = 20;

        $documentos = $this->docRepo->findGenerales($categoriaId, $search ?: null, $page, $perPage);
        $total = $this->docRepo->countGenerales($categoriaId, $search ?: null);
        $totalPaginas = max(1, (int) ceil($total / $perPage));

        $this->render('documentos/generales', [
            'documentos' => array_map(fn (Documento $d) => $this->docToArray($d), $documentos),
            'tiposDocumento' => $this->categoriaArray($this->categoriaRepo->findByTipo('tipo_documento')),
            'total' => $total,
            'pagina' => $page,
            'totalPaginas' => $totalPaginas,
            'search' => $search,
            'categoriaFiltro' => $categoriaId,
        ]);
    }

    public function porPaciente(): void
    {
        $this->requireAuth();
        $this->denyPaciente();

        /** @var string $ciRaw */
        $ciRaw = $_GET['ci'] ?? '';
        $ci = trim($ciRaw);
        /** @var string $searchRaw */
        $searchRaw = $_GET['q'] ?? '';
        $search = trim($searchRaw);
        /** @var string $catRaw */
        $catRaw = $_GET['categoria'] ?? '';
        $categoriaId = ($catRaw !== '' && isset($_GET['categoria'])) ? (int) $catRaw : null;
        /** @var string $paginaRaw */
        $paginaRaw = $_GET['pagina'] ?? '1';
        $page = max(1, (int) $paginaRaw);
        $perPage = 20;

        $paciente = null;
        $documentos = [];
        $total = 0;
        $totalPaginas = 1;
        $ciError = null;

        if ($ci !== '') {
            $paciente = $this->usuarioRepo->findByDocumentoIdentidad($ci);
            if ($paciente) {
                $documentos = $this->docRepo->findAll($categoriaId, $search ?: null, $paciente->getId(), $page, $perPage);
                $total = $this->docRepo->count($categoriaId, $search ?: null, $paciente->getId());
                $totalPaginas = max(1, (int) ceil($total / $perPage));
            } else {
                $ciError = "No se encontr&oacute; paciente con CI {$ci}.";
            }
        }

        $this->render('documentos/por_paciente', [
            'documentos' => array_map(fn (Documento $d) => $this->docToArray($d), $documentos),
            'tiposDocumento' => $this->categoriaArray($this->categoriaRepo->findByTipo('tipo_documento')),
            'total' => $total,
            'pagina' => $page,
            'totalPaginas' => $totalPaginas,
            'search' => $search,
            'categoriaFiltro' => $categoriaId,
            'ci' => $ci,
            'ciError' => $ciError,
            'ciPaciente' => $paciente,
        ]);
    }

    public function subir(): void
    {
        $this->requireAuth();
        $this->denyPaciente();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUpload();
            return;
        }

        $this->render('documentos/subir', $this->viewCategorias());
    }

    private function handleUpload(): void
    {
        /** @var string $httpAccept */
        $httpAccept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $isJson = str_contains($httpAccept, 'application/json');

        /** @var string $tituloPost */
        $tituloPost = $_POST['titulo'] ?? '';
        /** @var string $categoriaPost */
        $categoriaPost = $_POST['categoria'] ?? '';
        /** @var string $especialidadPost */
        $especialidadPost = $_POST['especialidad'] ?? '';
        /** @var string $pacientePost */
        $pacientePost = $_POST['paciente'] ?? '';
        /** @var string $descripcionPost */
        $descripcionPost = $_POST['descripcion'] ?? '';

        $v = new Validator();
        $v->required('titulo', $tituloPost, 'Título')
          ->minLength('titulo', $tituloPost, 3, 'Título')
          ->maxLength('titulo', $tituloPost, 200, 'Título')
          ->numeric('categoria', $categoriaPost, 'Tipo de documento');

        $categoriaId = (int) $categoriaPost;
        if (!$this->categoriaRepo->findById($categoriaId)) {
            $v->required('categoria', null, 'Tipo de documento');
        }

        $especialidadId = null;
        if ($especialidadPost !== '') {
            $especialidadId = (int) $especialidadPost;
            if (!$this->categoriaRepo->findById($especialidadId)) {
                $v->required('especialidad', null, 'Especialidad');
            }
        }

        $pacienteId = $pacientePost !== '' ? (int) $pacientePost : null;

        /** @var array{name: string, type: string, tmp_name: string, error: int, size: int}|null $archivo */
        $archivo = $_FILES['archivo'] ?? null;
        $uploadOk = $archivo !== null && $archivo['error'] === UPLOAD_ERR_OK;

        if (!$v->isValid() || !$uploadOk) {
            $msg = $v->getFirstError() ?? match ($archivo !== null ? $archivo['error'] : UPLOAD_ERR_NO_FILE) {
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

        /** @var string|false $contenidoPdf */
        $contenidoPdf = file_get_contents($archivo['tmp_name']);

        /** @var string|null $safeNameRaw */
        $safeNameRaw = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($archivo['name'], PATHINFO_FILENAME));
        $safeName = is_string($safeNameRaw) ? $safeNameRaw : '';
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
            titulo: Validator::sanitize($tituloPost),
            archivoPath: $destPath,
            archivoNombre: $filename,
            codigoQrId: null,
            categoriaId: $categoriaId,
            subidoPor: SessionManager::getUserId() ?? 0,
            descripcion: Validator::sanitize($descripcionPost),
            especialidadId: $especialidadId,
            pacienteId: $pacienteId,
            activo: true
        );
        $doc->setArchivoContenido($contenidoPdf !== false ? $contenidoPdf : null);

        $this->docRepo->save($doc);

        if ($isJson) {
            $this->json(['success' => true, 'redirect' => '/documentos/generales']);
        } else {
            $this->redirect('/documentos/generales?subido=1');
        }
    }

    public function editar(): void
    {
        $this->requireAuth();
        $this->denyPaciente();

        /** @var string $idRaw */
        $idRaw = $_GET['id'] ?? $_POST['id'] ?? '0';
        $id = (int) $idRaw;
        if ($id <= 0) {
            $this->redirect('/documentos/generales');
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
        /** @var string $httpAccept */
        $httpAccept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $isJson = str_contains($httpAccept, 'application/json');

        /** @var string $tituloPost */
        $tituloPost = $_POST['titulo'] ?? '';
        /** @var string $descripcionPost */
        $descripcionPost = $_POST['descripcion'] ?? '';
        /** @var string $categoriaPost */
        $categoriaPost = $_POST['categoria'] ?? '';
        /** @var string $especialidadPost */
        $especialidadPost = $_POST['especialidad'] ?? '';
        /** @var string $pacientePost */
        $pacientePost = $_POST['paciente'] ?? '';

        $v = new Validator();
        $v->required('titulo', $tituloPost, 'Título')
          ->minLength('titulo', $tituloPost, 3, 'Título')
          ->maxLength('titulo', $tituloPost, 200, 'Título')
          ->maxLength('descripcion', $descripcionPost, 500, 'Descripción')
          ->numeric('categoria', $categoriaPost, 'Tipo de documento');

        $categoriaId = (int) $categoriaPost;
        if (!$this->categoriaRepo->findById($categoriaId)) {
            $v->required('categoria', null, 'Tipo de documento');
        }

        $especialidadId = null;
        if ($especialidadPost !== '') {
            $especialidadId = (int) $especialidadPost;
            if (!$this->categoriaRepo->findById($especialidadId)) {
                $v->required('especialidad', null, 'Especialidad');
            }
        }

        $pacienteId = $pacientePost !== '' ? (int) $pacientePost : null;

        if (!$v->isValid()) {
            $msg = $v->getFirstError();
            if ($isJson) { $this->json(['error' => $msg], 422); return; }
            $existingDoc = $this->docRepo->findById($id);
            $this->render('documentos/editar', ['error' => $msg, 'doc' => $existingDoc ? $this->docToArray($existingDoc) : null] + $this->viewCategorias());
            return;
        }

        $doc = $this->docRepo->findById($id);
        if (!$doc) {
            $msg = 'Documento no encontrado.';
            if ($isJson) { $this->json(['error' => $msg], 404); return; }
            $this->redirect('/documentos/generales');
            return;
        }

        $updated = new Documento(
            id: $doc->getId(),
            titulo: Validator::sanitize($tituloPost),
            archivoPath: $doc->getArchivoPath(),
            archivoNombre: $doc->getArchivoNombre(),
            codigoQrId: $doc->getCodigoQrId(),
            categoriaId: $categoriaId,
            subidoPor: $doc->getSubidoPor(),
            descripcion: Validator::sanitize($descripcionPost),
            qrPath: $doc->getQrPath(),
            especialidadId: $especialidadId,
            encuestaId: $doc->getEncuestaId(),
            pacienteId: $pacienteId,
            activo: true,
            createdAt: $doc->getCreatedAt()
        );

        $this->docRepo->update($updated);

        if ($isJson) {
            $this->json(['success' => true, 'redirect' => '/documentos/generales']);
        } else {
            $this->redirect('/documentos/generales?editado=1');
        }
    }

    public function eliminar(): void
    {
        $this->requireAuth();
        $this->denyPaciente();

        /** @var string $idRaw */
        $idRaw = $_GET['id'] ?? '0';
        $id = (int) $idRaw;
        if ($id <= 0) {
            $this->redirect('/documentos/generales');
            return;
        }

        $this->docRepo->delete($id);
        $this->redirect('/documentos/generales?eliminado=1');
    }

    public function ver(): void
    {
        $this->requireAuth();
        /** @var string $idRaw */
        $idRaw = $_GET['id'] ?? '0';
        $id = (int) $idRaw;

        $doc = $id > 0 ? $this->docRepo->findById($id) : null;

        if (SessionManager::isPaciente() && ($doc === null || $doc->getPacienteId() !== SessionManager::getUserId())) {
            $this->redirect('/documentos');
            return;
        }

        $this->render('documentos/ver', [
            'doc' => $doc ? $this->docToArray($doc) : null,
        ] + $this->viewCategorias());
    }

    public function archivo(): void
    {
        $this->requireAuth();
        /** @var string $idRaw */
        $idRaw = $_GET['id'] ?? '0';
        $id = (int) $idRaw;

        $doc = $id > 0 ? $this->docRepo->findById($id) : null;
        if (!$doc) {
            http_response_code(404);
            exit;
        }

        if (SessionManager::isPaciente() && $doc->getPacienteId() !== SessionManager::getUserId()) {
            http_response_code(403);
            exit;
        }

        $contenido = $this->docRepo->getArchivoContent($id);
        if ($contenido === null) {
            http_response_code(404);
            exit;
        }

        $disposition = !empty($_GET['descargar']) ? 'attachment' : 'inline';

        header('Content-Type: application/pdf');
        header('Content-Disposition: ' . $disposition . '; filename="' . $doc->getArchivoNombre() . '"');
        header('Content-Length: ' . strlen($contenido));
        header('Cache-Control: private, max-age=3600');
        echo $contenido; // nosemgrep
        exit;
    }

    /**
     * @return array{id: int|null, titulo: string, categoria: string, categoria_id: int, descripcion: string, filename: string, subido: string, activo: bool, especialidad: string, especialidad_id: int|null, paciente_id: int|null, paciente: string}
     */
    private function docToArray(Documento $d): array
    {
        $created = $d->getCreatedAt();
        /** @var int|false $timestamp */
        $timestamp = $created ? strtotime($created) : false;

        return [
            'id' => $d->getId(),
            'titulo' => $d->getTitulo(),
            'categoria' => $d->getCategoriaNombre() ?? '',
            'categoria_id' => $d->getCategoriaId(),
            'descripcion' => $d->getDescripcion() ?? '',
            'filename' => $d->getArchivoNombre(),
            'subido' => $timestamp !== false ? date('d/m/Y', $timestamp) : '',
            'activo' => $d->isActivo(),
            'especialidad' => $d->getEspecialidadNombre() ?? '',
            'especialidad_id' => $d->getEspecialidadId(),
            'paciente_id' => $d->getPacienteId(),
            'paciente' => $d->getPacienteNombre() ?? '',
        ];
    }

    /**
     * @return array{especialidades: list<array{id: int|null, nombre: string}>, tiposDocumento: list<array{id: int|null, nombre: string}>, pacientes: list<array{id: int|null, nombre: string}>}
     */
    private function viewCategorias(): array
    {
        return [
            'especialidades' => $this->categoriaArray($this->categoriaRepo->findByTipo('especialidad')),
            'tiposDocumento' => $this->categoriaArray($this->categoriaRepo->findByTipo('tipo_documento')),
            'pacientes' => $this->pacienteArray($this->usuarioRepo->findAllPacientes()),
        ];
    }

    /** @param list<Categoria>|array<int, Categoria> $categorias
     *  @return list<array{id: int|null, nombre: string}>
     */
    private function categoriaArray(array $categorias): array
    {
        /** @var list<array{id: int|null, nombre: string}> */
        return array_values(array_map(fn (Categoria $c) => [
            'id' => $c->getId(),
            'nombre' => $c->getNombre(),
        ], $categorias));
    }

    /** @param list<Paciente>|array<int, Paciente> $pacientes
     *  @return list<array{id: int|null, nombre: string}>
     */
    private function pacienteArray(array $pacientes): array
    {
        /** @var list<array{id: int|null, nombre: string}> */
        return array_values(array_map(fn (\Elyra\Domain\Entity\Paciente $p) => [
            'id' => $p->getId(),
            'nombre' => $p->getApellido() . ', ' . $p->getNombre(),
        ], $pacientes));
    }
}
