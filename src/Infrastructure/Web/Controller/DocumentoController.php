<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Application\UseCases\Documento\EditarDocumentoUseCase;
use Elyra\Application\UseCases\Documento\EliminarDocumentoUseCase;
use Elyra\Application\UseCases\Documento\ListarDocumentosUseCase;
use Elyra\Application\UseCases\Documento\SubirDocumentoUseCase;
use Elyra\Application\UseCases\Documento\VerDocumentoUseCase;
use Elyra\Domain\Entity\Categoria;
use Elyra\Domain\Entity\Documento;
use Elyra\Infrastructure\Persistence\MySQL\CategoriaRepository;
use Elyra\Infrastructure\Persistence\MySQL\DocumentoRepository;
use Elyra\Infrastructure\Persistence\MySQL\UsuarioRepository;
use Elyra\Infrastructure\Service\ErrorHandler;
use Elyra\Infrastructure\Service\FileStorageService;
use Elyra\Infrastructure\Service\QRGeneratorService;
use Elyra\Infrastructure\Service\SessionManager;
use Elyra\Infrastructure\Service\Validator;

class DocumentoController extends BaseController
{
    private ListarDocumentosUseCase $listarDocumentos;
    private SubirDocumentoUseCase $subirDocumento;
    private EditarDocumentoUseCase $editarDocumento;
    private EliminarDocumentoUseCase $eliminarDocumento;
    private VerDocumentoUseCase $verDocumento;
    private CategoriaRepository $categoriaRepo;
    private UsuarioRepository $usuarioRepo;

    public function __construct()
    {
        $docRepo = new DocumentoRepository();
        $this->categoriaRepo = new CategoriaRepository();
        $this->usuarioRepo = new UsuarioRepository();

        $fileStorage = new FileStorageService();
        $qrService = new QRGeneratorService();

        $this->listarDocumentos = new ListarDocumentosUseCase($docRepo);
        $this->subirDocumento = new SubirDocumentoUseCase($docRepo, $fileStorage, $qrService);
        $this->editarDocumento = new EditarDocumentoUseCase($docRepo);
        $this->eliminarDocumento = new EliminarDocumentoUseCase($docRepo);
        $this->verDocumento = new VerDocumentoUseCase($docRepo);
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

        $result = $this->listarDocumentos->execute([
            'categoriaId' => $categoriaId,
            'busqueda' => $search,
            'pacienteId' => $pacienteId,
            'page' => $page,
        ]);

        $this->render('documentos/index', [
            'documentos' => array_map(fn(Documento $d) => $this->docToArray($d), $result['documentos']),
            'tiposDocumento' => $this->categoriaArray($this->categoriaRepo->findByTipo('tipo_documento')),
            'total' => $result['total'],
            'pagina' => $result['page'],
            'totalPaginas' => $result['totalPages'],
            'search' => $search,
            'categoriaFiltro' => $categoriaId,
        ]);
    }

    public function generales(): void
    {
        $this->requireAuth();
        $this->denyPaciente();
        $this->requireRole('admin', 'superadmin');

        /** @var string $searchRaw */
        $searchRaw = $_GET['q'] ?? '';
        $search = trim($searchRaw);
        /** @var string $catRaw */
        $catRaw = $_GET['categoria'] ?? '';
        $categoriaId = ($catRaw !== '' && isset($_GET['categoria'])) ? (int) $catRaw : null;
        /** @var string $paginaRaw */
        $paginaRaw = $_GET['pagina'] ?? '1';
        $page = max(1, (int) $paginaRaw);

        $result = $this->listarDocumentos->execute([
            'categoriaId' => $categoriaId,
            'busqueda' => $search,
            'page' => $page,
        ]);

        $this->render('documentos/generales', [
            'documentos' => array_map(fn(Documento $d) => $this->docToArray($d), $result['documentos']),
            'tiposDocumento' => $this->categoriaArray($this->categoriaRepo->findByTipo('tipo_documento')),
            'total' => $result['total'],
            'pagina' => $result['page'],
            'totalPaginas' => $result['totalPages'],
            'search' => $search,
            'categoriaFiltro' => $categoriaId,
        ]);
    }

    public function porPaciente(): void
    {
        $this->requireAuth();
        $this->denyPaciente();
        $this->requireRole('admin', 'superadmin');

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

        $paciente = null;
        $documentos = [];
        $total = 0;
        $totalPaginas = 1;
        $ciError = null;

        if ($ci !== '') {
            $paciente = $this->usuarioRepo->findByDocumentoIdentidad($ci);
            if ($paciente) {
                $result = $this->listarDocumentos->execute([
                    'categoriaId' => $categoriaId,
                    'busqueda' => $search,
                    'pacienteId' => $paciente->getId(),
                    'page' => $page,
                ]);
                $documentos = $result['documentos'];
                $total = $result['total'];
                $totalPaginas = $result['totalPages'];
            } else {
                $ciError = "No se encontr&oacute; paciente con CI {$ci}.";
            }
        }

        $this->render('documentos/por_paciente', [
            'documentos' => array_map(fn(Documento $d) => $this->docToArray($d), $documentos),
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
        $this->requireRole('admin', 'superadmin');

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

        $categoriaId = (int) $categoriaPost;

        /** @var array{name: string, type: string, tmp_name: string, error: int, size: int}|null $archivo */
        $archivo = $_FILES['archivo'] ?? null;
        $uploadOk = $archivo !== null && $archivo['error'] === UPLOAD_ERR_OK;

        if (!$uploadOk) {
            $msg = match ($archivo !== null ? $archivo['error'] : UPLOAD_ERR_NO_FILE) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El archivo supera el tamaño máximo permitido.',
                UPLOAD_ERR_NO_FILE => 'Seleccioná un archivo PDF para subir.',
                default => 'Error al subir el archivo.',
            };
            if ($isJson) {
                $this->json(['error' => $msg], 422);
                return;
            }
            $this->render('documentos/subir', ['error' => $msg] + $this->viewCategorias());
            return;
        }

        $uploadInput = [
            'titulo' => $tituloPost,
            'categoriaId' => $categoriaId,
            'subidoPor' => SessionManager::getUserId() ?? 0,
            'descripcion' => $descripcionPost,
            'archivoTmp' => $archivo['tmp_name'],
            'archivoNombre' => $archivo['name'],
        ];
        if ($especialidadPost !== '') {
            $uploadInput['especialidadId'] = (int) $especialidadPost;
        }
        if ($pacientePost !== '') {
            $uploadInput['pacienteId'] = (int) $pacientePost;
        }

        try {
            $this->subirDocumento->execute($uploadInput);
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            $msg = $e->getMessage();
            if ($isJson) {
                $this->json(['error' => $msg], 422);
                return;
            }
            $this->render('documentos/subir', ['error' => $msg] + $this->viewCategorias());
            return;
        }

        \Elyra\Infrastructure\Service\AuditLogger::logCreate('documento', null, ['titulo' => $tituloPost]);
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
        $this->requireRole('admin', 'superadmin');

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

        $doc = $this->verDocumento->execute(['id' => $id]);
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

        $categoriaId = (int) $categoriaPost;

        $editInput = [
            'id' => $id,
            'titulo' => $tituloPost,
            'descripcion' => $descripcionPost,
            'categoriaId' => $categoriaId,
        ];
        if ($especialidadPost !== '') {
            $editInput['especialidadId'] = (int) $especialidadPost;
        }
        if ($pacientePost !== '') {
            $editInput['pacienteId'] = (int) $pacientePost;
        }

        try {
            $this->editarDocumento->execute($editInput);
        } catch (\InvalidArgumentException | \DomainException $e) {
            $msg = $e->getMessage();
            $existingDoc = $this->verDocumento->execute(['id' => $id]);
            if ($isJson) {
                $this->json(['error' => $msg], 422);
                return;
            }
            $this->render('documentos/editar', ['error' => $msg, 'doc' => $existingDoc ? $this->docToArray($existingDoc) : null] + $this->viewCategorias());
            return;
        }

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
        $this->requireRole('admin', 'superadmin');

        /** @var string $idRaw */
        $idRaw = $_POST['id'] ?? $_GET['id'] ?? '0';
        $id = (int) $idRaw;
        if ($id <= 0) {
            $this->redirect('/documentos/generales');
            return;
        }

        try {
            $this->eliminarDocumento->execute(['id' => $id]);
        } catch (\DomainException $e) {
            // silently ignore
        }

        \Elyra\Infrastructure\Service\AuditLogger::logDelete('documento', (string) $id);
        $this->redirect('/documentos/generales?eliminado=1');
    }

    public function ver(): void
    {
        $this->requireAuth();
        /** @var string $idRaw */
        $idRaw = $_GET['id'] ?? '0';
        $id = (int) $idRaw;

        $doc = $this->verDocumento->execute(['id' => $id]);

        if (SessionManager::isPaciente() && ($doc === null || $doc->getPacienteId() !== SessionManager::getUserId())) {
            $this->redirect('/documentos');
            return;
        }

        $userId = SessionManager::getUserId();
        ErrorHandler::log('INFO', 'Documento visto', [
            'user_id' => $userId,
            'doc_id' => $id,
            'titulo' => $doc?->getTitulo(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ]);

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

        $doc = $this->verDocumento->execute(['id' => $id]);
        if (!$doc) {
            http_response_code(404);
            exit;
        }

        if (SessionManager::isPaciente() && $doc->getPacienteId() !== SessionManager::getUserId()) {
            http_response_code(403);
            exit;
        }

        $userId = SessionManager::getUserId();
        $disposition = !empty($_GET['descargar']) ? 'descarga' : 'vista';
        ErrorHandler::log('INFO', "Documento {$disposition}", [
            'user_id' => $userId,
            'doc_id' => $id,
            'titulo' => $doc->getTitulo(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ]);

        $contenido = $doc->getArchivoContenido();
        if ($contenido === null) {
            $docRepo = new \Elyra\Infrastructure\Persistence\MySQL\DocumentoRepository();
            $contenido = $docRepo->getArchivoContent($id);
        }
        if ($contenido === null) {
            http_response_code(404);
            exit;
        }

        $disposition = !empty($_GET['descargar']) ? 'attachment' : 'inline';
        $safeFilename = preg_replace('/[\r\n"\\\\]/', '', $doc->getArchivoNombre());

        header('Content-Type: application/pdf');
        header('Content-Disposition: ' . $disposition . '; filename="' . $safeFilename . '"');
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

    /** @param list<\Elyra\Domain\Entity\Paciente>|array<int, \Elyra\Domain\Entity\Paciente> $pacientes
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
