<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Application\UseCases\Encuesta\CrearEncuestaUseCase;
use Elyra\Application\UseCases\Encuesta\ObtenerResultadosUseCase;
use Elyra\Domain\Entity\Encuesta;
use Elyra\Domain\Entity\Pregunta;
use Elyra\Infrastructure\Persistence\MySQL\EncuestaRepository;
use Elyra\Infrastructure\Service\SessionManager;

class EncuestaController extends BaseController
{
    private CrearEncuestaUseCase $crearEncuesta;
    private ObtenerResultadosUseCase $obtenerResultados;
    private EncuestaRepository $encuestaRepo;

    public function __construct()
    {
        $this->encuestaRepo = new EncuestaRepository();
        $this->crearEncuesta = new CrearEncuestaUseCase($this->encuestaRepo);
        $this->obtenerResultados = new ObtenerResultadosUseCase($this->encuestaRepo);
    }

    public function index(): void
    {
        $this->requireAuth();

        $encuestas = $this->encuestaRepo->findAll();
        $lista = [];

        foreach ($encuestas as $e) {
            $encuestaId = $e->getId();
            if ($encuestaId === null) {
                continue;
            }
            $preguntas = $this->encuestaRepo->findPreguntasByEncuestaId($encuestaId);
            $creada = $e->getCreatedAt();
            $lista[] = [
                'id' => $encuestaId,
                'titulo' => $e->getTitulo(),
                'descripcion' => $e->getDescripcion() ?? '',
                'preguntas' => count($preguntas),
                'activa' => $e->isActiva(),
                'creada' => $creada ? date('d/m/Y', (int) strtotime($creada)) : '',
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
        /** @var string $tituloInput */
        $tituloInput = $_POST['titulo'] ?? '';
        $titulo = trim($tituloInput);
        /** @var string $descripcionInput */
        $descripcionInput = $_POST['descripcion'] ?? '';
        $descripcion = trim($descripcionInput);
        $preguntas = (array) ($_POST['preguntas'] ?? []);

        /** @var list<array{tipo: string, texto: string, opciones?: list<string>}> $preguntasData */
        $preguntasData = [];
        foreach ($preguntas as $p) {
            /** @var array{texto?: string, tipo?: string, opciones?: mixed} $p */
            $tipoP = $p['tipo'] ?? 'texto_libre';
            $textoP = $p['texto'] ?? '';
            if ($tipoP === 'multiple_choice' && isset($p['opciones'])) {
                /** @var list<string> $opcionesRaw */
                $opcionesRaw = (array) $p['opciones'];
                $raw = array_map(fn(string $o) => trim($o), $opcionesRaw);
                $preguntasData[] = [
                    'tipo' => $tipoP,
                    'texto' => $textoP,
                    'opciones' => array_values(array_filter($raw)),
                ];
            } else {
                $preguntasData[] = [
                    'tipo' => $tipoP,
                    'texto' => $textoP,
                ];
            }
        }

        $userId = SessionManager::getUserId() ?? 0;

        try {
            $this->crearEncuesta->execute([
                'titulo' => $titulo,
                'creadaPor' => $userId,
                'descripcion' => $descripcion,
                'preguntas' => $preguntasData,
            ]);
        } catch (\InvalidArgumentException | \DomainException $e) {
            $this->render('encuestas/crear', ['error' => $e->getMessage()]);
            return;
        }

        $this->redirect('/encuestas?creada=1');
    }

    public function resultados(): void
    {
        $this->requireAuth();

        /** @var string $idStr */
        $idStr = $_GET['id'] ?? '0';
        $id = (int) $idStr;
        if ($id <= 0) {
            $this->redirect('/encuestas');
            return;
        }

        $result = $this->obtenerResultados->execute(['id' => $id]);
        if ($result === null) {
            $this->redirect('/encuestas');
            return;
        }

        $encuesta = $result['encuesta'];
        $creada = $encuesta->getCreatedAt();

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
            }, $result['preguntas']),
            'activa' => $encuesta->isActiva(),
            'creada' => $creada ? date('d/m/Y', (int) strtotime($creada)) : '',
        ];

        $stats = array_map(function (array $s) {
            $datos = in_array($s['tipo'], ['texto', 'texto_libre'], true) ? $s['textosLibres'] : $s['conteo'];
            $promedio = 0;
            if ($s['tipo'] === 'escala' && $s['conteo'] !== []) {
                $suma = 0;
                $total = 0;
                foreach ($s['conteo'] as $val => $cant) {
                    $suma += (float) $val * $cant;
                    $total += $cant;
                }
                $promedio = $total > 0 ? round($suma / $total, 1) : 0;
            }
            $tipo = $s['tipo'] === 'texto_libre' ? 'texto' : $s['tipo'];
            return [
                'preguntaId' => $s['preguntaId'],
                'texto' => $s['texto'],
                'tipo' => $tipo,
                'datos' => $datos,
                'promedio' => $promedio,
                'total' => $s['total'],
            ];
        }, $result['stats']);

        $this->render('encuestas/resultados', [
            'encuesta' => $encuestaArr,
            'totalRespuestas' => $result['totalRespuestas'],
            'stats' => $stats,
        ]);
    }
}
