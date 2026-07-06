<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

class EncuestaController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();
        $this->render('encuestas/index', [
            'encuestas' => $this->mockEncuestas(),
        ]);
    }

    public function crear(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCrear();
            return;
        }

        $this->render('encuestas/crear');
    }

    private function handleCrear(): void
    {
        $csrf = trim($_POST['_csrf_token'] ?? '');
        if ($csrf !== ($_SESSION['_csrf_token'] ?? '')) {
            $this->render('encuestas/crear', ['error' => 'Sesi&oacute;n inv&aacute;lida. Recarg&aacute; e intent&aacute; de nuevo.']);
            return;
        }

        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $preguntas = $_POST['preguntas'] ?? [];

        if (strlen($titulo) < 3 || strlen($titulo) > 200) {
            $this->render('encuestas/crear', ['error' => 'El t&iacute;tulo debe tener entre 3 y 200 caracteres.']);
            return;
        }

        if (count($preguntas) < 1) {
            $this->render('encuestas/crear', ['error' => 'Agreg&aacute; al menos una pregunta.']);
            return;
        }

        $tiposValidos = ['multiple_choice', 'escala', 'texto'];
        foreach ($preguntas as $i => $p) {
            $texto = trim($p['texto'] ?? '');
            $tipo = trim($p['tipo'] ?? '');

            if (strlen($texto) < 3) {
                $this->render('encuestas/crear', ['error' => "La pregunta $i debe tener al menos 3 caracteres."]);
                return;
            }

            if (!in_array($tipo, $tiposValidos, true)) {
                $this->render('encuestas/crear', ['error' => "Tipo inv&aacute;lido en la pregunta $i."]);
                return;
            }

            if ($tipo === 'multiple_choice') {
                $opciones = array_map('trim', $p['opciones'] ?? []);
                $opciones = array_values(array_filter($opciones));
                if (count($opciones) < 2) {
                    $this->render('encuestas/crear', ['error' => "La pregunta $i necesita al menos 2 opciones."]);
                    return;
                }
            }
        }

        $storageDir = __DIR__ . '/../../../../storage/encuestas';
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0775, true);
        }

        $metaFile = $storageDir . '/.meta.json';
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
            'descripcion' => $descripcion,
            'preguntas' => $preguntas,
            'activa' => true,
            'creada' => date('d/m/Y'),
        ];

        file_put_contents($metaFile, json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $this->redirect('/encuestas?creada=1');
    }

    public function resultados(): void
    {
        $this->requireAuth();
        $this->render('encuestas/resultados');
    }

    private function storedEncuestas(): array
    {
        $metaFile = __DIR__ . '/../../../../storage/encuestas/.meta.json';
        if (!is_file($metaFile)) return [];
        return json_decode(file_get_contents($metaFile), true) ?? [];
    }

    private function mockEncuestas(): array
    {
        $mock = [
            ['id' => 1, 'titulo' => 'Satisfacción general del paciente', 'descripcion' => 'Encuesta para pacientes internados sobre la calidad de atención recibida.', 'preguntas' => 8, 'activa' => true, 'creada' => '10/05/2026'],
            ['id' => 2, 'titulo' => 'Evaluación de enfermería', 'descripcion' => 'Opinión sobre el cuidado y trato del personal de enfermería.', 'preguntas' => 5, 'activa' => true, 'creada' => '12/05/2026'],
            ['id' => 3, 'titulo' => 'Calidad de alimentos', 'descripcion' => 'Encuesta sobre la calidad y variedad de los alimentos servidos.', 'preguntas' => 6, 'activa' => false, 'creada' => '08/05/2026'],
            ['id' => 4, 'titulo' => 'Atención en emergencias', 'descripcion' => 'Tiempo de espera y calidad de atención en el servicio de emergencias.', 'preguntas' => 10, 'activa' => true, 'creada' => '15/05/2026'],
            ['id' => 5, 'titulo' => 'Limpieza e higiene', 'descripcion' => 'Percepción de los pacientes sobre la limpieza de las instalaciones.', 'preguntas' => 4, 'activa' => false, 'creada' => '01/05/2026'],
        ];

        $stored = $this->storedEncuestas();
        foreach ($stored as &$s) {
            $s['preguntas'] = count($s['preguntas'] ?? []);
        }
        unset($s);

        return array_merge($mock, $stored);
    }
}
