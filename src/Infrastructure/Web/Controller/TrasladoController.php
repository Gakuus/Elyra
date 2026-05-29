<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

class TrasladoController extends BaseController
{
    public function index(): void
    {
        $this->render('traslados/index');
    }

    public function nuevo(): void
    {
        $this->render('traslados/nuevo');
    }

    public function ver(): void
    {
        $this->render('traslados/ver');
    }

    public function actualizarEstado(): void
    {
        $this->redirect('/traslados');
    }

    public function historial(): void
    {
        $this->render('traslados/historial');
    }
}
