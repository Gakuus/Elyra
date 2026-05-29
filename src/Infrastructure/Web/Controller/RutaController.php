<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

class RutaController extends BaseController
{
    public function index(): void
    {
        $this->render('rutas/index');
    }

    public function crear(): void
    {
        $this->render('rutas/crear');
    }
}
