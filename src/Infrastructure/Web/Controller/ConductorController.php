<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Application\UseCases\Conductor\CrearConductorUseCase;
use Elyra\Application\UseCases\Conductor\ListarConductoresUseCase;
use Elyra\Domain\Entity\Funcionario;
use Elyra\Infrastructure\Persistence\MySQL\ConductorRepository;
use Elyra\Infrastructure\Service\SessionManager;

class ConductorController extends BaseController
{
    private ListarConductoresUseCase $listarConductores;
    private CrearConductorUseCase $crearConductor;

    public function __construct()
    {
        $repo = new ConductorRepository();
        $this->listarConductores = new ListarConductoresUseCase($repo);
        $this->crearConductor = new CrearConductorUseCase($repo);
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->denyPaciente();

        $result = $this->listarConductores->execute([]);

        $conductores = array_map(fn(Funcionario $c) => [
            'id' => $c->getId(),
            'nombre' => $c->getNombre(),
            'apellido' => $c->getApellido(),
            'username' => $c->getUsername() ?? '',
            'email' => $c->getEmail() ?? '',
            'licencia' => $c->getLicencia() ?? '',
            'telefono' => $c->getTelefono() ?? '',
            'activo' => $c->isActivo(),
            'rol' => $c->getRol()->value(),
        ], $result['conductores']);

        $this->render('conductores/index', [
            'conductores' => $conductores,
            'total' => $result['total'],
            'activos' => $result['activos'],
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

        $this->render('conductores/crear');
    }

    private function handleCrear(): void
    {
        /** @var string $csrfTokenRaw */
        $csrfTokenRaw = $_POST['_csrf_token'] ?? '';
        $csrf = trim($csrfTokenRaw);
        /** @var string $csrfSession */
        $csrfSession = $_SESSION['_csrf_token'] ?? '';
        if ($csrf !== $csrfSession) {
            $this->render('conductores/crear', ['error' => 'Sesi&oacute;n inv&aacute;lida. Recarg&aacute; e intent&aacute; de nuevo.']);
            return;
        }

        /** @var string $nombre */
        $nombre = $_POST['nombre'] ?? '';
        /** @var string $apellido */
        $apellido = $_POST['apellido'] ?? '';
        /** @var string $username */
        $username = $_POST['username'] ?? '';
        /** @var string $password */
        $password = $_POST['password'] ?? '';
        /** @var string $email */
        $email = $_POST['email'] ?? '';
        /** @var string $licencia */
        $licencia = $_POST['licencia'] ?? '';
        /** @var string $telefono */
        $telefono = $_POST['telefono'] ?? '';
        /** @var string $documento */
        $documento = $_POST['documento_identidad'] ?? '';

        try {
            $this->crearConductor->execute([
                'nombre' => $nombre,
                'apellido' => $apellido,
                'username' => $username,
                'password' => $password,
                'email' => $email,
                'documentoIdentidad' => $documento,
                'licencia' => $licencia,
                'telefono' => $telefono,
            ]);
        } catch (\InvalidArgumentException | \DomainException $e) {
            $this->render('conductores/crear', ['error' => $e->getMessage()]);
            return;
        }

        $this->redirect('/conductores?creado=1');
    }

    public function guardar(): void
    {
        $this->crear();
    }
}
