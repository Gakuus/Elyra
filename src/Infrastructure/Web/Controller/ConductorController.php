<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Application\UseCases\Auth\ActualizarFuncionarioUseCase;
use Elyra\Application\UseCases\Auth\CrearFuncionarioUseCase;
use Elyra\Application\UseCases\Conductor\ListarConductoresUseCase;
use Elyra\Domain\Entity\Funcionario;
use Elyra\Domain\ValueObject\CategoriaLicenciaConducir;
use Elyra\Domain\ValueObject\LicenciaProfesional;
use Elyra\Infrastructure\Persistence\MySQL\ConductorRepository;
use Elyra\Infrastructure\Persistence\MySQL\UsuarioRepository;

class ConductorController extends BaseController
{
    private ListarConductoresUseCase $listarConductores;
    private CrearFuncionarioUseCase $crearFuncionario;
    private ActualizarFuncionarioUseCase $actualizarFuncionario;

    public function __construct()
    {
        $repo = new ConductorRepository();
        $this->listarConductores = new ListarConductoresUseCase($repo);
        $usuarioRepo = new UsuarioRepository();
        $this->crearFuncionario = new CrearFuncionarioUseCase($usuarioRepo);
        $this->actualizarFuncionario = new ActualizarFuncionarioUseCase($usuarioRepo);
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->denyPaciente();
        $this->requireRole('admin', 'superadmin');

        /** @var string $buscar */
        $buscar = $_GET['buscar'] ?? '';
        /** @var string $activoRaw */
        $activoRaw = $_GET['activo'] ?? '';
        $activo = $activoRaw !== '' ? ($activoRaw === '1') : null;

        $result = $this->listarConductores->execute([
            'buscar' => $buscar,
            'activo' => $activo,
        ]);

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

        $this->render('conductores/list', [
            'conductores' => $conductores,
            'total' => $result['total'],
            'activos' => $result['activos'],
            'buscar' => $buscar,
            'filtroActivo' => $activoRaw,
        ]);
    }

    public function crear(): void
    {
        $this->requireAuth();
        $this->denyPaciente();
        $this->requireRole('admin', 'superadmin');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCrear();
            return;
        }

        $this->render('conductores/crear', [
            'licencias' => LicenciaProfesional::obtenerTodas(),
            'categoriasLicenciaConducir' => CategoriaLicenciaConducir::todas(),
        ]);
    }

    private function handleCrear(): void
    {
        /** @var string $csrfTokenRaw */
        $csrfTokenRaw = $_POST['_csrf_token'] ?? '';
        $csrf = trim($csrfTokenRaw);
        /** @var string $csrfSession */
        $csrfSession = $_SESSION['_csrf_token'] ?? '';
        if ($csrf !== $csrfSession) {
            $this->renderForm('Sesi&oacute;n inv&aacute;lida. Recarg&aacute; e intent&aacute; de nuevo.');
            return;
        }

        /** @var string $rol */
        $rol = $_POST['rol'] ?? 'conductor';
        if (!in_array($rol, ['conductor', 'copiloto'], true)) {
            $this->renderForm('Rol inv&aacute;lido.');
            return;
        }

        try {
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
            /** @var string $documento */
            $documento = $_POST['documento_identidad'] ?? '';
            /** @var string $licencia */
            $licencia = $_POST['licencia'] ?? '';
            /** @var string $licenciaConducir */
            $licenciaConducir = $_POST['licencia_conducir'] ?? '';
            /** @var string $telefono */
            $telefono = $_POST['telefono'] ?? '';

            $this->crearFuncionario->execute([
                'nombre' => $nombre,
                'apellido' => $apellido,
                'username' => $username,
                'password' => $password,
                'rol' => $rol,
                'email' => $email,
                'documentoIdentidad' => $documento,
                'licencia' => $licencia,
                'licenciaConducir' => $licenciaConducir,
                'telefono' => $telefono,
            ]);
        } catch (\InvalidArgumentException | \DomainException $e) {
            $this->renderForm($e->getMessage());
            return;
        }

        \Elyra\Infrastructure\Service\AuditLogger::logCreate('conductor', null, ['username' => $username, 'rol' => $rol]);
        $this->redirect('/conductores?creado=1');
    }

    private function renderForm(string $error = ''): void
    {
        $this->render('conductores/crear', [
            'error' => $error,
            'licencias' => LicenciaProfesional::obtenerTodas(),
            'categoriasLicenciaConducir' => CategoriaLicenciaConducir::todas(),
        ]);
    }

    public function guardar(): void
    {
        $this->crear();
    }

    public function editar(): void
    {
        $this->requireAuth();
        $this->denyPaciente();
        $this->requireRole('admin', 'superadmin');

        /** @var string $idRaw */
        $idRaw = $_GET['id'] ?? '0';
        $id = (int) $idRaw;

        if ($id <= 0) {
            $this->redirect('/conductores');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEditar($id);
            return;
        }

        $usuarioRepo = new UsuarioRepository();
        $existing = $usuarioRepo->findById($id);
        if ($existing === null || $existing->getTipo() !== 'funcionario') {
            $this->redirect('/conductores');
            return;
        }

        /** @var Funcionario $funcionario */
        $funcionario = $existing;

        $this->render('conductores/editar', [
            'conductor' => [
                'id' => $funcionario->getId(),
                'nombre' => $funcionario->getNombre(),
                'apellido' => $funcionario->getApellido(),
                'username' => $funcionario->getUsername() ?? '',
                'email' => $funcionario->getEmail() ?? '',
                'rol' => $funcionario->getRol()->value(),
                'licencia' => $funcionario->getLicencia() ?? '',
                'licencia_conducir' => $funcionario->getLicenciaConducir() ?? '',
                'telefono' => $funcionario->getTelefono() ?? '',
                'documento_identidad' => $funcionario->getDocumentoIdentidad() ?? '',
            ],
            'licencias' => LicenciaProfesional::obtenerTodas(),
            'categoriasLicenciaConducir' => CategoriaLicenciaConducir::todas(),
        ]);
    }

    private function handleEditar(int $id): void
    {
        /** @var string $csrfTokenRaw */
        $csrfTokenRaw = $_POST['_csrf_token'] ?? '';
        $csrf = trim($csrfTokenRaw);
        /** @var string $csrfSession */
        $csrfSession = $_SESSION['_csrf_token'] ?? '';
        if ($csrf !== $csrfSession) {
            $this->redirect('/conductores/editar?id=' . $id . '&error=csrf');
            return;
        }

        /** @var string $rol */
        $rol = $_POST['rol'] ?? 'conductor';
        if (!in_array($rol, ['conductor', 'copiloto'], true)) {
            $this->redirect('/conductores/editar?id=' . $id . '&error=rol');
            return;
        }

        try {
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
            /** @var string $documento */
            $documento = $_POST['documento_identidad'] ?? '';
            /** @var string $licencia */
            $licencia = $_POST['licencia'] ?? '';
            /** @var string $licenciaConducir */
            $licenciaConducir = $_POST['licencia_conducir'] ?? '';
            /** @var string $telefono */
            $telefono = $_POST['telefono'] ?? '';

            $this->actualizarFuncionario->execute([
                'id' => $id,
                'nombre' => $nombre,
                'apellido' => $apellido,
                'username' => $username,
                'rol' => $rol,
                'email' => $email,
                'documentoIdentidad' => $documento,
                'licencia' => $licencia,
                'licenciaConducir' => $licenciaConducir,
                'telefono' => $telefono,
                'password' => $password,
            ]);
        } catch (\InvalidArgumentException | \DomainException $e) {
            $this->redirect('/conductores/editar?id=' . $id . '&error=' . urlencode($e->getMessage()));
            return;
        }

        \Elyra\Infrastructure\Service\AuditLogger::logUpdate('conductor', (string) $id);
        $this->redirect('/conductores?actualizado=1');
    }

    public function desactivar(): void
    {
        $this->requireAuth();
        $this->denyPaciente();
        $this->requireRole('admin', 'superadmin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/conductores');
            return;
        }

        /** @var string $csrfTokenRaw */
        $csrfTokenRaw = $_POST['_csrf_token'] ?? '';
        $csrf = trim($csrfTokenRaw);
        /** @var string $csrfSession */
        $csrfSession = $_SESSION['_csrf_token'] ?? '';
        if ($csrf !== $csrfSession) {
            $this->redirect('/conductores');
            return;
        }

        /** @var string $idRaw */
        $idRaw = $_POST['id'] ?? '0';
        $id = (int) $idRaw;

        if ($id <= 0) {
            $this->redirect('/conductores');
            return;
        }

        $repo = new ConductorRepository();
        $conductor = $repo->findById($id);

        if ($conductor === null) {
            $this->redirect('/conductores');
            return;
        }

        $conductor->setActivo(false);
        $repo->update($conductor);

        \Elyra\Infrastructure\Service\AuditLogger::logUpdate('conductor', (string) $id, ['accion' => 'desactivar']);
        $this->redirect('/conductores?desactivado=1');
    }

    public function reactivar(): void
    {
        $this->requireAuth();
        $this->denyPaciente();
        $this->requireRole('admin', 'superadmin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/conductores');
            return;
        }

        /** @var string $csrfTokenRaw */
        $csrfTokenRaw = $_POST['_csrf_token'] ?? '';
        $csrf = trim($csrfTokenRaw);
        /** @var string $csrfSession */
        $csrfSession = $_SESSION['_csrf_token'] ?? '';
        if ($csrf !== $csrfSession) {
            $this->redirect('/conductores');
            return;
        }

        /** @var string $idRaw */
        $idRaw = $_POST['id'] ?? '0';
        $id = (int) $idRaw;

        if ($id <= 0) {
            $this->redirect('/conductores');
            return;
        }

        $repo = new ConductorRepository();
        $conductor = $repo->findById($id);

        if ($conductor === null) {
            $this->redirect('/conductores');
            return;
        }

        $conductor->setActivo(true);
        $repo->update($conductor);

        \Elyra\Infrastructure\Service\AuditLogger::logUpdate('conductor', (string) $id, ['accion' => 'reactivar']);
        $this->redirect('/conductores?reactivado=1');
    }
}
