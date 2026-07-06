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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleNuevo();
            return;
        }

        $this->render('traslados/nuevo', [
            'conductores' => $this->mockConductores(),
            'ubicaciones' => $this->mockUbicaciones(),
            'rutas' => $this->mockRutas(),
        ]);
    }

    private function handleNuevo(): void
    {
        $csrf = trim($_POST['_csrf_token'] ?? '');
        if ($csrf !== ($_SESSION['_csrf_token'] ?? '')) {
            $this->render('traslados/nuevo', ['error' => 'Sesi&oacute;n inv&aacute;lida. Recarg&aacute; e intent&aacute; de nuevo.', 'conductores' => $this->mockConductores(), 'ubicaciones' => $this->mockUbicaciones(), 'rutas' => $this->mockRutas()]);
            return;
        }

        $conductor = trim($_POST['conductor'] ?? '');
        $elemento = trim($_POST['elemento'] ?? '');
        $tipo = trim($_POST['tipo'] ?? '');
        $origen = trim($_POST['origen'] ?? '');
        $destino = trim($_POST['destino'] ?? '');
        $fecha = trim($_POST['fecha_salida'] ?? '');
        $hora = trim($_POST['hora_salida'] ?? '');

        if (strlen($conductor) < 2) {
            $this->render('traslados/nuevo', ['error' => 'Seleccion&aacute; un conductor v&aacute;lido.', 'conductores' => $this->mockConductores(), 'ubicaciones' => $this->mockUbicaciones(), 'rutas' => $this->mockRutas()]);
            return;
        }

        if (strlen($elemento) < 3) {
            $this->render('traslados/nuevo', ['error' => 'El elemento a trasladar debe tener al menos 3 caracteres.', 'conductores' => $this->mockConductores(), 'ubicaciones' => $this->mockUbicaciones(), 'rutas' => $this->mockRutas()]);
            return;
        }

        if (!in_array($tipo, ['paciente', 'equipamiento', 'insumo'], true)) {
            $this->render('traslados/nuevo', ['error' => 'Seleccion&aacute; un tipo v&aacute;lido.', 'conductores' => $this->mockConductores(), 'ubicaciones' => $this->mockUbicaciones(), 'rutas' => $this->mockRutas()]);
            return;
        }

        if ($origen === $destino || $origen === '' || $destino === '') {
            $this->render('traslados/nuevo', ['error' => 'Seleccion&aacute; origen y destino v&aacute;lidos y distintos.', 'conductores' => $this->mockConductores(), 'ubicaciones' => $this->mockUbicaciones(), 'rutas' => $this->mockRutas()]);
            return;
        }

        if (!$fecha || !$hora) {
            $this->render('traslados/nuevo', ['error' => 'Complet&aacute; fecha y hora de salida.', 'conductores' => $this->mockConductores(), 'ubicaciones' => $this->mockUbicaciones(), 'rutas' => $this->mockRutas()]);
            return;
        }

        $storageDir = __DIR__ . '/../../../../storage/traslados';
        if (!is_dir($storageDir)) mkdir($storageDir, 0775, true);

        $metaFile = $storageDir . '/.meta.json';
        $meta = [];
        if (is_file($metaFile)) {
            $meta = json_decode(file_get_contents($metaFile), true) ?? [];
        }

        $nextId = 1;
        if (!empty($meta)) {
            $ids = array_column($meta, 'id');
            $nextId = max($ids) + 1;
        }

        $year = date('y');
        $secuencial = str_pad($nextId, 3, '0', STR_PAD_LEFT);
        $codigo = "TR-{$year}{$secuencial}";

        $meta[] = [
            'id' => $nextId,
            'codigo' => $codigo,
            'conductor' => $conductor,
            'copiloto' => trim($_POST['copiloto'] ?? ''),
            'elemento' => $elemento,
            'tipo' => $tipo,
            'origen' => $origen,
            'destino' => $destino,
            'ruta' => trim($_POST['ruta'] ?? ''),
            'fecha' => $fecha,
            'hora' => $hora,
            'hora_llegada' => trim($_POST['hora_llegada'] ?? ''),
            'estado' => 'pendiente',
        ];

        file_put_contents($metaFile, json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $this->redirect('/traslados?creado=1');
    }

    private function mockConductores(): array
    {
        return ['Carlos Gómez', 'Ana Martínez', 'Luis Fernández', 'Ricardo Álvarez'];
    }

    private function mockUbicaciones(): array
    {
        return [
            'Hospital de Clínicas - Emergencias',
            'Hospital de Clínicas - Cardiología',
            'Hospital de Clínicas - Cirugía',
            'Hospital de Clínicas - Terapia Intensiva',
            'Hospital de Clínicas - Nefrología',
            'Hospital de Clínicas - Maternidad',
            'Hospital de Clínicas - Pediatría',
            'Hospital de Clínicas - Diagnóstico por Imágenes',
            'Hospital de Clínicas - Quirófano',
            'Clínica Privada - Centro',
            'Sanatorio Español',
        ];
    }

    private function mockRutas(): array
    {
        return [
            'Ruta 1: Emergencias → Cardiología',
            'Ruta 2: Emergencias → Cirugía',
            'Ruta 3: Emergencias → Terapia Intensiva',
            'Ruta 4: Emergencias → Quirófano',
            'Ruta 5: Externo → Hospital',
        ];
    }

    public function ver(): void
    {
        $this->requireAuth();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('/traslados');
            return;
        }

        $traslado = $this->findTrasladoById($id);
        if (!$traslado) {
            $this->redirect('/traslados');
            return;
        }

        $timeline = $this->buildTimeline($traslado);

        $this->render('traslados/ver', [
            't' => $traslado,
            'timeline' => $timeline,
        ]);
    }

    private function findTrasladoById(int $id): ?array
    {
        $all = $this->mockTraslados();
        foreach ($all as $t) {
            if ($t['id'] === $id) return $t;
        }
        return null;
    }

    private function buildTimeline(array $traslado): array
    {
        $estados = ['pendiente', 'en_curso', 'en_destino', 'en_retorno', 'completado'];
        $timeline = [];

        $fechaHora = ($traslado['fecha'] ?? '') . ' ' . ($traslado['hora'] ?? '');
        $actual = $traslado['estado'];

        foreach ($estados as $e) {
            $completado = false;
            $activo = false;

            if ($e === $actual) {
                $completado = true;
                $activo = true;
            } elseif (in_array($actual, ['pendiente', 'cancelado'], true) && $e === 'pendiente') {
                $completado = true;
                $activo = $actual === 'pendiente';
            } else {
                $idxActual = array_search($actual, $estados, true);
                $idxE = array_search($e, $estados, true);
                if ($idxActual !== false && $idxE < $idxActual) {
                    $completado = true;
                } elseif ($actual === 'cancelado' && $e === 'pendiente') {
                    $completado = true;
                }
            }

            $timeline[] = [
                'estado' => $e,
                'completado' => $completado,
                'activo' => $activo,
                'fecha' => $completado ? $fechaHora : '',
            ];
        }

        if ($actual === 'cancelado') {
            $timeline[] = [
                'estado' => 'cancelado',
                'completado' => true,
                'activo' => true,
                'fecha' => $fechaHora,
            ];
        }

        return $timeline;
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
        $mock = [
            ['id' => 1, 'codigo' => 'TR-001', 'paciente' => 'Juan Pérez', 'origen' => 'Emergencias', 'destino' => 'Cardiología', 'conductor' => 'Carlos Gómez', 'estado' => 'pendiente', 'fecha' => '06/07/2026', 'hora' => '09:00'],
            ['id' => 2, 'codigo' => 'TR-002', 'paciente' => 'María López', 'origen' => 'Emergencias', 'destino' => 'Cirugía', 'conductor' => 'Ana Martínez', 'estado' => 'en_curso', 'fecha' => '06/07/2026', 'hora' => '10:30'],
            ['id' => 3, 'codigo' => 'TR-003', 'paciente' => 'Pedro Ramírez', 'origen' => 'Terapia', 'destino' => 'Diagnóstico por Imágenes', 'conductor' => 'Luis Fernández', 'estado' => 'en_destino', 'fecha' => '06/07/2026', 'hora' => '11:00'],
            ['id' => 4, 'codigo' => 'TR-004', 'paciente' => 'Laura Fernández', 'origen' => 'Emergencias', 'destino' => 'Nefrología', 'conductor' => 'Carlos Gómez', 'estado' => 'completado', 'fecha' => '06/07/2026', 'hora' => '08:00'],
            ['id' => 5, 'codigo' => 'TR-005', 'paciente' => 'Diego Torres', 'origen' => 'Emergencias', 'destino' => 'Cirugía', 'conductor' => 'Ana Martínez', 'estado' => 'cancelado', 'fecha' => '05/07/2026', 'hora' => '14:00'],
            ['id' => 6, 'codigo' => 'TR-006', 'paciente' => 'Sofía García', 'origen' => 'Maternidad', 'destino' => 'Pediatría', 'conductor' => 'Luis Fernández', 'estado' => 'pendiente', 'fecha' => '06/07/2026', 'hora' => '14:00'],
            ['id' => 7, 'codigo' => 'TR-007', 'paciente' => 'Roberto Acosta', 'origen' => 'Emergencias', 'destino' => 'Terapia Intensiva', 'conductor' => 'Carlos Gómez', 'estado' => 'completado', 'fecha' => '05/07/2026', 'hora' => '16:00'],
            ['id' => 8, 'codigo' => 'TR-008', 'paciente' => 'Valentina Ríos', 'origen' => 'Emergencias', 'destino' => 'Quirófano', 'conductor' => 'Ana Martínez', 'estado' => 'en_retorno', 'fecha' => '06/07/2026', 'hora' => '12:00'],
        ];

        $stored = $this->storedTraslados();
        $storedMapped = array_map(function ($s) {
            $s['paciente'] = $s['elemento'];
            return $s;
        }, $stored);

        return array_merge($mock, $storedMapped);
    }

    private function storedTraslados(): array
    {
        $metaFile = __DIR__ . '/../../../../storage/traslados/.meta.json';
        if (!is_file($metaFile)) return [];
        return json_decode(file_get_contents($metaFile), true) ?? [];
    }
}
