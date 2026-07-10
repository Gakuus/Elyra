<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Domain\Entity\Noticia;
use Elyra\Domain\Entity\Pregunta;
use Elyra\Domain\Entity\Respuesta;
use Elyra\Infrastructure\Persistence\MySQL\DocumentoRepository;
use Elyra\Infrastructure\Persistence\MySQL\EncuestaRepository;
use Elyra\Infrastructure\Persistence\MySQL\NoticiaRepository;
use Elyra\Infrastructure\Persistence\MySQL\UsuarioRepository;
use Elyra\Infrastructure\Service\RateLimiter;
use Elyra\Infrastructure\Service\SessionManager;

class PublicController extends BaseController
{
    private DocumentoRepository $docRepo;
    private UsuarioRepository $usuarioRepo;
    private EncuestaRepository $encuestaRepo;
    private NoticiaRepository $noticiaRepo;

    public function __construct()
    {
        $this->docRepo = new DocumentoRepository();
        $this->usuarioRepo = new UsuarioRepository();
        $this->encuestaRepo = new EncuestaRepository();
        $this->noticiaRepo = new NoticiaRepository();
    }

    public function home(): void
    {
        if (SessionManager::isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        $noticiasSemana = $this->noticiaRepo->findThisWeek();

        $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','setiembre','octubre','noviembre','diciembre'];

        $formatear = function (Noticia $n) use ($meses): array {
            $created = $n->getCreatedAt();
            $fecha = '';
            if ($created) {
                $ts = (int) strtotime($created);
                $fecha = date('j', $ts) . ' de ' . ($meses[(int) date('n', $ts) - 1] ?? '') . ' de ' . date('Y', $ts);
            }
            return [
                'id' => $n->getId(),
                'titulo' => $n->getTitulo(),
                'contenido' => $n->getContenido(),
                'imagen' => $n->getImagen(),
                'creada' => $fecha,
            ];
        };

        $noticiasSemanaArr = array_map($formatear, $noticiasSemana);

        require __DIR__ . '/../../../../views/publico/home.php';
    }

    public function verDocumento(): void
    {
        /** @var string $idStr */
        $idStr = $_GET['id'] ?? 0;
        $id = (int) $idStr;
        if ($id <= 0) {
            http_response_code(404);
            require __DIR__ . '/../../../../views/errors/404.php';
            return;
        }

        $doc = $this->docRepo->findById($id);
        if (!$doc || !$doc->isActivo()) {
            http_response_code(404);
            require __DIR__ . '/../../../../views/errors/404.php';
            return;
        }

        $this->render('publico/documento', ['doc' => $this->docToArray($doc)]);
    }

    public function archivo(): void
    {
        /** @var string $idStr */
        $idStr = $_GET['id'] ?? 0;
        $id = (int) $idStr;

        $doc = $id > 0 ? $this->docRepo->findById($id) : null;
        if (!$doc || !$doc->isActivo()) {
            http_response_code(404);
            require __DIR__ . '/../../../../views/errors/404.php';
            return;
        }

        $contenido = $this->docRepo->getArchivoContent($id);
        if ($contenido === null) {
            http_response_code(404);
            require __DIR__ . '/../../../../views/errors/404.php';
            return;
        }

        $disposition = !empty($_GET['descargar']) ? 'attachment' : 'inline';

        header('Content-Type: application/pdf');
        header('Content-Disposition: ' . $disposition . '; filename="' . $doc->getArchivoNombre() . '"');
        header('Content-Length: ' . strlen($contenido));
        header('Cache-Control: public, max-age=3600');
        echo $contenido; // nosemgrep
        exit;
    }

    public function misDocumentos(): void
    {
        /** @var string $token */
        $token = $_GET['token'] ?? '';
        if ($token === '') {
            http_response_code(404);
            require __DIR__ . '/../../../../views/errors/404.php';
            return;
        }

        $paciente = $this->usuarioRepo->findPacienteByToken($token);
        if (!$paciente) {
            http_response_code(404);
            require __DIR__ . '/../../../../views/errors/404.php';
            return;
        }

        $documentos = $this->docRepo->findByPaciente($paciente->getId() ?? 0);

        $this->render('publico/mis-documentos', [
            'paciente' => ['id' => $paciente->getId(), 'nombre' => $paciente->getApellido() . ', ' . $paciente->getNombre()],
            'documentos' => array_map(fn (\Elyra\Domain\Entity\Documento $d) => $this->docToArray($d), $documentos),
        ]);
    }

    /** @return array<string, mixed> */
    private function docToArray(\Elyra\Domain\Entity\Documento $d): array
    {
        $created = $d->getCreatedAt();

        return [
            'id' => $d->getId(),
            'titulo' => $d->getTitulo(),
            'categoria' => $d->getCategoriaNombre() ?? '',
            'categoria_id' => $d->getCategoriaId(),
            'descripcion' => $d->getDescripcion() ?? '',
            'filename' => $d->getArchivoNombre(),
            'subido' => $created ? date('d/m/Y', (int) strtotime($created)) : '',
            'activo' => $d->isActivo(),
            'especialidad' => $d->getEspecialidadNombre() ?? '',
            'especialidad_id' => $d->getEspecialidadId(),
            'paciente_id' => $d->getPacienteId(),
            'paciente' => $d->getPacienteNombre() ?? '',
            'encuesta_id' => $d->getEncuestaId(),
        ];
    }

    public function mostrarEncuesta(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleResponderEncuesta();
            return;
        }

        /** @var string $idStr */
        $idStr = $_GET['id'] ?? 0;
        $id = (int) $idStr;
        if ($id <= 0) {
            http_response_code(404);
            require __DIR__ . '/../../../../views/errors/404.php';
            return;
        }

        $encuesta = $this->encuestaRepo->findById($id);
        if (!$encuesta || !$encuesta->isActiva()) {
            http_response_code(404);
            require __DIR__ . '/../../../../views/errors/404.php';
            return;
        }

        $preguntas = $this->encuestaRepo->findPreguntasByEncuestaId($id);

        $encuestaArr = [
            'id' => $encuesta->getId(),
            'titulo' => $encuesta->getTitulo(),
            'descripcion' => $encuesta->getDescripcion() ?? '',
            'preguntas' => array_map(function (Pregunta $p) {
                $tipo = $p->getTipo()->value();
                return [
                    'texto' => $p->getTexto(),
                    'tipo' => $tipo === 'texto_libre' ? 'texto' : $tipo,
                    'opciones' => $p->getOpciones(),
                ];
            }, $preguntas),
            'activa' => $encuesta->isActiva(),
            'creada' => $encuesta->getCreatedAt() ? date('d/m/Y', (int) strtotime($encuesta->getCreatedAt())) : '',
        ];

        $this->render('publico/encuesta', ['encuesta' => $encuestaArr]);
    }

    private function handleResponderEncuesta(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (!is_string($ip)) {
            $ip = '127.0.0.1';
        }
        if (!RateLimiter::checkSurveySubmission($ip)) {
            $this->render('publico/encuesta', ['error' => 'Ya respondiste esta encuesta recientemente. Intente de nuevo más tarde.']);
            return;
        }

        /** @var string $idStr */
        $idStr = $_POST['encuesta_id'] ?? 0;
        $id = (int) $idStr;
        $encuesta = $this->encuestaRepo->findById($id);
        if (!$encuesta || !$encuesta->isActiva()) {
            $this->redirect('/publico/encuesta?error=1');
            return;
        }

        $preguntas = $this->encuestaRepo->findPreguntasByEncuestaId($id);
        /** @var array<int, string> $respuestas */
        $respuestas = (array) ($_POST['respuestas'] ?? []);
        $errores = [];

        foreach ($preguntas as $i => $p) {
            $tipo = $p->getTipo()->value();
            $resp = trim((string) ($respuestas[$i] ?? ''));

            if ($tipo === 'multiple_choice') {
                if (empty($resp)) {
                    $errores[] = "Respondé la pregunta " . ($i + 1);
                }
            } elseif ($tipo === 'escala') {
                if ($resp === '' || !in_array((int)$resp, [1, 2, 3, 4, 5])) {
                    $errores[] = "Seleccioná una escala válida en la pregunta " . ($i + 1);
                }
            } elseif ($tipo === 'texto_libre') {
                if (strlen($resp) < 1) {
                    $errores[] = "Completá el texto de la pregunta " . ($i + 1);
                }
            }
        }

        if (!empty($errores)) {
            $encuestaArr = [
                'id' => $encuesta->getId(),
                'titulo' => $encuesta->getTitulo(),
                'descripcion' => $encuesta->getDescripcion() ?? '',
                'preguntas' => array_map(function (Pregunta $p) {
                    $tipo = $p->getTipo()->value();
                    return [
                        'texto' => $p->getTexto(),
                        'tipo' => $tipo === 'texto_libre' ? 'texto' : $tipo,
                        'opciones' => $p->getOpciones(),
                    ];
                }, $preguntas),
                'activa' => $encuesta->isActiva(),
            'creada' => $encuesta->getCreatedAt() ? date('d/m/Y', (int) strtotime($encuesta->getCreatedAt())) : '',
            ];
            $this->render('publico/encuesta', ['encuesta' => $encuestaArr, 'error' => implode('<br>', $errores)]);
            return;
        }

        $sesionToken = bin2hex(random_bytes(16));
        /** @var string|null $tokenPaciente */
        $tokenPaciente = $_GET['token'] ?? null;

        $this->encuestaRepo->saveRespuestasBatch($id, $sesionToken, $tokenPaciente, $respuestas);

        RateLimiter::incrementSurveySubmission($ip);

        $this->redirect('/publico/encuesta?ok=1&id=' . $id);
    }
}
