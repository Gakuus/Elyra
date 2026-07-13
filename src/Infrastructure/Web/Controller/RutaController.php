<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Application\UseCases\Ruta\CrearRutaUseCase;
use Elyra\Application\UseCases\Ruta\ListarRutasUseCase;
use Elyra\Domain\Entity\Ruta;
use Elyra\Infrastructure\Persistence\MySQL\RutaRepository;

class RutaController extends BaseController
{
    private ListarRutasUseCase $listarRutas;
    private CrearRutaUseCase $crearRuta;

    public function __construct()
    {
        $repo = new RutaRepository();
        $this->listarRutas = new ListarRutasUseCase($repo);
        $this->crearRuta = new CrearRutaUseCase($repo);
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->denyPaciente();

        $result = $this->listarRutas->execute();

        $rutas = array_map(fn(Ruta $r) => [
            'id' => $r->getId(),
            'nombre' => $r->getNombre(),
            'origen' => $r->getOrigen(),
            'destino' => $r->getDestino(),
            'distancia_km' => $r->getDistanciaKm(),
            'descripcion' => $r->getDescripcion() ?? '',
        ], $result['rutas']);

        $this->render('rutas/index', [
            'rutas' => $rutas,
            'total' => $result['total'],
        ]);
    }

    public function crear(): void
    {
        $this->requireAuth();
        $this->denyPaciente();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCrear();
            return;
        }

        $this->render('rutas/crear');
    }

    private function handleCrear(): void
    {
        /** @var string $csrfTokenRaw */
        $csrfTokenRaw = $_POST['_csrf_token'] ?? '';
        $csrf = trim($csrfTokenRaw);
        /** @var string $csrfSession */
        $csrfSession = $_SESSION['_csrf_token'] ?? '';
        if ($csrf !== $csrfSession) {
            $this->render('rutas/crear', ['error' => 'Sesi&oacute;n inv&aacute;lida. Recarg&aacute; e intent&aacute; de nuevo.']);
            return;
        }

        /** @var string $nombre */
        $nombre = $_POST['nombre'] ?? '';
        /** @var string $origen */
        $origen = $_POST['origen'] ?? '';
        /** @var string $destino */
        $destino = $_POST['destino'] ?? '';
        /** @var string $distanciaRaw */
        $distanciaRaw = $_POST['distancia_km'] ?? '';
        /** @var string $descripcion */
        $descripcion = $_POST['descripcion'] ?? '';

        $rutaInput = [
            'nombre' => $nombre,
            'origen' => $origen,
            'destino' => $destino,
            'descripcion' => $descripcion,
        ];
        if ($distanciaRaw !== '') {
            $rutaInput['distanciaKm'] = (float) $distanciaRaw;
        }

        try {
            $this->crearRuta->execute($rutaInput);
        } catch (\InvalidArgumentException | \DomainException $e) {
            $this->render('rutas/crear', ['error' => $e->getMessage()]);
            return;
        }

        \Elyra\Infrastructure\Service\AuditLogger::logCreate('ruta', null, ['nombre' => $nombre]);
        $this->redirect('/rutas?creada=1');
    }

    public function guardar(): void
    {
        $this->crear();
    }
}
