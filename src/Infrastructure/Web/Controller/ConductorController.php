<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

class ConductorController extends BaseController
{
    public function index(): void
    {
        $this->render('conductores/index');
    }

    public function crear(): void
    {
        $this->render('conductores/crear');
    }
}
