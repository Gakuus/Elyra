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
        $codigo = $_GET['qr'] ?? '';
        $this->render('publico/documento', ['codigo' => $codigo]);
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
