<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Application\UseCases\Ubicacion\ObtenerHistorialRutaUseCase;
use Elyra\Application\UseCases\Ubicacion\ObtenerUbicacionesActivasUseCase;
use Elyra\Application\UseCases\Ubicacion\RegistrarUbicacionUseCase;
use Elyra\Infrastructure\Persistence\MySQL\TrasladoRepository;
use Elyra\Infrastructure\Persistence\MySQL\UbicacionConductorRepository;
use Elyra\Infrastructure\Persistence\MySQL\UsuarioRepository;
use Elyra\Infrastructure\Service\LocationBroadcaster;
use Elyra\Infrastructure\Service\RouteCacheService;
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

        $userRole = SessionManager::getUserRole();
        if ($userRole !== 'conductor' && $userRole !== 'admin' && $userRole !== 'superadmin') {
            $this->json(['error' => 'Sin permisos para registrar ubicación'], 403);
            return;
        }

        $gpsKey = 'gps:' . $userId;
        if (!\Elyra\Infrastructure\Service\RateLimiter::checkGeneral($gpsKey, 60, 60)) {
            $this->json(['error' => 'Demasiadas solicitudes. Intentá de nuevo en un minuto.'], 429);
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
            \Elyra\Infrastructure\Service\RateLimiter::incrementGeneral($gpsKey, 60);
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
        $this->requireRole('admin', 'superadmin');

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

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');
        ob_implicit_flush(true);

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

    public function trasladosActivos(): void
    {
        $this->requireAuth();

        $trasladoRepo = new TrasladoRepository();
        $conductorRepo = new \Elyra\Infrastructure\Persistence\MySQL\ConductorRepository();
        $usuarioRepo = new UsuarioRepository();
        $ubicacionRepo = new UbicacionConductorRepository();

        $estadosActivos = ['pendiente', 'en_curso', 'en_destino', 'en_retorno'];
        $traslados = $trasladoRepo->findAllByEstados($estadosActivos);

        $coordsMap = $this->getLocationCoords();

        $copilotoCache = [];
        $funcionarios = $usuarioRepo->findAllFuncionarios(true);
        foreach ($funcionarios as $func) {
            $funcId = $func->getId();
            if ($funcId !== null) {
                $copilotoCache[$funcId] = $func->getApellido() . ', ' . $func->getNombre();
            }
        }

        $result = [];

        foreach ($traslados as $t) {
            $origenCoords = $coordsMap[$t->getOrigen()] ?? null;
            $destinoCoords = $coordsMap[$t->getDestino()] ?? null;

            if ($origenCoords === null && $t->getOrigenCoordenada() !== null) {
                $origenCoords = [$t->getOrigenCoordenada()->latitud(), $t->getOrigenCoordenada()->longitud()];
            }
            if ($destinoCoords === null && $t->getDestinoCoordenada() !== null) {
                $destinoCoords = [$t->getDestinoCoordenada()->latitud(), $t->getDestinoCoordenada()->longitud()];
            }

            $conductor = $conductorRepo->findById($t->getConductorId());
            $conductorNombre = $conductor !== null
                ? $conductor->getApellido() . ', ' . $conductor->getNombre()
                : 'Conductor #' . $t->getConductorId();

            $copilotoNombre = null;
            $copilotoId = $t->getCopilotoId();
            if ($copilotoId !== null) {
                $copilotoNombre = $copilotoCache[$copilotoId] ?? null;
            }

            $ubicacion = $ubicacionRepo->findByConductorId($t->getConductorId());
            $conductorLat = null;
            $conductorLng = null;
            if ($ubicacion !== null) {
                $conductorLat = $ubicacion->getCoordenada()->latitud();
                $conductorLng = $ubicacion->getCoordenada()->longitud();
            }

            $result[] = [
                'id' => $t->getId(),
                'codigo' => $t->getCodigo(),
                'conductor_id' => $t->getConductorId(),
                'conductor_nombre' => $conductorNombre,
                'copiloto_nombre' => $copilotoNombre,
                'estado' => $t->getEstado()->value(),
                'origen' => $t->getOrigen(),
                'destino' => $t->getDestino(),
                'origen_lat' => $origenCoords !== null ? $origenCoords[0] : null,
                'origen_lng' => $origenCoords !== null ? $origenCoords[1] : null,
                'destino_lat' => $destinoCoords !== null ? $destinoCoords[0] : null,
                'destino_lng' => $destinoCoords !== null ? $destinoCoords[1] : null,
                'conductor_lat' => $conductorLat,
                'conductor_lng' => $conductorLng,
                'hora_salida' => $t->getHoraSalidaEstimada(),
                'hora_llegada' => $t->getHoraLlegadaDestino(),
            ];
        }

        $this->json($result);
    }

    /** @return array<string, array{float, float}> */
    private function getLocationCoords(): array
    {
        return [
            'Hospital de Clínicas - Emergencias' => [-34.9211, -56.1645],
            'Hospital de Clínicas - Cardiología' => [-34.9215, -56.1648],
            'Hospital de Clínicas - Cirugía' => [-34.9213, -56.1642],
            'Hospital de Clínicas - Terapia Intensiva' => [-34.9208, -56.1640],
            'Hospital de Clínicas - Nefrología' => [-34.9205, -56.1650],
            'Hospital de Clínicas - Maternidad' => [-34.9218, -56.1652],
            'Hospital de Clínicas - Pediatría' => [-34.9202, -56.1646],
            'Hospital de Clínicas - Diagnóstico por Imágenes' => [-34.9209, -56.1638],
            'Hospital de Clínicas - Quirófano' => [-34.9216, -56.1636],
            'Clínica Privada - Centro' => [-34.9012, -56.1900],
            'Sanatorio Español' => [-34.8950, -56.1720],
        ];
    }

    public function rutaReal(): void
    {
        $this->requireAuth();

        /** @var float|false|null $origenLat */
        $origenLat = filter_input(INPUT_GET, 'origen_lat', FILTER_VALIDATE_FLOAT);
        /** @var float|false|null $origenLng */
        $origenLng = filter_input(INPUT_GET, 'origen_lng', FILTER_VALIDATE_FLOAT);
        /** @var float|false|null $destinoLat */
        $destinoLat = filter_input(INPUT_GET, 'destino_lat', FILTER_VALIDATE_FLOAT);
        /** @var float|false|null $destinoLng */
        $destinoLng = filter_input(INPUT_GET, 'destino_lng', FILTER_VALIDATE_FLOAT);

        if ($origenLat === false || $origenLng === false || $destinoLat === false || $destinoLng === false
            || $origenLat === null || $origenLng === null || $destinoLat === null || $destinoLng === null
        ) {
            $this->json(['error' => 'Coordenadas inválidas'], 400);
            return;
        }

        $cache = new RouteCacheService();
        $route = $cache->getRoute($origenLat, $origenLng, $destinoLat, $destinoLng);

        if ($route === null) {
            $this->json([
                'coordinates' => [[$origenLat, $origenLng], [$destinoLat, $destinoLng]],
                'distance_km' => 0,
                'duration_min' => 0,
                'fallback' => true,
            ]);
            return;
        }

        $this->json($route);
    }
}
