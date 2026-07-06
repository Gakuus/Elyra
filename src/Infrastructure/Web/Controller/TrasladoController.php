<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

class TrasladoController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();

        $traslados = $this->mockTraslados();

        $pendientes = count(array_filter($traslados, fn($t) => $t['estado'] === 'pendiente'));
        $enCurso = count(array_filter($traslados, fn($t) => in_array($t['estado'], ['en_curso', 'en_destino', 'en_retorno'], true)));
        $hoy = date('d/m/Y');
        $completadosHoy = count(array_filter($traslados, fn($t) => $t['estado'] === 'completado' && $t['fecha'] === $hoy));
        $total = count(array_filter($traslados, fn($t) => $t['estado'] !== 'cancelado'));

        $activos = array_values(array_filter($traslados, fn($t) => in_array($t['estado'], ['pendiente', 'en_curso', 'en_destino', 'en_retorno'], true)));

        $this->render('traslados/index', [
            'pendientes' => $pendientes,
            'enCurso' => $enCurso,
            'completadosHoy' => $completadosHoy,
            'total' => $total,
            'activos' => $activos,
        ]);
    }

    public function nuevo(): void
    {
        $this->requireAuth();
        $this->render('traslados/nuevo');
    }

    public function ver(): void
    {
        $this->requireAuth();
        $this->render('traslados/ver');
    }

    public function actualizarEstado(): void
    {
        $this->requireAuth();
        $this->redirect('/traslados');
    }

    public function historial(): void
    {
        $this->requireAuth();
        $this->render('traslados/historial');
    }

    private function mockTraslados(): array
    {
        return [
            ['id' => 1, 'codigo' => 'TR-001', 'paciente' => 'Juan Pérez', 'origen' => 'Emergencias', 'destino' => 'Cardiología', 'conductor' => 'Carlos Gómez', 'estado' => 'pendiente', 'fecha' => '06/07/2026', 'hora' => '09:00'],
            ['id' => 2, 'codigo' => 'TR-002', 'paciente' => 'María López', 'origen' => 'Emergencias', 'destino' => 'Cirugía', 'conductor' => 'Ana Martínez', 'estado' => 'en_curso', 'fecha' => '06/07/2026', 'hora' => '10:30'],
            ['id' => 3, 'codigo' => 'TR-003', 'paciente' => 'Pedro Ramírez', 'origen' => 'Terapia', 'destino' => 'Diagnóstico por Imágenes', 'conductor' => 'Luis Fernández', 'estado' => 'en_destino', 'fecha' => '06/07/2026', 'hora' => '11:00'],
            ['id' => 4, 'codigo' => 'TR-004', 'paciente' => 'Laura Fernández', 'origen' => 'Emergencias', 'destino' => 'Nefrología', 'conductor' => 'Carlos Gómez', 'estado' => 'completado', 'fecha' => '06/07/2026', 'hora' => '08:00'],
            ['id' => 5, 'codigo' => 'TR-005', 'paciente' => 'Diego Torres', 'origen' => 'Emergencias', 'destino' => 'Cirugía', 'conductor' => 'Ana Martínez', 'estado' => 'cancelado', 'fecha' => '05/07/2026', 'hora' => '14:00'],
            ['id' => 6, 'codigo' => 'TR-006', 'paciente' => 'Sofía García', 'origen' => 'Maternidad', 'destino' => 'Pediatría', 'conductor' => 'Luis Fernández', 'estado' => 'pendiente', 'fecha' => '06/07/2026', 'hora' => '14:00'],
            ['id' => 7, 'codigo' => 'TR-007', 'paciente' => 'Roberto Acosta', 'origen' => 'Emergencias', 'destino' => 'Terapia Intensiva', 'conductor' => 'Carlos Gómez', 'estado' => 'completado', 'fecha' => '05/07/2026', 'hora' => '16:00'],
            ['id' => 8, 'codigo' => 'TR-008', 'paciente' => 'Valentina Ríos', 'origen' => 'Emergencias', 'destino' => 'Quirófano', 'conductor' => 'Ana Martínez', 'estado' => 'en_retorno', 'fecha' => '06/07/2026', 'hora' => '12:00'],
        ];
    }
}
