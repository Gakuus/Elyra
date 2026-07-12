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
                $this->render('traslados/nuevo', ['error' => 'Seleccion&aacute; un paciente v&aacute;lido.'] + $formDefaults);
                return;
            }
            $pacientes = $this->usuarioRepo->findAllPacientes();
            foreach ($pacientes as $p) {
                if ($p->getId() === $pacienteId) {
                    $descripcion = $p->getApellido() . ', ' . $p->getNombre();
                    break;
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

        /** @var array{conductorId: int, origen: string, destino: string, registradoPor: int, horaSalidaEstimada: string, elementos: list<array{tipo: string, descripcion: string, cantidad: int, pacienteId?: int}>} $input */
        $input = [
            'conductorId' => $conductorId,
            'origen' => $origen,
            'destino' => $destino,
            'registradoPor' => $userId,
            'horaSalidaEstimada' => $horaSalida,
            'elementos' => $elementos,
        ];
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
}
