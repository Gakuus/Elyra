<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

class DashboardController extends BaseController
{
    private function mockDocumentos(): array
    {
        return [
            ['id' => 1, 'titulo' => 'Indicaciones pre-operatorias', 'categoria' => 'Cirugía', 'subido' => '15/05/2026', 'activo' => true],
            ['id' => 2, 'titulo' => 'Preparación para estudios imagenológicos', 'categoria' => 'Imagenología', 'subido' => '14/05/2026', 'activo' => true],
            ['id' => 3, 'titulo' => 'Plan de alta enfermería - Nefrología', 'categoria' => 'Nefrología', 'subido' => '12/05/2026', 'activo' => true],
            ['id' => 4, 'titulo' => 'Cuidados post-operatorios cardiovasculares', 'categoria' => 'Cardiología', 'subido' => '10/05/2026', 'activo' => true],
            ['id' => 5, 'titulo' => 'Guía de preparación para cirugía ginecológica', 'categoria' => 'Ginecología', 'subido' => '08/05/2026', 'activo' => true],
            ['id' => 6, 'titulo' => 'Indicaciones ecocardiograma con dobutamina', 'categoria' => 'Cardiología', 'subido' => '06/05/2026', 'activo' => true],
            ['id' => 7, 'titulo' => 'Prevención de infecciones intrahospitalarias', 'categoria' => 'Enfermería', 'subido' => '04/05/2026', 'activo' => false],
            ['id' => 8, 'titulo' => 'Indicaciones para ingreso a centro de nefrología', 'categoria' => 'Nefrología', 'subido' => '02/05/2026', 'activo' => true],
        ];
    }

    private function uploadedDocs(): array
    {
        $metaFile = __DIR__ . '/../../../../storage/uploads/documents/.meta.json';
        if (!is_file($metaFile)) return [];
        return json_decode(file_get_contents($metaFile), true) ?? [];
    }

    public function index(): void
    {
        $this->requireAuth();

        $mock = $this->mockDocumentos();
        $uploaded = $this->uploadedDocs();
        $allDocs = array_merge($mock, $uploaded);

        $totalDocs = count(array_filter($allDocs, fn($d) => $d['activo']));
        $totalEncuestas = 0;
        $totalTraslados = 0;
        $totalConductores = 0;

        $recientes = array_filter($allDocs, fn($d) => $d['activo']);
        usort($recientes, function ($a, $b) {
            $fa = \DateTime::createFromFormat('d/m/Y', $a['subido']);
            $fb = \DateTime::createFromFormat('d/m/Y', $b['subido']);
            if (!$fa || !$fb) return 0;
            return $fb <=> $fa;
        });
        $recientes = array_slice($recientes, 0, 5);

        $this->render('dashboard/index', [
            'totalDocs' => $totalDocs,
            'totalEncuestas' => $totalEncuestas,
            'totalTraslados' => $totalTraslados,
            'totalConductores' => $totalConductores,
            'recientes' => $recientes,
        ]);
    }
}
