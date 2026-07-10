<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Infrastructure\Service\SessionManager;

class TrasladoController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();

        if (SessionManager::isPaciente()) {
            $this->render('traslados/index', [
                'isPaciente' => true,
                'activos' => [],
                'historico' => [],
            ]);
            return;
        }

        $traslados = $this->mockTraslados();

        $pendientes = count(array_filter($traslados, fn($t) => $t['estado'] === 'pendiente'));
        $enCurso = count(array_filter($traslados, fn($t) => in_array($t['estado'], ['en_curso', 'en_destino', 'en_retorno'], true)));
        $hoy = date('d/m/Y');
        $completadosHoy = count(array_filter($traslados, fn($t) => $t['estado'] === 'completado' && $t['fecha'] === $hoy));
        $total = count(array_filter($traslados, fn($t) => $t['estado'] !== 'cancelado'));

        $activos = array_values(array_filter($traslados, fn($t) => in_array($t['estado'], ['pendiente', 'en_curso', 'en_destino', 'en_retorno'], true)));

        $this->render('traslados/index', [
            'isPaciente' => false,
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
        $this->denyPaciente();

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
        /** @var string $csrfTokenRaw */
        $csrfTokenRaw = $_POST['_csrf_token'] ?? '';
        $csrf = trim($csrfTokenRaw);
        /** @var string $csrfSession */
        $csrfSession = $_SESSION['_csrf_token'] ?? '';
        if ($csrf !== $csrfSession) {
            $this->render('traslados/nuevo', ['error' => 'Sesi&oacute;n inv&aacute;lida. Recarg&aacute; e intent&aacute; de nuevo.', 'conductores' => $this->mockConductores(), 'ubicaciones' => $this->mockUbicaciones(), 'rutas' => $this->mockRutas()]);
            return;
        }

        /** @var string $conductorRaw */
        $conductorRaw = $_POST['conductor'] ?? '';
        $conductor = trim($conductorRaw);
        /** @var string $elementoRaw */
        $elementoRaw = $_POST['elemento'] ?? '';
        $elemento = trim($elementoRaw);
        /** @var string $tipoRaw */
        $tipoRaw = $_POST['tipo'] ?? '';
        $tipo = trim($tipoRaw);
        /** @var string $origenRaw */
        $origenRaw = $_POST['origen'] ?? '';
        $origen = trim($origenRaw);
        /** @var string $destinoRaw */
        $destinoRaw = $_POST['destino'] ?? '';
        $destino = trim($destinoRaw);
        /** @var string $fechaRaw */
        $fechaRaw = $_POST['fecha_salida'] ?? '';
        $fecha = trim($fechaRaw);
        /** @var string $horaRaw */
        $horaRaw = $_POST['hora_salida'] ?? '';
        $hora = trim($horaRaw);

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
        /** @var list<array<string, mixed>> $meta */
        $meta = [];
        if (is_file($metaFile)) {
            /** @var string $metaContent */
            $metaContent = file_get_contents($metaFile);
            /** @var list<array<string, mixed>> $decoded */
            $decoded = json_decode($metaContent, true) ?? [];
            $meta = $decoded;
        }

        $nextId = 1;
        if (!empty($meta)) {
            /** @var list<int> $ids */
            $ids = array_column($meta, 'id');
            if ($ids !== []) {
                $nextId = max($ids) + 1;
            }
        }

        $year = date('y');
        $secuencial = str_pad((string) $nextId, 3, '0', STR_PAD_LEFT);
        $codigo = "TR-{$year}{$secuencial}";

        /** @var string $copilotoRaw */
        $copilotoRaw = $_POST['copiloto'] ?? '';
        /** @var string $rutaRaw */
        $rutaRaw = $_POST['ruta'] ?? '';
        /** @var string $horaLlegadaRaw */
        $horaLlegadaRaw = $_POST['hora_llegada'] ?? '';
        $meta[] = [
            'id' => $nextId,
            'codigo' => $codigo,
            'conductor' => $conductor,
            'copiloto' => trim($copilotoRaw),
            'elemento' => $elemento,
            'tipo' => $tipo,
            'origen' => $origen,
            'destino' => $destino,
            'ruta' => trim($rutaRaw),
            'fecha' => $fecha,
            'hora' => $hora,
            'hora_llegada' => trim($horaLlegadaRaw),
            'estado' => 'pendiente',
        ];

        file_put_contents($metaFile, json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $this->redirect('/traslados?creado=1');
    }

    /** @return list<string> */
    private function mockConductores(): array
    {
        return ['Carlos Gómez', 'Ana Martínez', 'Luis Fernández', 'Ricardo Álvarez'];
    }

    /** @return list<string> */
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

    /** @return list<string> */
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
        $this->denyPaciente();

        /** @var string $idRaw */
        $idRaw = $_GET['id'] ?? '0';
        $id = (int) $idRaw;
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

    /** @return array<string, mixed>|null */
    private function findTrasladoById(int $id): ?array
    {
        $all = $this->mockTraslados();
        foreach ($all as $t) {
            if ($t['id'] === $id) return $t;
        }
        return null;
    }

    /**
     * @param array<string, mixed> $traslado
     * @return list<array{estado: string, completado: bool, activo: bool, fecha: string}>
     */
    private function buildTimeline(array $traslado): array
    {
        $estados = ['pendiente', 'en_curso', 'en_destino', 'en_retorno', 'completado'];
        $timeline = [];

        /** @var string $fechaVal */
        $fechaVal = $traslado['fecha'] ?? '';
        /** @var string $horaVal */
        $horaVal = $traslado['hora'] ?? '';
        $fechaHora = $fechaVal . ' ' . $horaVal;
        /** @var string $actual */
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
        $this->denyPaciente();

        /** @var string $idRaw */
        $idRaw = $_GET['id'] ?? $_POST['id'] ?? '0';
        $id = (int) $idRaw;
        if ($id <= 0) {
            $this->redirect('/traslados');
            return;
        }

        $traslado = $this->findTrasladoById($id);
        if (!$traslado) {
            $this->redirect('/traslados');
            return;
        }

        $stateMachine = [
            'pendiente' => ['en_curso', 'cancelado'],
            'en_curso' => ['en_destino', 'cancelado'],
            'en_destino' => ['en_retorno', 'cancelado'],
            'en_retorno' => ['completado', 'cancelado'],
            'completado' => [],
            'cancelado' => [],
        ];

        /** @var string $current */
        $current = $traslado['estado'];
        $allowed = $stateMachine[$current] ?? [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            /** @var string $nuevoEstado */
            $nuevoEstado = $_POST['estado'] ?? '';

            if (!in_array($nuevoEstado, $allowed, true)) {
                $_SESSION['flash_error'] = 'Transición de estado no válida.';
                $this->redirect('/traslados/ver?id=' . $id);
                return;
            }

            $this->updateTrasladoEstado($id, $nuevoEstado);

            $_SESSION['flash_success'] = 'Estado actualizado a ' . $this->estadoLabel($nuevoEstado);
            $this->redirect('/traslados/ver?id=' . $id);
            return;
        }

        $estados = [
            'pendiente' => ['label' => 'Pendiente', 'class' => 'warning'],
            'en_curso' => ['label' => 'En curso', 'class' => 'primary'],
            'en_destino' => ['label' => 'En destino', 'class' => 'info'],
            'en_retorno' => ['label' => 'En retorno', 'class' => 'secondary'],
            'completado' => ['label' => 'Completado', 'class' => 'success'],
            'cancelado' => ['label' => 'Cancelado', 'class' => 'danger'],
        ];

        $this->render('traslados/actualizar_estado', [
            't' => $traslado,
            'allowed' => $allowed,
            'estados' => $estados,
        ]);
    }

    public function historial(): void
    {
        $this->requireAuth();

        if (SessionManager::isPaciente()) {
            $this->render('traslados/historial', [
                'isPaciente' => true,
                'traslados' => [],
                'estadosList' => [],
                'conductores' => [],
                'filtros' => [],
            ]);
            return;
        }

        $traslados = $this->mockTraslados();

        /** @var string $estado */
        $estado = $_GET['estado'] ?? '';
        /** @var string $conductor */
        $conductor = $_GET['conductor'] ?? '';
        /** @var string $buscar */
        $buscar = $_GET['buscar'] ?? '';
        /** @var string $fecha */
        $fecha = $_GET['fecha'] ?? '';

        if ($estado !== '') {
            $traslados = array_filter($traslados, fn($t) => $t['estado'] === $estado);
        }
        if ($conductor !== '') {
            $traslados = array_filter($traslados, fn($t) => ($t['conductor'] ?? '') === $conductor);
        }
        if ($buscar !== '') {
            $q = mb_strtolower($buscar);
            $traslados = array_filter($traslados, function ($t) use ($q) {
                /** @var mixed $codigoRaw */
                $codigoRaw = $t['codigo'] ?? '';
                /** @var mixed $pacienteRaw */
                $pacienteRaw = $t['paciente'] ?? $t['elemento'] ?? '';
                /** @var mixed $origenRaw */
                $origenRaw = $t['origen'] ?? '';
                /** @var mixed $destinoRaw */
                $destinoRaw = $t['destino'] ?? '';
                $codigo = is_string($codigoRaw) ? $codigoRaw : '';
                $paciente = is_string($pacienteRaw) ? $pacienteRaw : '';
                $origen = is_string($origenRaw) ? $origenRaw : '';
                $destino = is_string($destinoRaw) ? $destinoRaw : '';
                return mb_strpos(mb_strtolower($codigo), $q) !== false ||
                    mb_strpos(mb_strtolower($paciente), $q) !== false ||
                    mb_strpos(mb_strtolower($origen), $q) !== false ||
                    mb_strpos(mb_strtolower($destino), $q) !== false;
            });
        }
        if ($fecha !== '') {
            $traslados = array_filter($traslados, fn($t) => ($t['fecha'] ?? '') === $fecha);
        }

        usort($traslados, fn($a, $b) => ($b['id'] ?? 0) <=> ($a['id'] ?? 0));

        /** @var list<string> $conductoresRaw */
        $conductoresRaw = array_column(array_merge($this->mockTraslados(), $this->storedTraslados()), 'conductor');
        /** @var array<int|string, string> $conductoresUnique */
        $conductoresUnique = array_unique($conductoresRaw);
        /** @var list<string> $conductores */
        $conductores = array_values($conductoresUnique);
        sort($conductores);

        $estadosList = [
            'pendiente' => 'Pendiente',
            'en_curso' => 'En curso',
            'en_destino' => 'En destino',
            'en_retorno' => 'En retorno',
            'completado' => 'Completado',
            'cancelado' => 'Cancelado',
        ];

        $this->render('traslados/historial', [
            'traslados' => $traslados,
            'estadosList' => $estadosList,
            'conductores' => $conductores,
            'filtros' => compact('estado', 'conductor', 'buscar', 'fecha'),
        ]);
    }

    private function estadoLabel(string $estado): string
    {
        $map = [
            'pendiente' => 'Pendiente',
            'en_curso' => 'En curso',
            'en_destino' => 'En destino',
            'en_retorno' => 'En retorno',
            'completado' => 'Completado',
            'cancelado' => 'Cancelado',
        ];
        return $map[$estado] ?? $estado;
    }

    private function updateTrasladoEstado(int $id, string $nuevoEstado): void
    {
        $metaFile = __DIR__ . '/../../../../storage/traslados/.meta.json';
        /** @var list<array<string, mixed>> $traslados */
        $traslados = [];
        if (is_file($metaFile)) {
            /** @var string $content */
            $content = file_get_contents($metaFile);
            /** @var list<array<string, mixed>> $decoded */
            $decoded = json_decode($content, true) ?? [];
            $traslados = $decoded;
        }

        $updated = false;
        foreach ($traslados as &$t) {
            /** @var array<string, mixed> $t */
            if ($t['id'] === $id) {
                $t['estado'] = $nuevoEstado;
                $t['updated_at'] = date('d/m/Y H:i');
                $updated = true;
                break;
            }
        }
        unset($t);

        if ($updated) {
            file_put_contents($metaFile, json_encode($traslados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    /** @return list<array<string, mixed>> */
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

    /** @return list<array<string, mixed>> */
    private function storedTraslados(): array
    {
        $metaFile = __DIR__ . '/../../../../storage/traslados/.meta.json';
        if (!is_file($metaFile)) return [];
        /** @var string $content */
        $content = file_get_contents($metaFile);
        /** @var list<array<string, mixed>> $decoded */
        $decoded = json_decode($content, true) ?? [];
        return $decoded;
    }
}
