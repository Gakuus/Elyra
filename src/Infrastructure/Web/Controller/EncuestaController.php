<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

class EncuestaController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();
        $this->render('encuestas/index', [
            'encuestas' => $this->mockEncuestas(),
        ]);
    }

    public function crear(): void
    {
        $this->requireAuth();
        $this->render('encuestas/crear');
    }

    public function resultados(): void
    {
        $this->requireAuth();
        $this->render('encuestas/resultados');
    }

    private function mockEncuestas(): array
    {
        return [
            ['id' => 1, 'titulo' => 'Satisfacción general del paciente', 'descripcion' => 'Encuesta para pacientes internados sobre la calidad de atención recibida.', 'preguntas' => 8, 'activa' => true, 'creada' => '10/05/2026'],
            ['id' => 2, 'titulo' => 'Evaluación de enfermería', 'descripcion' => 'Opinión sobre el cuidado y trato del personal de enfermería.', 'preguntas' => 5, 'activa' => true, 'creada' => '12/05/2026'],
            ['id' => 3, 'titulo' => 'Calidad de alimentos', 'descripcion' => 'Encuesta sobre la calidad y variedad de los alimentos servidos.', 'preguntas' => 6, 'activa' => false, 'creada' => '08/05/2026'],
            ['id' => 4, 'titulo' => 'Atención en emergencias', 'descripcion' => 'Tiempo de espera y calidad de atención en el servicio de emergencias.', 'preguntas' => 10, 'activa' => true, 'creada' => '15/05/2026'],
            ['id' => 5, 'titulo' => 'Limpieza e higiene', 'descripcion' => 'Percepción de los pacientes sobre la limpieza de las instalaciones.', 'preguntas' => 4, 'activa' => false, 'creada' => '01/05/2026'],
        ];
    }
}
