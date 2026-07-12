<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Application\UseCases\Traslado\ActualizarEstadoTrasladoUseCase;
use Elyra\Application\UseCases\Traslado\HistorialTrasladosUseCase;
use Elyra\Application\UseCases\Traslado\ListarTrasladosUseCase;
use Elyra\Application\UseCases\Traslado\RegistrarTrasladoUseCase;
use Elyra\Application\UseCases\Traslado\VerDetalleTrasladoUseCase;
use Elyra\Application\UseCases\Conductor\ListarConductoresUseCase;
use Elyra\Application\UseCases\Ruta\ListarRutasUseCase;
use Elyra\Domain\Entity\Funcionario;
use Elyra\Domain\Entity\Traslado;
use Elyra\Domain\ValueObject\EstadoTraslado;
use Elyra\Infrastructure\Persistence\MySQL\ConductorRepository;
use Elyra\Infrastructure\Persistence\MySQL\RutaRepository;
use Elyra\Infrastructure\Persistence\MySQL\TrasladoRepository;
use Elyra\Infrastructure\Persistence\MySQL\UsuarioRepository;
use Elyra\Infrastructure\Service\FileStorageService;
use Elyra\Infrastructure\Service\QRGeneratorService;
use Elyra\Infrastructure\Service\SessionManager;

class TrasladoController extends BaseController
{
    private ListarTrasladosUseCase $listarTraslados;
    private VerDetalleTrasladoUseCase $verDetalle;
    private RegistrarTrasladoUseCase $registrarTraslado;
    private ActualizarEstadoTrasladoUseCase $actualizarEstado;
    private HistorialTrasladosUseCase $historialTraslados;
    private ListarConductoresUseCase $listarConductores;
    private ListarRutasUseCase $listarRutas;

    public function __construct()
    {
        $trasladoRepo = new TrasladoRepository();
        $conductorRepo = new ConductorRepository();
        $rutaRepo = new RutaRepository();
        $usuarioRepo = new UsuarioRepository();

        $this->listarTraslados = new ListarTrasladosUseCase($trasladoRepo);
        $this->verDetalle = new VerDetalleTrasladoUseCase($trasladoRepo);
        $this->registrarTraslado = new RegistrarTrasladoUseCase($trasladoRepo);
        $this->actualizarEstado = new ActualizarEstadoTrasladoUseCase($trasladoRepo);
        $this->historialTraslados = new HistorialTrasladosUseCase($trasladoRepo);
        $this->listarConductores = new ListarConductoresUseCase($conductorRepo);
        $this->listarRutas = new ListarRutasUseCase($rutaRepo);
    }

    public function index(): void
    {
        $this->requireAuth();

        if (SessionManager::isPaciente()) {
            $this->render('traslados/index', [
                'isPaciente' => true,
                'traslados' => [],
                'pendientes' => 0,
                'enCurso' => 0,
                'completadosHoy' => 0,
                'totalHoy' => 0,
            ]);
            return;
        }

        $activos = $this->listarTraslados->execute(['estado' => '']);
        $hoy = date('Y-m-d');
        $completadosHoy = $this->listarTraslados->execute(['estado' => 'completado', 'fechaDesde' => $hoy, 'fechaHasta' => $hoy]);

        $pendientes = 0;
        $enCurso = 0;
        foreach ($activos['traslados'] as $t) {
            $e = $t->getEstado()->value();
            if ($e === 'pendiente') {
                $pendientes++;
            } elseif (in_array($e, ['en_curso', 'en_destino', 'en_retorno'], true)) {
                $enCurso++;
            }
        }

        $trasladosMapped = array_map(fn(Traslado $t) => $this->mapTraslado($t), $activos['traslados']);

        $this->render('traslados/index', [
            'isPaciente' => false,
            'traslados' => $trasladosMapped,
            'pendientes' => $pendientes,
            'enCurso' => $enCurso,
            'completadosHoy' => count($completadosHoy['traslados']),
            'totalHoy' => $activos['total'],
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

        $conductoresResult = $this->listarConductores->execute(['activo' => true]);
        $conductoresNames = array_map(
            fn(Funcionario $c) => $c->getApellido() . ', ' . $c->getNombre(),
            $conductoresResult['conductores']
        );

        $rutasResult = $this->listarRutas->execute();
        $rutasNames = array_map(fn($r) => $r->getNombre() . ': ' . $r->getOrigen() . ' → ' . $r->getDestino(), $rutasResult['rutas']);

        $this->render('traslados/nuevo', [
            'conductores' => $conductoresNames,
            'ubicaciones' => $this->getUbicaciones(),
            'rutas' => $rutasNames,
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
            $this->redirectBack('/traslados/nuevo', 'Sesi&oacute;n inv&aacute;lida. Recarg&aacute; e intent&aacute; de nuevo.');
            return;
        }

        /** @var string $conductorRaw */
        $conductorRaw = $_POST['conductor'] ?? '';
        $conductorNombre = trim($conductorRaw);
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

        $conductoresResult = $this->listarConductores->execute(['activo' => true]);
        $conductoresNames = array_map(
            fn(Funcionario $c) => $c->getApellido() . ', ' . $c->getNombre(),
            $conductoresResult['conductores']
        );
        $rutasResult = $this->listarRutas->execute();
        $rutasNames = array_map(fn($r) => $r->getNombre() . ': ' . $r->getOrigen() . ' → ' . $r->getDestino(), $rutasResult['rutas']);
        $formDefaults = ['conductores' => $conductoresNames, 'ubicaciones' => $this->getUbicaciones(), 'rutas' => $rutasNames];

        if ($conductorNombre === '') {
            $this->render('traslados/nuevo', ['error' => 'Seleccion&aacute; un conductor v&aacute;lido.'] + $formDefaults);
            return;
        }

        if (strlen($elemento) < 3) {
            $this->render('traslados/nuevo', ['error' => 'El elemento a trasladar debe tener al menos 3 caracteres.'] + $formDefaults);
            return;
        }

        if (!in_array($tipo, ['paciente', 'equipamiento', 'insumo'], true)) {
            $this->render('traslados/nuevo', ['error' => 'Seleccion&aacute; un tipo v&aacute;lido.'] + $formDefaults);
            return;
        }

        if ($origen === $destino || $origen === '' || $destino === '') {
            $this->render('traslados/nuevo', ['error' => 'Seleccion&aacute; origen y destino v&aacute;lidos y distintos.'] + $formDefaults);
            return;
        }

        if ($fecha === '' || $hora === '') {
            $this->render('traslados/nuevo', ['error' => 'Complet&aacute; fecha y hora de salida.'] + $formDefaults);
            return;
        }

        $conductorId = $this->findConductorIdByName($conductorNombre);
        if ($conductorId === null) {
            $this->render('traslados/nuevo', ['error' => 'Conductor no encontrado.'] + $formDefaults);
            return;
        }

        $userId = SessionManager::getUserId() ?? 0;
        $horaSalida = $fecha . ' ' . $hora;

        try {
            $this->registrarTraslado->execute([
                'conductorId' => $conductorId,
                'origen' => $origen,
                'destino' => $destino,
                'registradoPor' => $userId,
                'horaSalidaEstimada' => $horaSalida,
                'elementos' => [
                    [
                        'tipo' => $tipo,
                        'descripcion' => $elemento,
                        'cantidad' => 1,
                    ],
                ],
            ]);
        } catch (\InvalidArgumentException | \DomainException $e) {
            $this->render('traslados/nuevo', ['error' => $e->getMessage()] + $formDefaults);
            return;
        }

        $this->redirect('/traslados?creado=1');
    }

    public function ver(): void
    {
        $this->requireAuth();
        $this->denyPaciente();

        /** @var string $idRaw */
        $idRaw = $_GET['id'] ?? '0';
        $id = (int) $idRaw;

        $result = $this->verDetalle->execute(['id' => $id]);
        if ($result === null) {
            $this->redirect('/traslados');
            return;
        }

        $traslado = $result['traslado'];
        $t = $this->mapTrasladoDetalle($traslado);
        $timeline = $this->buildTimeline($traslado);

        $this->render('traslados/ver', ['t' => $t, 'timeline' => $timeline]);
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

        $result = $this->verDetalle->execute(['id' => $id]);
        if ($result === null) {
            $this->redirect('/traslados');
            return;
        }

        $traslado = $result['traslado'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            /** @var string $nuevoEstado */
            $nuevoEstado = $_POST['estado'] ?? '';
            $userId = SessionManager::getUserId() ?? 0;

            try {
                $this->actualizarEstado->execute([
                    'id' => $id,
                    'nuevoEstado' => $nuevoEstado,
                    'actualizadoPor' => $userId,
                ]);
                $_SESSION['flash_success'] = 'Estado actualizado a ' . $this->estadoLabel($nuevoEstado);
            } catch (\DomainException | \InvalidArgumentException $e) {
                $_SESSION['flash_error'] = $e->getMessage();
            }

            $this->redirect('/traslados/ver?id=' . $id);
            return;
        }

        $estadosMap = [
            'pendiente' => ['label' => 'Pendiente', 'class' => 'warning'],
            'en_curso' => ['label' => 'En curso', 'class' => 'primary'],
            'en_destino' => ['label' => 'En destino', 'class' => 'info'],
            'en_retorno' => ['label' => 'En retorno', 'class' => 'secondary'],
            'completado' => ['label' => 'Completado', 'class' => 'success'],
            'cancelado' => ['label' => 'Cancelado', 'class' => 'danger'],
        ];

        $estadoActual = $traslado->getEstado();
        $allowed = $estadoActual->transicionesPermitidas();

        $t = $this->mapTrasladoDetalle($traslado);

        $this->render('traslados/actualizar_estado', [
            't' => $t,
            'allowed' => $allowed,
            'estados' => $estadosMap,
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
                'filtros' => ['buscar' => '', 'estado' => '', 'conductor' => '', 'fecha' => ''],
            ]);
            return;
        }

        /** @var string $estado */
        $estado = $_GET['estado'] ?? '';
        /** @var string $buscar */
        $buscar = $_GET['buscar'] ?? '';
        /** @var string $conductor */
        $conductor = $_GET['conductor'] ?? '';
        /** @var string $fecha */
        $fecha = $_GET['fecha'] ?? '';

        $result = $this->historialTraslados->execute([
            'estado' => $estado !== '' ? $estado : null,
        ]);

        $conductoresResult = $this->listarConductores->execute([]);
        $conductoresNames = array_map(
            fn(Funcionario $c) => $c->getApellido() . ', ' . $c->getNombre(),
            $conductoresResult['conductores']
        );

        $estadosList = [
            'pendiente' => 'Pendiente',
            'en_curso' => 'En curso',
            'en_destino' => 'En destino',
            'en_retorno' => 'En retorno',
            'completado' => 'Completado',
            'cancelado' => 'Cancelado',
        ];

        $trasladosMapped = array_map(fn(Traslado $t) => $this->mapTrasladoHistorial($t), $result['traslados']);

        $this->render('traslados/historial', [
            'traslados' => $trasladosMapped,
            'estadosList' => $estadosList,
            'conductores' => $conductoresNames,
            'filtros' => compact('estado', 'buscar', 'conductor', 'fecha'),
        ]);
    }

    /** @return array{id: int|null, codigo: string, conductor: string, origen: string, destino: string, estado: string, estado_texto: string, salida: string} */
    private function mapTraslado(Traslado $t): array
    {
        return [
            'id' => $t->getId(),
            'codigo' => $t->getCodigo(),
            'conductor' => 'Conductor #' . $t->getConductorId(),
            'origen' => $t->getOrigen(),
            'destino' => $t->getDestino(),
            'estado' => $t->getEstado()->value(),
            'estado_texto' => $this->estadoLabel($t->getEstado()->value()),
            'salida' => $t->getHoraSalidaEstimada() ?? '',
        ];
    }

    /** @return array{id: int|null, codigo: string, conductor: string, copiloto: string, origen: string, destino: string, estado: string, fecha: string, hora: string, hora_llegada: string} */
    private function mapTrasladoDetalle(Traslado $t): array
    {
        $salida = $t->getHoraSalidaEstimada() ?? '';
        $fecha = '';
        $hora = '';
        if ($salida !== '' && str_contains($salida, ' ')) {
            [$fecha, $hora] = explode(' ', $salida, 2);
        }

        return [
            'id' => $t->getId(),
            'codigo' => $t->getCodigo(),
            'conductor' => 'Conductor #' . $t->getConductorId(),
            'copiloto' => $t->getCopilotoId() !== null ? 'Copiloto #' . $t->getCopilotoId() : '-',
            'origen' => $t->getOrigen(),
            'destino' => $t->getDestino(),
            'estado' => $t->getEstado()->value(),
            'fecha' => $fecha,
            'hora' => $hora,
            'hora_llegada' => $t->getHoraLlegadaDestino() ?? '-',
        ];
    }

    /** @return array{id: int|null, codigo: string, conductor: string, origen: string, destino: string, estado: string, fecha: string} */
    private function mapTrasladoHistorial(Traslado $t): array
    {
        $salida = $t->getHoraSalidaEstimada() ?? '';
        $fecha = $salida;

        return [
            'id' => $t->getId(),
            'codigo' => $t->getCodigo(),
            'conductor' => 'Conductor #' . $t->getConductorId(),
            'origen' => $t->getOrigen(),
            'destino' => $t->getDestino(),
            'estado' => $t->getEstado()->value(),
            'fecha' => $fecha,
        ];
    }

    /** @return list<array{estado: string, completado: bool, activo: bool, fecha: string}> */
    private function buildTimeline(Traslado $traslado): array
    {
        $estados = ['pendiente', 'en_curso', 'en_destino', 'en_retorno', 'completado'];
        $timeline = [];
        $actual = $traslado->getEstado()->value();
        $fechaHora = $traslado->getHoraSalidaEstimada() ?? '';

        foreach ($estados as $e) {
            $completado = false;
            $activo = false;

            if ($e === $actual) {
                $completado = true;
                $activo = true;
            } else {
                $idxActual = array_search($actual, $estados, true);
                $idxE = array_search($e, $estados, true);
                if ($idxActual !== false && $idxE !== false && $idxE < $idxActual) {
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

    private function estadoLabel(string $estado): string
    {
        return match ($estado) {
            'pendiente' => 'Pendiente',
            'en_curso' => 'En curso',
            'en_destino' => 'En destino',
            'en_retorno' => 'En retorno',
            'completado' => 'Completado',
            'cancelado' => 'Cancelado',
            default => $estado,
        };
    }

    /** @return list<string> */
    private function getUbicaciones(): array
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

    private function findConductorIdByName(string $nombre): ?int
    {
        $result = $this->listarConductores->execute(['activo' => true]);
        foreach ($result['conductores'] as $c) {
            $fullName = $c->getApellido() . ', ' . $c->getNombre();
            if ($fullName === $nombre) {
                return $c->getId();
            }
        }
        return null;
    }

    private function redirectBack(string $url, string $error): void
    {
        $conductoresResult = $this->listarConductores->execute(['activo' => true]);
        $conductoresNames = array_map(
            fn(Funcionario $c) => $c->getApellido() . ', ' . $c->getNombre(),
            $conductoresResult['conductores']
        );
        $rutasResult = $this->listarRutas->execute();
        $rutasNames = array_map(fn($r) => $r->getNombre() . ': ' . $r->getOrigen() . ' → ' . $r->getDestino(), $rutasResult['rutas']);

        $this->render('traslados/nuevo', [
            'error' => $error,
            'conductores' => $conductoresNames,
            'ubicaciones' => $this->getUbicaciones(),
            'rutas' => $rutasNames,
        ]);
    }
}
