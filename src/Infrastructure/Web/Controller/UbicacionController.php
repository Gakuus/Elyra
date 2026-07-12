<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Application\UseCases\Ubicacion\ObtenerHistorialRutaUseCase;
use Elyra\Application\UseCases\Ubicacion\ObtenerUbicacionesActivasUseCase;
use Elyra\Application\UseCases\Ubicacion\RegistrarUbicacionUseCase;
use Elyra\Infrastructure\Persistence\MySQL\TrasladoRepository;
use Elyra\Infrastructure\Persistence\MySQL\UbicacionConductorRepository;
use Elyra\Infrastructure\Service\LocationBroadcaster;
use Elyra\Infrastructure\Service\SessionManager;

class UbicacionController extends BaseController
{
    private RegistrarUbicacionUseCase $registrarUbicacion;
    private ObtenerUbicacionesActivasUseCase $obtenerUbicaciones;
    private ObtenerHistorialRutaUseCase $obtenerHistorial;
    private UbicacionConductorRepository $ubicacionRepo;

    public function __construct()
    {
        $this->ubicacionRepo = new UbicacionConductorRepository();
        $trasladoRepo = new TrasladoRepository();
        $broadcaster = LocationBroadcaster::getInstance();
        $this->registrarUbicacion = new RegistrarUbicacionUseCase($this->ubicacionRepo, $trasladoRepo, $broadcaster);
        $this->obtenerUbicaciones = new ObtenerUbicacionesActivasUseCase($this->ubicacionRepo);
        $this->obtenerHistorial = new ObtenerHistorialRutaUseCase($this->ubicacionRepo);
    }

    public function mapa(): void
    {
        $this->requireAuth();

        $this->render('traslados/mapa');
    }

    public function conductorView(): void
    {
        $this->requireAuth();

        $userId = SessionManager::getUserId() ?? 0;
        $conductorId = $userId;

        $ubicacionActual = $this->ubicacionRepo->findByConductorId($conductorId);

        $this->render('traslados/tracking', [
            'conductor_id' => $conductorId,
            'ubicacion_actual' => $ubicacionActual !== null ? $ubicacionActual->toArray() : null,
        ]);
    }

    public function registrar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
            return;
        }

        $userId = SessionManager::getUserId();
        if ($userId === null) {
            $this->json(['error' => 'No autenticado'], 401);
            return;
        }

        $input = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($input)) {
            $this->json(['error' => 'Invalid JSON'], 400);
            return;
        }

        /** @var mixed $rawLat */
        $rawLat = $input['latitud'] ?? null;
        /** @var mixed $rawLng */
        $rawLng = $input['longitud'] ?? null;
        if ($rawLat === null || $rawLng === null) {
            $this->json(['error' => 'latitud y longitud requeridas'], 400);
            return;
        }

        if (!is_numeric($rawLat) || !is_numeric($rawLng)) {
            $this->json(['error' => 'latitud y longitud deben ser numéricos'], 400);
            return;
        }

        try {
            /** @var mixed $rawHeading */
            $rawHeading = $input['heading'] ?? null;
            /** @var mixed $rawVelocidad */
            $rawVelocidad = $input['velocidad'] ?? null;
            /** @var mixed $rawTrasladoId */
            $rawTrasladoId = $input['traslado_id'] ?? null;

            $this->registrarUbicacion->execute([
                'conductor_id' => $userId,
                'latitud' => (float) $rawLat,
                'longitud' => (float) $rawLng,
                'heading' => is_numeric($rawHeading) ? (int) $rawHeading : null,
                'velocidad' => is_numeric($rawVelocidad) ? (float) $rawVelocidad : null,
                'traslado_id' => is_numeric($rawTrasladoId) ? (int) $rawTrasladoId : null,
            ]);

            $this->json(['success' => true]);
        } catch (\InvalidArgumentException $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function activas(): void
    {
        $this->requireAuth();

        $ubicaciones = $this->obtenerUbicaciones->execute();

        $result = [];
        foreach ($ubicaciones as $item) {
            $u = $item['ubicacion'];
            $coord = $u->getCoordenada();
            $result[] = [
                'conductor_id' => $u->getConductorId(),
                'conductor_nombre' => $item['conductor_nombre'],
                'latitud' => $coord->latitud(),
                'longitud' => $coord->longitud(),
                'heading' => $u->getHeading(),
                'velocidad' => $u->getVelocidad(),
                'updated_at' => $u->getUpdatedAt(),
                'traslado_codigo' => $item['traslado_codigo'],
                'traslado_estado' => $item['traslado_estado'],
                'traslado_origen' => $item['traslado_origen'],
                'traslado_destino' => $item['traslado_destino'],
            ];
        }

        $this->json($result);
    }

    public function historial(): void
    {
        $this->requireAuth();

        /** @var mixed $rawConductorId */
        $rawConductorId = $_GET['conductor_id'] ?? '0';
        $conductorId = is_numeric($rawConductorId) ? (int) $rawConductorId : 0;
        if ($conductorId <= 0) {
            $this->json(['error' => 'conductor_id requerido'], 400);
            return;
        }

        /** @var mixed $rawTrasladoId */
        $rawTrasladoId = isset($_GET['traslado_id']) ? $_GET['traslado_id'] : null;
        $trasladoId = is_numeric($rawTrasladoId) ? (int) $rawTrasladoId : null;

        /** @var string $desde */
        $desde = $_GET['desde'] ?? date('Y-m-d 00:00:00');
        /** @var string $hasta */
        $hasta = $_GET['hasta'] ?? date('Y-m-d 23:59:59');

        $puntos = $this->obtenerHistorial->execute([
            'conductor_id' => $conductorId,
            'traslado_id' => $trasladoId,
            'desde' => $desde,
            'hasta' => $hasta,
        ]);

        $this->json($puntos);
    }

    public function eventStream(): void
    {
        $this->requireAuth();

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        $broadcaster = LocationBroadcaster::getInstance();
        $broadcaster->cleanStaleListeners();
        $listenerPath = $broadcaster->registerListener();

        echo "event: connected\ndata: {\"status\":\"ok\"}\n\n";
        flush();

        set_time_limit(0);
        $startTime = microtime(true);
        $maxDuration = 120.0;

        try {
            while ((microtime(true) - $startTime) < $maxDuration) {
                $events = $broadcaster->readEvents($listenerPath, microtime(true));
                if ($events !== '') {
                    echo $events;
                    flush();
                }

                if (connection_aborted()) {
                    break;
                }

                usleep(500_000);
            }
        } finally {
            $broadcaster->removeListener($listenerPath);
        }
    }
}
