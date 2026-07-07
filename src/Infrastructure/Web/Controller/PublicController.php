<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Infrastructure\Persistence\MySQL\DocumentoRepository;
use Elyra\Infrastructure\Persistence\MySQL\EncuestaRepository;
use Elyra\Infrastructure\Persistence\MySQL\UsuarioRepository;
use Elyra\Domain\Entity\Pregunta;
use Elyra\Domain\Entity\Respuesta;
use Elyra\Infrastructure\Service\SessionManager;

class PublicController extends BaseController
{
    private DocumentoRepository $docRepo;
    private UsuarioRepository $usuarioRepo;
    private EncuestaRepository $encuestaRepo;

    public function __construct()
    {
        $this->docRepo = new DocumentoRepository();
        $this->usuarioRepo = new UsuarioRepository();
        $this->encuestaRepo = new EncuestaRepository();
    }

    public function home(): void
    {
        if (SessionManager::isAuthenticated()) {
            $this->redirect('/dashboard');
        }
        require __DIR__ . '/../../../../views/publico/home.php';
    }

    public function verDocumento(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
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
        $id = (int) ($_GET['id'] ?? 0);

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
        echo $contenido;
        exit;
    }

    public function misDocumentos(): void
    {
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
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

        $documentos = $this->docRepo->findByPaciente($paciente->getId());

        $this->render('publico/mis-documentos', [
            'paciente' => ['id' => $paciente->getId(), 'nombre' => $paciente->getApellido() . ', ' . $paciente->getNombre()],
            'documentos' => array_map(fn (\Elyra\Domain\Entity\Documento $d) => $this->docToArray($d), $documentos),
        ]);
    }

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
            'subido' => $created ? date('d/m/Y', strtotime($created)) : '',
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

        $id = (int) ($_GET['id'] ?? 0);
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
            'creada' => $encuesta->getCreatedAt() ? date('d/m/Y', strtotime($encuesta->getCreatedAt())) : '',
        ];

        $this->render('publico/encuesta', ['encuesta' => $encuestaArr]);
    }

    private function handleResponderEncuesta(): void
    {
        $id = (int) ($_POST['encuesta_id'] ?? 0);
        $encuesta = $this->encuestaRepo->findById($id);
        if (!$encuesta || !$encuesta->isActiva()) {
            $this->redirect('/publico/encuesta?error=1');
            return;
        }

        $preguntas = $this->encuestaRepo->findPreguntasByEncuestaId($id);
        $respuestas = $_POST['respuestas'] ?? [];
        $errores = [];

        foreach ($preguntas as $i => $p) {
            $tipo = $p->getTipo()->value();
            $resp = trim($respuestas[$i] ?? '');

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
                'creada' => $encuesta->getCreatedAt() ? date('d/m/Y', strtotime($encuesta->getCreatedAt())) : '',
            ];
            $this->render('publico/encuesta', ['encuesta' => $encuestaArr, 'error' => implode('<br>', $errores)]);
            return;
        }

        $sesionToken = bin2hex(random_bytes(16));
        $tokenPaciente = $_GET['token'] ?? null;

        $this->encuestaRepo->saveRespuestasBatch($id, $sesionToken, $tokenPaciente, $respuestas);

        $this->redirect('/publico/encuesta?ok=1&id=' . $id);
    }
}
