<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

class EncuestaController extends BaseController
{
    public function index(): void
    {
        $this->render('encuestas/index');
    }

    public function crear(): void
    {
        $this->render('encuestas/crear');
    }

    public function resultados(): void
    {
        $this->render('encuestas/resultados');
    }
}
