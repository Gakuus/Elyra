<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

class DocumentoController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();

        $categorias = [
            ['id' => 1, 'nombre' => 'Cardiología'],
            ['id' => 2, 'nombre' => 'Nefrología'],
            ['id' => 3, 'nombre' => 'Imagenología'],
            ['id' => 4, 'nombre' => 'Ginecología'],
            ['id' => 5, 'nombre' => 'Cirugía'],
            ['id' => 6, 'nombre' => 'Enfermería'],
        ];

        $documentos = [
            [
                'id' => 1, 'titulo' => 'Indicaciones pre-operatorias', 'categoria' => 'Cirugía',
                'subido' => '15/05/2026', 'activo' => true,
            ],
            [
                'id' => 2, 'titulo' => 'Preparación para estudios imagenológicos', 'categoria' => 'Imagenología',
                'subido' => '14/05/2026', 'activo' => true,
            ],
            [
                'id' => 3, 'titulo' => 'Plan de alta enfermería - Nefrología', 'categoria' => 'Nefrología',
                'subido' => '12/05/2026', 'activo' => true,
            ],
            [
                'id' => 4, 'titulo' => 'Cuidados post-operatorios cardiovasculares', 'categoria' => 'Cardiología',
                'subido' => '10/05/2026', 'activo' => true,
            ],
            [
                'id' => 5, 'titulo' => 'Guía de preparación para cirugía ginecológica', 'categoria' => 'Ginecología',
                'subido' => '08/05/2026', 'activo' => true,
            ],
            [
                'id' => 6, 'titulo' => 'Indicaciones ecocardiograma con dobutamina', 'categoria' => 'Cardiología',
                'subido' => '06/05/2026', 'activo' => true,
            ],
            [
                'id' => 7, 'titulo' => 'Prevención de infecciones intrahospitalarias', 'categoria' => 'Enfermería',
                'subido' => '04/05/2026', 'activo' => false,
            ],
            [
                'id' => 8, 'titulo' => 'Indicaciones para ingreso a centro de nefrología', 'categoria' => 'Nefrología',
                'subido' => '02/05/2026', 'activo' => true,
            ],
        ];

        $search = trim($_GET['q'] ?? '');
        $categoriaFiltro = trim($_GET['categoria'] ?? '');
        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
        $porPagina = 5;

        if ($search) {
            $documentos = array_values(array_filter($documentos, function ($d) use ($search) {
                return stripos($d['titulo'], $search) !== false;
            }));
        }

        if ($categoriaFiltro) {
            $documentos = array_values(array_filter($documentos, function ($d) use ($categoriaFiltro) {
                return $d['categoria'] === $categoriaFiltro;
            }));
        }

        $total = count($documentos);
        $totalPaginas = max(1, (int) ceil($total / $porPagina));
        $pagina = min($pagina, $totalPaginas);
        $offset = ($pagina - 1) * $porPagina;
        $paginaDocs = array_slice($documentos, $offset, $porPagina);

        $this->render('documentos/index', [
            'documentos' => $paginaDocs,
            'categorias' => $categorias,
            'total' => $total,
            'pagina' => $pagina,
            'totalPaginas' => $totalPaginas,
            'search' => $search,
            'categoriaFiltro' => $categoriaFiltro,
        ]);
    }

    public function subir(): void
    {
        $this->requireAuth();
        $this->render('documentos/subir');
    }

    public function editar(): void
    {
        $this->requireAuth();
        $this->render('documentos/editar');
    }

    public function eliminar(): void
    {
        $this->requireAuth();
        $this->redirect('/documentos');
    }

    public function ver(): void
    {
        $this->requireAuth();
        $this->render('documentos/ver');
    }
}
