<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Infrastructure\Persistence\MySQL\ConductorRepository;
use Elyra\Infrastructure\Persistence\MySQL\DocumentoRepository;
use Elyra\Infrastructure\Persistence\MySQL\EncuestaRepository;
use Elyra\Infrastructure\Persistence\MySQL\TrasladoRepository;
use Elyra\Infrastructure\Service\SessionManager;

class DashboardController extends BaseController
{
    private DocumentoRepository $docRepo;
    private EncuestaRepository $encuestaRepo;
    private TrasladoRepository $trasladoRepo;
    private ConductorRepository $conductorRepo;

    public function __construct()
    {
        $this->docRepo = new DocumentoRepository();
        $this->encuestaRepo = new EncuestaRepository();
        $this->trasladoRepo = new TrasladoRepository();
        $this->conductorRepo = new ConductorRepository();
    }

    public function index(): void
    {
        $this->requireAuth();

        if (SessionManager::isPaciente()) {
            $this->pacienteDashboard();
            return;
        }

        $this->adminDashboard();
    }

    private function pacienteDashboard(): void
    {
        $userId = SessionManager::getUserId() ?? 0;

        $totalDocs = $this->docRepo->countByPaciente($userId);
        $recientes = $this->docRepo->findByPaciente($userId, null, null, 1, 5);

        $this->render('dashboard/paciente', [
            'totalDocs' => $totalDocs,
            'recientes' => array_map(fn ($d) => [
                'id' => $d->getId(),
                'titulo' => $d->getTitulo(),
                'categoria' => $d->getCategoriaNombre() ?? '',
                'subido' => $d->getCreatedAt() ? date('d/m/Y', (int) strtotime($d->getCreatedAt())) : '',
            ], $recientes),
        ]);
    }

    private function adminDashboard(): void
    {
        $totalDocs = $this->docRepo->count();
        $totalGenerales = $this->docRepo->countGenerales();
        $totalEncuestas = $this->encuestaRepo->countTotal();
        $totalRespuestas = $this->encuestaRepo->countRespuestas();
        $totalTraslados = $this->trasladoRepo->countTotal();
        $totalConductores = $this->conductorRepo->countTotal();
        $totalActivos = $this->trasladoRepo->count() - $this->trasladoRepo->countByEstado('completado') - $this->trasladoRepo->countByEstado('cancelado');

        $recientes = $this->docRepo->findAll(null, null, null, 1, 5);

        $docsPorCategoria = $this->docRepo->countByCategoria();
        $trasladosPorMes = $this->trasladoRepo->countByMonth();

        $this->render('dashboard/home', [
            'totalDocs' => $totalDocs,
            'totalGenerales' => $totalGenerales,
            'totalEncuestas' => $totalEncuestas,
            'totalRespuestas' => $totalRespuestas,
            'totalTraslados' => $totalTraslados,
            'totalActivos' => $totalActivos,
            'totalConductores' => $totalConductores,
            'docsPorCategoria' => $docsPorCategoria,
            'trasladosPorMes' => $trasladosPorMes,
            'recientes' => array_map(fn ($d) => [
                'id' => $d->getId(),
                'titulo' => $d->getTitulo(),
                'categoria' => $d->getCategoriaNombre() ?? '',
                'subido' => $d->getCreatedAt() ? date('d/m/Y', (int) strtotime($d->getCreatedAt())) : '',
                'activo' => $d->isActivo(),
            ], $recientes),
        ]);
    }
}
