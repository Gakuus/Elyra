<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

class DashboardController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();
        $this->render('dashboard/index');
    }
}
