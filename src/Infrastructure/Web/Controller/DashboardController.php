<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Infrastructure\Persistence\MySQL\DocumentoRepository;
use Elyra\Infrastructure\Service\SessionManager;

class DashboardController extends BaseController
{
    private DocumentoRepository $docRepo;

    public function __construct()
    {
        $this->docRepo = new DocumentoRepository();
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
        $userId = SessionManager::getUserId();

        $totalDocs = $this->docRepo->countByPaciente($userId);
        $recientes = $this->docRepo->findByPaciente($userId, null, null, 1, 5);

        $this->render('dashboard/paciente', [
            'totalDocs' => $totalDocs,
            'recientes' => array_map(fn ($d) => [
                'id' => $d->getId(),
                'titulo' => $d->getTitulo(),
                'categoria' => $d->getCategoriaNombre() ?? '',
                'subido' => $d->getCreatedAt() ? date('d/m/Y', strtotime($d->getCreatedAt())) : '',
            ], $recientes),
        ]);
    }

    private function adminDashboard(): void
    {
        $totalDocs = $this->docRepo->count();
        $totalEncuestas = 0;
        $totalTraslados = 0;
        $totalConductores = 0;

        $recientes = $this->docRepo->findAll(null, null, null, 1, 5);

        $this->render('dashboard/index', [
            'totalDocs' => $totalDocs,
            'totalEncuestas' => $totalEncuestas,
            'totalTraslados' => $totalTraslados,
            'totalConductores' => $totalConductores,
            'recientes' => array_map(fn ($d) => [
                'id' => $d->getId(),
                'titulo' => $d->getTitulo(),
                'categoria' => $d->getCategoriaNombre() ?? '',
                'subido' => $d->getCreatedAt() ? date('d/m/Y', strtotime($d->getCreatedAt())) : '',
                'activo' => $d->isActivo(),
            ], $recientes),
        ]);
    }
}
