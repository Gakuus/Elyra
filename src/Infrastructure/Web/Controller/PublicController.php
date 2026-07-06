<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

class PublicController extends BaseController
{
    public function home(): void
    {
        if (\Elyra\Infrastructure\Service\SessionManager::isAuthenticated()) {
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

        $doc = $this->findDocById($id);
        if (!$doc || empty($doc['activo'])) {
            http_response_code(404);
            require __DIR__ . '/../../../../views/errors/404.php';
            return;
        }

        $this->render('publico/documento', ['doc' => $doc]);
    }

    private function findDocById(int $id): ?array
    {
        $storageDir = __DIR__ . '/../../../../storage/uploads/documents';
        $metaFile = $storageDir . '/.meta.json';
        $meta = [];
        if (is_file($metaFile)) {
            $meta = json_decode(file_get_contents($metaFile), true) ?? [];
        }

        $mock = [
            ['id' => 1, 'titulo' => 'Indicaciones pre-operatorias', 'categoria' => 'Cirugía', 'subido' => '15/05/2026', 'activo' => true],
            ['id' => 2, 'titulo' => 'Preparación para estudios imagenológicos', 'categoria' => 'Imagenología', 'subido' => '14/05/2026', 'activo' => true],
            ['id' => 3, 'titulo' => 'Plan de alta enfermería - Nefrología', 'categoria' => 'Nefrología', 'subido' => '12/05/2026', 'activo' => true],
            ['id' => 4, 'titulo' => 'Cuidados post-operatorios cardiovasculares', 'categoria' => 'Cardiología', 'subido' => '10/05/2026', 'activo' => true],
            ['id' => 5, 'titulo' => 'Guía de preparación para cirugía ginecológica', 'categoria' => 'Ginecología', 'subido' => '08/05/2026', 'activo' => true],
            ['id' => 6, 'titulo' => 'Indicaciones ecocardiograma con dobutamina', 'categoria' => 'Cardiología', 'subido' => '06/05/2026', 'activo' => true],
            ['id' => 7, 'titulo' => 'Prevención de infecciones intrahospitalarias', 'categoria' => 'Enfermería', 'subido' => '04/05/2026', 'activo' => false],
            ['id' => 8, 'titulo' => 'Indicaciones para ingreso a centro de nefrología', 'categoria' => 'Nefrología', 'subido' => '02/05/2026', 'activo' => true],
        ];

        $all = array_merge($mock, $meta);
        foreach ($all as $d) {
            if ($d['id'] === $id) return $d;
        }
        return null;
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

        $encuesta = $this->findEncuestaById($id);
        if (!$encuesta || empty($encuesta['activa'])) {
            http_response_code(404);
            require __DIR__ . '/../../../../views/errors/404.php';
            return;
        }

        $this->render('publico/encuesta', ['encuesta' => $encuesta]);
    }

    private function handleResponderEncuesta(): void
    {
        $id = (int) ($_POST['encuesta_id'] ?? 0);
        $encuesta = $this->findEncuestaById($id);
        if (!$encuesta || empty($encuesta['activa'])) {
            $this->redirect('/publico/encuesta?error=1');
            return;
        }

        $respuestas = $_POST['respuestas'] ?? [];
        $errores = [];

        foreach ($encuesta['preguntas'] as $i => $p) {
            $resp = trim($respuestas[$i] ?? '');

            if ($p['tipo'] === 'multiple_choice') {
                if (empty($resp)) {
                    $errores[] = "Respond&eacute; la pregunta " . ($i + 1);
                }
            } elseif ($p['tipo'] === 'escala') {
                if ($resp === '' || !in_array((int)$resp, [1, 2, 3, 4, 5])) {
                    $errores[] = "Seleccion&aacute; una escala v&aacute;lida en la pregunta " . ($i + 1);
                }
            } elseif ($p['tipo'] === 'texto') {
                if (strlen($resp) < 1) {
                    $errores[] = "Complet&aacute; el texto de la pregunta " . ($i + 1);
                }
            }
        }

        if (!empty($errores)) {
            $this->render('publico/encuesta', ['encuesta' => $encuesta, 'error' => implode('<br>', $errores)]);
            return;
        }

        $dir = __DIR__ . '/../../../../storage/encuestas/respuestas';
        if (!is_dir($dir)) mkdir($dir, 0775, true);

        $file = $dir . '/' . $id . '.json';
        $all = [];
        if (is_file($file)) {
            $all = json_decode(file_get_contents($file), true) ?? [];
        }

        $all[] = [
            'fecha' => date('d/m/Y H:i:s'),
            'respuestas' => $respuestas,
        ];

        file_put_contents($file, json_encode($all, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $this->redirect('/publico/encuesta?ok=1&id=' . $id);
    }

    private function findEncuestaById(int $id): ?array
    {
        $storageDir = __DIR__ . '/../../../../storage/encuestas';
        $metaFile = $storageDir . '/.meta.json';
        $meta = [];
        if (is_file($metaFile)) {
            $meta = json_decode(file_get_contents($metaFile), true) ?? [];
        }

        $mock = [
            ['id' => 1, 'titulo' => 'Satisfacción general del paciente', 'descripcion' => 'Encuesta para pacientes internados sobre la calidad de atención recibida.', 'preguntas' => [
                ['texto' => '¿Cómo calificarías la atención general recibida?', 'tipo' => 'escala'],
                ['texto' => '¿El personal médico te explicó claramente tu diagnóstico?', 'tipo' => 'multiple_choice', 'opciones' => ['Sí, completamente', 'Sí, parcialmente', 'No']],
                ['texto' => '¿Recomendarías este hospital a otros pacientes?', 'tipo' => 'multiple_choice', 'opciones' => ['Sí', 'No estoy seguro', 'No']],
            ], 'activa' => true, 'creada' => '10/05/2026'],
            ['id' => 2, 'titulo' => 'Evaluación de enfermería', 'descripcion' => 'Opinión sobre el cuidado y trato del personal de enfermería.', 'preguntas' => [
                ['texto' => '¿El trato del personal de enfermería fue respetuoso?', 'tipo' => 'escala'],
                ['texto' => 'Dejanos tu comentario sobre el servicio de enfermería', 'tipo' => 'texto'],
            ], 'activa' => true, 'creada' => '12/05/2026'],
            ['id' => 3, 'titulo' => 'Calidad de alimentos', 'descripcion' => 'Encuesta sobre la calidad y variedad de los alimentos servidos.', 'preguntas' => [
                ['texto' => '¿Cómo calificarías la calidad de la comida?', 'tipo' => 'escala'],
            ], 'activa' => false, 'creada' => '08/05/2026'],
            ['id' => 4, 'titulo' => 'Atención en emergencias', 'descripcion' => 'Tiempo de espera y calidad de atención en el servicio de emergencias.', 'preguntas' => [
                ['texto' => '¿Cuánto tiempo esperaste para ser atendido?', 'tipo' => 'multiple_choice', 'opciones' => ['Menos de 15 min', '15-30 min', '30-60 min', 'M&aacute;s de 60 min']],
                ['texto' => '¿El personal de emergencias fue eficiente?', 'tipo' => 'escala'],
            ], 'activa' => true, 'creada' => '15/05/2026'],
            ['id' => 5, 'titulo' => 'Limpieza e higiene', 'descripcion' => 'Percepción de los pacientes sobre la limpieza de las instalaciones.', 'preguntas' => [
                ['texto' => '¿Cómo calificarías la limpieza de las instalaciones?', 'tipo' => 'escala'],
            ], 'activa' => false, 'creada' => '01/05/2026'],
        ];

        $all = array_merge($mock, $meta);
        foreach ($all as $e) {
            if ($e['id'] === $id) return $e;
        }
        return null;
    }
}
