<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

class DocumentoController extends BaseController
{
    public function index(): void
    {
        $this->render('documentos/index');
    }

    public function subir(): void
    {
        $this->render('documentos/subir');
    }

    public function editar(): void
    {
        $this->render('documentos/editar');
    }

    public function eliminar(): void
    {
        $this->redirect('/documentos');
    }

    public function ver(): void
    {
        $this->render('documentos/ver');
    }
}
