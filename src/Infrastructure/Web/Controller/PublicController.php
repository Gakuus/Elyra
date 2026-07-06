<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

class PublicController extends BaseController
{
    public function home(): void
    {
        if (isset($_SESSION['user'])) {
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
        $this->render('publico/encuesta');
    }

    public function enviarEncuesta(): void
    {
        $this->redirect('/publico/encuesta?ok=1');
    }
}
