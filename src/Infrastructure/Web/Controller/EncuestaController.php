<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Domain\Entity\Encuesta;
use Elyra\Domain\Entity\Pregunta;
use Elyra\Domain\ValueObject\TipoPregunta;
use Elyra\Infrastructure\Persistence\MySQL\EncuestaRepository;

class EncuestaController extends BaseController
{
    private EncuestaRepository $encuestaRepo;

    public function __construct()
    {
        $this->encuestaRepo = new EncuestaRepository();
    }

    public function index(): void
    {
        $this->requireAuth();

        $encuestas = $this->encuestaRepo->findAll();
        $lista = [];

        foreach ($encuestas as $e) {
            $preguntas = $this->encuestaRepo->findPreguntasByEncuestaId($e->getId());
            $creada = $e->getCreatedAt();
            $lista[] = [
                'id' => $e->getId(),
                'titulo' => $e->getTitulo(),
                'descripcion' => $e->getDescripcion() ?? '',
                'preguntas' => count($preguntas),
                'activa' => $e->isActiva(),
                'creada' => $creada ? date('d/m/Y', strtotime($creada)) : '',
            ];
        }

        $this->render('encuestas/index', [
            'encuestas' => $lista,
        ]);
    }

    public function crear(): void
    {
        $this->requireAuth();
        $this->denyPaciente();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCrear();
            return;
        }

        $this->render('encuestas/crear');
    }

    private function handleCrear(): void
    {
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $preguntas = $_POST['preguntas'] ?? [];

        if (strlen($titulo) < 3 || strlen($titulo) > 200) {
            $this->render('encuestas/crear', ['error' => 'El título debe tener entre 3 y 200 caracteres.']);
            return;
        }

        if (count($preguntas) < 1) {
            $this->render('encuestas/crear', ['error' => 'Agregá al menos una pregunta.']);
            return;
        }

        $tiposValidos = ['multiple_choice', 'escala', 'texto'];
        $preguntasData = [];

        foreach ($preguntas as $i => $p) {
            $texto = trim($p['texto'] ?? '');
            $tipo = trim($p['tipo'] ?? '');

            if (strlen($texto) < 3) {
                $this->render('encuestas/crear', ['error' => "La pregunta " . ($i + 1) . " debe tener al menos 3 caracteres."]);
                return;
            }

            if (!in_array($tipo, $tiposValidos, true)) {
                $this->render('encuestas/crear', ['error' => "Tipo inválido en la pregunta " . ($i + 1) . "."]);
                return;
            }

            if ($tipo === 'texto') {
                $tipo = 'texto_libre';
            }

            $opciones = null;
            if ($tipo === 'multiple_choice') {
                $opciones = array_map('trim', $p['opciones'] ?? []);
                $opciones = array_values(array_filter($opciones));
                if (count($opciones) < 2) {
                    $this->render('encuestas/crear', ['error' => "La pregunta " . ($i + 1) . " necesita al menos 2 opciones."]);
                    return;
                }
            }

            $preguntasData[] = [
                'tipo' => $tipo,
                'texto' => $texto,
                'opciones' => $opciones,
            ];
        }

        $encuesta = new Encuesta(
            id: null,
            titulo: $titulo,
            creadaPor: (int) ($_SESSION['user_id'] ?? 0),
            descripcion: $descripcion,
            activa: true,
        );

        $encuesta = $this->encuestaRepo->save($encuesta);

        foreach ($preguntasData as $orden => $pd) {
            $tipoVo = new TipoPregunta($pd['tipo']);
            $pregunta = new Pregunta(
                id: null,
                encuestaId: $encuesta->getId(),
                tipo: $tipoVo,
                texto: $pd['texto'],
                orden: $orden,
                opciones: $pd['opciones'],
                requerida: true,
            );
            $this->encuestaRepo->savePregunta($pregunta);
        }

        $this->redirect('/encuestas?creada=1');
    }

    public function resultados(): void
    {
        $this->requireAuth();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('/encuestas');
            return;
        }

        $encuesta = $this->encuestaRepo->findById($id);
        if (!$encuesta) {
            $this->redirect('/encuestas');
            return;
        }

        $preguntas = $this->encuestaRepo->findPreguntasByEncuestaId($id);
        $agrupado = $this->encuestaRepo->findRespuestasAgrupadas($id);

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

        $this->render('encuestas/resultados', [
            'encuesta' => $encuestaArr,
            'totalRespuestas' => $agrupado['totalRespuestas'],
            'stats' => $agrupado['stats'],
        ]);
    }
}
