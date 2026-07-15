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
use Elyra\Infrastructure\Persistence\MySQL\CatalogoElementoRepository;
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
    private CatalogoElementoRepository $catalogoRepo;
    private UsuarioRepository $usuarioRepo;

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
        $this->catalogoRepo = new CatalogoElementoRepository();
        $this->usuarioRepo = $usuarioRepo;
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->requireRole('admin', 'superadmin', 'conductor', 'copiloto');

        if (SessionManager::isPaciente()) {
            $this->render('traslados/list', [
                'isPaciente' => true,
                'traslados' => [],
                'pendientes' => 0,
                'enCurso' => 0,
                'completadosHoy' => 0,
                'totalHoy' => 0,
            ]);
            return;
        }

        $trasladoRepo = new TrasladoRepository();
        $pendientes = $trasladoRepo->countByEstado('pendiente');
        $enCurso = $trasladoRepo->countByEstado('en_curso')
            + $trasladoRepo->countByEstado('en_destino')
            + $trasladoRepo->countByEstado('en_retorno');
        $hoy = date('Y-m-d');
        $completadosHoyResult = $this->listarTraslados->execute(['estado' => 'completado', 'fechaDesde' => $hoy, 'fechaHasta' => $hoy]);
        $totalResult = $this->listarTraslados->execute(['estado' => '']);

        $trasladosMapped = array_map(fn(Traslado $t) => $this->mapTraslado($t), $totalResult['traslados']);

        $this->render('traslados/list', [
            'isPaciente' => false,
            'traslados' => $trasladosMapped,
            'pendientes' => $pendientes,
            'enCurso' => $enCurso,
            'completadosHoy' => count($completadosHoyResult['traslados']),
            'totalHoy' => $totalResult['total'],
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

        $this->render('traslados/nuevo', $this->getFormData());
    }

    /** @return array<string, mixed> */
    private function getFormData(): array
    {
        $conductoresResult = $this->listarConductores->execute(['activo' => true]);
        $conductores = array_map(
            fn(Funcionario $c) => ['id' => $c->getId(), 'nombre' => $c->getApellido() . ', ' . $c->getNombre()],
            $conductoresResult['conductores']
        );

        $copilotos = $this->usuarioRepo->findAllFuncionarios(true);
        $copilotosList = array_values(array_filter(
            $copilotos,
            fn(Funcionario $f) => $f->getRol()->value() === 'copiloto'
        ));
        $copilotosMapped = array_map(
            fn(Funcionario $c) => ['id' => $c->getId(), 'nombre' => $c->getApellido() . ', ' . $c->getNombre()],
            $copilotosList
        );

        $pacientes = $this->usuarioRepo->findAllPacientes();
        $pacientesMapped = array_map(
            fn($p) => ['id' => $p->getId(), 'nombre' => $p->getApellido() . ', ' . $p->getNombre() . ' (CI: ' . ($p->getDocumentoIdentidad() ?? '-') . ')'],
            $pacientes
        );

        $insumos = $this->catalogoRepo->findByTipo('insumo', true);
        $equipamiento = $this->catalogoRepo->findByTipo('equipamiento', true);
        $organos = $this->catalogoRepo->findByTipo('organo', true);

        $rutasResult = $this->listarRutas->execute();
        $rutasMapped = array_map(
            fn($r) => [
                'id' => $r->getId(),
                'nombre' => $r->getNombre() . ': ' . $r->getOrigen() . ' → ' . $r->getDestino(),
                'distancia_km' => $r->getDistanciaKm(),
            ],
            $rutasResult['rutas']
        );

        return [
            'conductores' => $conductores,
            'copilotos' => $copilotosMapped,
            'pacientes' => $pacientesMapped,
            'insumos' => array_map(fn($i) => ['id' => $i->getId(), 'nombre' => $i->getNombre()], $insumos),
            'equipamiento' => array_map(fn($e) => ['id' => $e->getId(), 'nombre' => $e->getNombre()], $equipamiento),
            'organos' => array_map(fn($o) => ['id' => $o->getId(), 'nombre' => $o->getNombre()], $organos),
            'rutas' => $rutasMapped,
            'ubicaciones' => $this->getUbicaciones(),
        ];
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

        /** @var string $conductorIdRaw */
        $conductorIdRaw = $_POST['conductor_id'] ?? '0';
        $conductorId = (int) $conductorIdRaw;
        /** @var string $copilotoIdRaw */
        $copilotoIdRaw = $_POST['copiloto_id'] ?? '';
        $copilotoId = $copilotoIdRaw !== '' ? (int) $copilotoIdRaw : null;
        /** @var string $tipoRaw */
        $tipoRaw = $_POST['tipo'] ?? '';
        $tipo = trim($tipoRaw);
        /** @var string $origenRaw */
        $origenRaw = $_POST['origen'] ?? '';
        $origen = trim($origenRaw);
        /** @var string $destinoRaw */
        $destinoRaw = $_POST['destino'] ?? '';
        $destino = trim($destinoRaw);
        /** @var string $rutaIdRaw */
        $rutaIdRaw = $_POST['ruta_id'] ?? '';
        $rutaId = $rutaIdRaw !== '' ? (int) $rutaIdRaw : null;
        /** @var string $fechaRaw */
        $fechaRaw = $_POST['fecha_salida'] ?? '';
        $fecha = trim($fechaRaw);
        /** @var string $horaRaw */
        $horaRaw = $_POST['hora_salida'] ?? '';
        $hora = trim($horaRaw);
        /** @var string $horaLlegadaRaw */
        $horaLlegadaRaw = $_POST['hora_llegada'] ?? '';
        $horaLlegada = trim($horaLlegadaRaw);

        $formDefaults = $this->getFormData();

        if ($conductorId <= 0) {
            $this->render('traslados/nuevo', ['error' => 'Seleccion&aacute; un conductor v&aacute;lido.'] + $formDefaults);
            return;
        }

        if (!in_array($tipo, ['paciente', 'equipamiento', 'insumo', 'organo'], true)) {
            $this->render('traslados/nuevo', ['error' => 'Seleccion&aacute; un tipo v&aacute;lido.'] + $formDefaults);
            return;
        }

        $descripcion = '';
        $pacienteId = null;

        if ($tipo === 'paciente') {
            /** @var string $pacienteIdRaw */
            $pacienteIdRaw = $_POST['paciente_id'] ?? '0';
            $pacienteId = (int) $pacienteIdRaw;

            if ($pacienteId <= 0) {
                /** @var string $nombreRaw */
                $nombreRaw = $_POST['paciente_nombre'] ?? '';
                $nombre = trim($nombreRaw);
                /** @var string $apellidoRaw */
                $apellidoRaw = $_POST['paciente_apellido'] ?? '';
                $apellido = trim($apellidoRaw);
                /** @var string $documentoRaw */
                $documentoRaw = $_POST['paciente_documento'] ?? '';
                $documento = trim($documentoRaw);

                if ($nombre === '' || $apellido === '' || $documento === '') {
                    $this->render('traslados/nuevo', ['error' => 'Seleccion&aacute; un paciente existente o complet&aacute; nombre, apellido y documento para crear uno nuevo.'] + $formDefaults);
                    return;
                }

                $existing = $this->usuarioRepo->findByDocumentoIdentidad($documento);
                if ($existing !== null) {
                    $pacienteId = $existing->getId() ?? 0;
                    $descripcion = $existing->getApellido() . ', ' . $existing->getNombre();
                } else {
                    $tokenAcceso = bin2hex(random_bytes(32));
                    $nuevoPaciente = new \Elyra\Domain\Entity\Paciente(
                        id: null,
                        nombre: $nombre,
                        apellido: $apellido,
                        documentoIdentidad: $documento,
                        tokenAcceso: $tokenAcceso,
                    );
                    $savedPaciente = $this->usuarioRepo->savePaciente($nuevoPaciente);
                    $pacienteId = $savedPaciente->getId() ?? 0;
                    $descripcion = $apellido . ', ' . $nombre;
                }
            } else {
                $pacientes = $this->usuarioRepo->findAllPacientes();
                foreach ($pacientes as $p) {
                    if ($p->getId() === $pacienteId) {
                        $descripcion = $p->getApellido() . ', ' . $p->getNombre();
                        break;
                    }
                }
            }
        } elseif ($tipo === 'organo') {
            /** @var string $organoIdRaw */
            $organoIdRaw = $_POST['catalogo_elemento_id'] ?? '0';
            $organoId = (int) $organoIdRaw;
            /** @var string $pacienteAsociadoRaw */
            $pacienteAsociadoRaw = $_POST['paciente_id'] ?? '0';
            $pacienteId = (int) $pacienteAsociadoRaw;
            if ($organoId <= 0) {
                $this->render('traslados/nuevo', ['error' => 'Seleccion&aacute; un &oacute;rgano v&aacute;lido.'] + $formDefaults);
                return;
            }
            if ($pacienteId <= 0) {
                $this->render('traslados/nuevo', ['error' => 'Seleccion&aacute; el paciente asociado al &oacute;rgano.'] + $formDefaults);
                return;
            }
            $organo = $this->catalogoRepo->findById($organoId);
            $descripcion = $organo !== null ? $organo->getNombre() : '';
        } elseif (in_array($tipo, ['insumo', 'equipamiento'], true)) {
            /** @var string $catalogoIdRaw */
            $catalogoIdRaw = $_POST['catalogo_elemento_id'] ?? '0';
            $catalogoId = (int) $catalogoIdRaw;
            if ($catalogoId <= 0) {
                $this->render('traslados/nuevo', ['error' => 'Seleccion&aacute; un elemento del cat&aacute;logo.'] + $formDefaults);
                return;
            }
            $item = $this->catalogoRepo->findById($catalogoId);
            $descripcion = $item !== null ? $item->getNombre() : '';
        }

        if ($origen === $destino || $origen === '' || $destino === '') {
            $this->render('traslados/nuevo', ['error' => 'Seleccion&aacute; origen y destino v&aacute;lidos y distintos.'] + $formDefaults);
            return;
        }

        if ($fecha === '' || $hora === '') {
            $this->render('traslados/nuevo', ['error' => 'Complet&aacute; fecha y hora de salida.'] + $formDefaults);
            return;
        }

        $userId = SessionManager::getUserId() ?? 0;
        $horaSalida = $fecha . ' ' . $hora;
        /** @var string $observacionesRaw */
        $observacionesRaw = $_POST['observaciones'] ?? '';
        $observaciones = trim($observacionesRaw);

        /** @var array{tipo: string, descripcion: string, cantidad: int} $elementoBase */
        $elementoBase = [
            'tipo' => $tipo,
            'descripcion' => $descripcion,
            'cantidad' => 1,
        ];
        $elemento = $pacienteId !== null
            ? $elementoBase + ['pacienteId' => $pacienteId]
            : $elementoBase;

        /** @var list<array{tipo: string, descripcion: string, cantidad: int, pacienteId?: int}> $elementos */
        $elementos = [$elemento];

        /** @var array{conductorId: int, origen: string, destino: string, registradoPor: int, horaSalidaEstimada: string, elementos: list<array{tipo: string, descripcion: string, cantidad: int, pacienteId?: int}>, observaciones?: string} $input */
        $input = [
            'conductorId' => $conductorId,
            'origen' => $origen,
            'destino' => $destino,
            'registradoPor' => $userId,
            'horaSalidaEstimada' => $horaSalida,
            'elementos' => $elementos,
        ];
        if ($observaciones !== '') {
            $input['observaciones'] = $observaciones;
        }
        if ($copilotoId !== null) {
            $input['copilotoId'] = $copilotoId;
        }
        if ($rutaId !== null) {
            $input['rutaId'] = $rutaId;
        }

        try {
            $this->registrarTraslado->execute($input);
        } catch (\InvalidArgumentException | \DomainException $e) {
            $this->render('traslados/nuevo', ['error' => $e->getMessage()] + $formDefaults);
            return;
        }

        \Elyra\Infrastructure\Service\AuditLogger::logCreate('traslado', null, [
            'tipo' => $tipo,
            'conductor_id' => $conductorId,
            'origen' => $origen,
            'destino' => $destino,
        ]);
        $this->redirect('/traslados?creado=1');
    }

    public function ver(): void
    {
        $this->requireAuth();
        $this->denyPaciente();
        $this->requireRole('admin', 'superadmin', 'conductor', 'copiloto');

        /** @var string $idRaw */
        $idRaw = $_GET['id'] ?? '0';
        $id = (int) $idRaw;

        $result = $this->verDetalle->execute(['id' => $id]);
        if ($result === null) {
            $this->redirect('/traslados');
            return;
        }

        $traslado = $result['traslado'];
        $t = $this->mapTrasladoDetalle($traslado, $result['elementos']);
        $timeline = $this->buildTimeline($traslado, $result['historial']);

        $this->render('traslados/ver', [
            't' => $t,
            'timeline' => $timeline,
            'elementos' => $result['elementos'],
            'historial' => $result['historial'],
        ]);
    }

    public function actualizarEstado(): void
    {
        $this->requireAuth();
        $this->denyPaciente();
        $this->requireRole('admin', 'superadmin', 'conductor', 'copiloto');

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
            /** @var string $motivoRaw */
            $motivoRaw = $_POST['motivo'] ?? '';
            $motivo = trim($motivoRaw);
            /** @var string $observacionRaw */
            $observacionRaw = $_POST['observacion'] ?? '';
            $observacion = trim($observacionRaw);
            $userId = SessionManager::getUserId() ?? 0;

            try {
                $params = [
                    'id' => $id,
                    'nuevoEstado' => $nuevoEstado,
                    'actualizadoPor' => $userId,
                    'actualizadoPorRol' => SessionManager::getUserRole() ?? '',
                ];
                if ($motivo !== '') {
                    $params['motivo'] = $motivo;
                }
                if ($observacion !== '') {
                    $params['observacion'] = $observacion;
                }
                $this->actualizarEstado->execute($params);
                \Elyra\Infrastructure\Service\AuditLogger::logStateChange(
                    'traslado',
                    (string) $id,
                    $traslado->getEstado()->value(),
                    $nuevoEstado,
                );
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
        /** @var string $conductorFilter */
        $conductorFilter = $_GET['conductor'] ?? '';
        /** @var string $fecha */
        $fecha = $_GET['fecha'] ?? '';

        $inputParams = [
            'estado' => $estado !== '' ? $estado : null,
        ];

        if ($conductorFilter !== '') {
            $conductorRepo = new ConductorRepository();
            $allConductores = $conductorRepo->findAll();
            foreach ($allConductores as $c) {
                $cName = $c->getApellido() . ', ' . $c->getNombre();
                if ($cName === $conductorFilter) {
                    $inputParams['conductorId'] = $c->getId();
                    break;
                }
            }
        }

        if ($fecha !== '') {
            /** @var int|false $ts */
            $ts = strtotime($fecha);
            if ($ts !== false) {
                $fechaParsed = date('Y-m-d', $ts);
                $inputParams['fechaDesde'] = $fechaParsed;
                $inputParams['fechaHasta'] = $fechaParsed;
            }
        }

        $result = $this->historialTraslados->execute($inputParams);

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
            'filtros' => compact('estado', 'buscar', 'conductorFilter', 'fecha'),
        ]);
    }

    private function resolveConductorName(int $conductorId): string
    {
        $conductorRepo = new ConductorRepository();
        $conductor = $conductorRepo->findById($conductorId);
        if ($conductor !== null) {
            return $conductor->getApellido() . ', ' . $conductor->getNombre();
        }
        return 'Conductor #' . $conductorId;
    }

    /** @return array{descripcion: string, tipo: string} */
    private function resolveElementoInfo(Traslado $t): array
    {
        $repo = new TrasladoRepository();
        $elementos = $repo->findElementosByTrasladoId($t->getId() ?? 0);
        if (empty($elementos)) {
            return ['descripcion' => '-', 'tipo' => 'paciente'];
        }
        $el = $elementos[0];
        return [
            'descripcion' => $el->getDescripcion() ?? '-',
            'tipo' => $el->getTipo()->value(),
        ];
    }

    /** @return array{id: int|null, codigo: string, conductor: string, origen: string, destino: string, estado: string, estado_texto: string, salida: string} */
    private function mapTraslado(Traslado $t): array
    {
        return [
            'id' => $t->getId(),
            'codigo' => $t->getCodigo(),
            'conductor' => $this->resolveConductorName($t->getConductorId()),
            'origen' => $t->getOrigen(),
            'destino' => $t->getDestino(),
            'estado' => $t->getEstado()->value(),
            'estado_texto' => $this->estadoLabel($t->getEstado()->value()),
            'salida' => $t->getHoraSalidaEstimada() ?? '',
        ];
    }

    /** @return array{id: int|null, codigo: string, conductor: string, copiloto: string, origen: string, destino: string, estado: string, fecha: string, hora: string, hora_llegada: string, elemento_descripcion: string, elemento_tipo: string, observaciones: string|null, motivo_cancelacion: string|null} */
    /**
     * @param list<\Elyra\Domain\Entity\ElementoTraslado>|null $elementos
     * @return array{id: int|null, codigo: string, conductor: string, copiloto: string, origen: string, destino: string, estado: string, fecha: string, hora: string, hora_llegada: string, elemento_descripcion: string, elemento_tipo: string, observaciones: string|null, motivo_cancelacion: string|null}
     */
    private function mapTrasladoDetalle(Traslado $t, ?array $elementos = null): array
    {
        $salida = $t->getHoraSalidaEstimada() ?? '';
        $fecha = '';
        $hora = '';
        if ($salida !== '' && str_contains($salida, ' ')) {
            [$fecha, $hora] = explode(' ', $salida, 2);
        }

        if ($elementos !== null && count($elementos) > 0) {
            $elDesc = $elementos[0]->getDescripcion() ?? '-';
            $elTipo = $elementos[0]->getTipo()->value();
        } else {
            $info = $this->resolveElementoInfo($t);
            $elDesc = $info['descripcion'];
            $elTipo = $info['tipo'];
        }

        return [
            'id' => $t->getId(),
            'codigo' => $t->getCodigo(),
            'conductor' => $this->resolveConductorName($t->getConductorId()),
            'copiloto' => $t->getCopilotoId() !== null ? $this->resolveConductorName($t->getCopilotoId()) : '-',
            'origen' => $t->getOrigen(),
            'destino' => $t->getDestino(),
            'estado' => $t->getEstado()->value(),
            'fecha' => $fecha,
            'hora' => $hora,
            'hora_llegada' => $t->getHoraLlegadaDestino() ?? '-',
            'elemento_descripcion' => $elDesc,
            'elemento_tipo' => $elTipo,
            'observaciones' => $t->getObservaciones(),
            'motivo_cancelacion' => $t->getMotivoCancelacion(),
        ];
    }

    /** @return array{id: int|null, codigo: string, conductor: string, elemento_descripcion: string, origen: string, destino: string, estado: string, fecha: string} */
    private function mapTrasladoHistorial(Traslado $t): array
    {
        $salida = $t->getHoraSalidaEstimada() ?? '';
        $fecha = $salida;
        $info = $this->resolveElementoInfo($t);

        return [
            'id' => $t->getId(),
            'codigo' => $t->getCodigo(),
            'conductor' => $this->resolveConductorName($t->getConductorId()),
            'elemento_descripcion' => $info['descripcion'],
            'origen' => $t->getOrigen(),
            'destino' => $t->getDestino(),
            'estado' => $t->getEstado()->value(),
            'fecha' => $fecha,
        ];
    }

    /**
     * @param list<\Elyra\Domain\Entity\HistorialEstado>|null $historial
     * @return list<array{estado: string, completado: bool, activo: bool, fecha: string}>
     */
    private function buildTimeline(Traslado $traslado, ?array $historial = null): array
    {
        $estados = ['pendiente', 'en_curso', 'en_destino', 'en_retorno', 'completado'];
        $timeline = [];
        $actual = $traslado->getEstado()->value();

        $fechasReales = [];
        if ($historial !== null) {
            foreach ($historial as $h) {
                $fechasReales[$h->getEstadoNuevo()] = $h->getCreatedAt() ?? '';
            }
        }

        $fechaEstimada = $traslado->getHoraSalidaEstimada() ?? '';

        $fechaMap = [
            'pendiente' => $fechaEstimada,
            'en_curso' => $traslado->getHoraSalidaEfectiva() ?? $fechaEstimada,
            'en_destino' => $traslado->getHoraLlegadaDestino() ?? '',
            'en_retorno' => $traslado->getHoraInicioRetorno() ?? '',
            'completado' => $traslado->getHoraLlegadaHospital() ?? '',
        ];

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

            $fecha = $fechasReales[$e] ?? $fechaMap[$e];

            $timeline[] = [
                'estado' => $e,
                'completado' => $completado,
                'activo' => $activo,
                'fecha' => $completado ? $fecha : '',
            ];
        }

        if ($actual === 'cancelado') {
            $fechaCancel = $fechasReales['cancelado'] ?? $fechaEstimada;
            $timeline[] = [
                'estado' => 'cancelado',
                'completado' => true,
                'activo' => true,
                'fecha' => $fechaCancel,
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

    public function apiCatalogo(): void
    {
        $this->requireAuth();
        $tipo = $_GET['tipo'] ?? '';
        if (!in_array($tipo, ['insumo', 'equipamiento', 'organo'], true)) {
            $this->json(['error' => 'Tipo inválido'], 400);
            return;
        }
        $items = $this->catalogoRepo->findByTipo($tipo, true);
        $this->json(array_map(fn($i) => ['id' => $i->getId(), 'nombre' => $i->getNombre(), 'descripcion' => $i->getDescripcion()], $items));
    }

    public function apiPacientes(): void
    {
        $this->requireAuth();
        $pacientes = $this->usuarioRepo->findAllPacientes();
        $this->json(array_map(fn($p) => ['id' => $p->getId(), 'nombre' => $p->getApellido() . ', ' . $p->getNombre(), 'documento' => $p->getDocumentoIdentidad()], $pacientes));
    }

    public function apiCopilotos(): void
    {
        $this->requireAuth();
        $all = $this->usuarioRepo->findAllFuncionarios(true);
        $copilotos = array_values(array_filter($all, fn(Funcionario $f) => $f->getRol()->value() === 'copiloto'));
        $this->json(array_map(fn(Funcionario $c) => ['id' => $c->getId(), 'nombre' => $c->getApellido() . ', ' . $c->getNombre()], $copilotos));
    }

    public function apiRutasInfo(): void
    {
        $this->requireAuth();
        $rutasResult = $this->listarRutas->execute();
        $this->json(array_map(fn($r) => [
            'id' => $r->getId(),
            'nombre' => $r->getNombre(),
            'origen' => $r->getOrigen(),
            'destino' => $r->getDestino(),
            'distancia_km' => $r->getDistanciaKm(),
        ], $rutasResult['rutas']));
    }

    private function redirectBack(string $url, string $error): void
    {
        $this->render('traslados/nuevo', ['error' => $error] + $this->getFormData());
    }

    public function exportar(): void
    {
        $this->requireAuth();
        $this->requireRole('admin', 'superadmin');

        $result = $this->listarTraslados->execute(['estado' => '']);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="traslados_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        if ($output === false) {
            return;
        }
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['Código', 'Conductor', 'Origen', 'Destino', 'Estado', 'Fecha registro', 'Hora salida estimada'], ';');

        foreach ($result['traslados'] as $t) {
            fputcsv($output, [
                $t->getCodigo(),
                $this->getConductorNombre($t->getConductorId()),
                $t->getOrigen(),
                $t->getDestino(),
                $t->getEstado()->value(),
                $t->getCreatedAt() ? date('d/m/Y H:i', (int) strtotime($t->getCreatedAt())) : '',
                $t->getHoraSalidaEstimada() ?? '',
            ], ';');
        }

        fclose($output);
        exit;
    }

    private function getConductorNombre(int $conductorId): string
    {
        $conductor = $this->listarConductores->execute(['activo' => null])['conductores'] ?? [];
        foreach ($conductor as $c) {
            if ($c->getId() === $conductorId) {
                return $c->getApellido() . ', ' . $c->getNombre();
            }
        }
        return '#{$conductorId}';
    }
}
