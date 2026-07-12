<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Application\UseCases\Auth\ActualizarFuncionarioUseCase;
use Elyra\Application\UseCases\Auth\CrearFuncionarioUseCase;
use Elyra\Application\UseCases\Auth\DesactivarFuncionarioUseCase;
use Elyra\Application\UseCases\Auth\ListarFuncionariosUseCase;
use Elyra\Domain\Entity\Funcionario;
use Elyra\Domain\ValueObject\CategoriaLicenciaConducir;
use Elyra\Domain\ValueObject\LicenciaProfesional;
use Elyra\Infrastructure\Persistence\MySQL\UsuarioRepository;
use Elyra\Infrastructure\Service\SessionManager;

class FuncionarioController extends BaseController
{
    private ListarFuncionariosUseCase $listarFuncionarios;
    private CrearFuncionarioUseCase $crearFuncionario;
    private ActualizarFuncionarioUseCase $actualizarFuncionario;
    private DesactivarFuncionarioUseCase $desactivarFuncionario;

    public function __construct()
    {
        $repo = new UsuarioRepository();
        $this->listarFuncionarios = new ListarFuncionariosUseCase($repo);
        $this->crearFuncionario = new CrearFuncionarioUseCase($repo);
        $this->actualizarFuncionario = new ActualizarFuncionarioUseCase($repo);
        $this->desactivarFuncionario = new DesactivarFuncionarioUseCase($repo);
    }

    public function index(): void
    {
        $this->requireRole('admin', 'superadmin');

        /** @var string $buscar */
        $buscar = $_GET['buscar'] ?? '';
        /** @var string $activoRaw */
        $activoRaw = $_GET['activo'] ?? '';
        $activo = $activoRaw !== '' ? ($activoRaw === '1') : null;

        $result = $this->listarFuncionarios->execute([
            'buscar' => $buscar,
            'activo' => $activo,
        ]);

        $funcionarios = array_map(fn(Funcionario $f) => [
            'id' => $f->getId(),
            'nombre' => $f->getNombre(),
            'apellido' => $f->getApellido(),
            'username' => $f->getUsername() ?? '',
            'email' => $f->getEmail() ?? '',
            'rol' => $f->getRol()->value(),
            'rol_label' => ucfirst($f->getRol()->value()),
            'licencia' => $f->getLicencia() ?? '',
            'licencia_conducir' => $f->getLicenciaConducir() ?? '',
            'telefono' => $f->getTelefono() ?? '',
            'activo' => $f->isActivo(),
        ], $result['funcionarios']);

        $this->render('funcionarios/index', [
            'funcionarios' => $funcionarios,
            'total' => $result['total'],
            'activos' => $result['activos'],
            'inactivos' => $result['inactivos'],
            'buscar' => $buscar,
            'filtroActivo' => $activoRaw,
        ]);
    }

    public function crear(): void
    {
        $this->requireRole('admin', 'superadmin');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCrear();
            return;
        }

        $this->render('funcionarios/form', [
            'modo' => 'crear',
            'roles' => \Elyra\Domain\ValueObject\RolUsuario::valores(),
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
            $this->render('funcionarios/form', [
                'modo' => 'crear',
                'roles' => \Elyra\Domain\ValueObject\RolUsuario::valores(),
                'licencias' => LicenciaProfesional::obtenerTodas(),
                'categoriasLicenciaConducir' => CategoriaLicenciaConducir::todas(),
                'error' => 'Sesi&oacute;n inv&aacute;lida.',
            ]);
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
        /** @var string $rol */
        $rol = $_POST['rol'] ?? 'admin';
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

        try {
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
            $this->render('funcionarios/form', [
                'modo' => 'crear',
                'roles' => \Elyra\Domain\ValueObject\RolUsuario::valores(),
                'licencias' => LicenciaProfesional::obtenerTodas(),
                'categoriasLicenciaConducir' => CategoriaLicenciaConducir::todas(),
                'error' => $e->getMessage(),
                'form' => $_POST,
            ]);
            return;
        }

        $this->redirect('/funcionarios?creado=1');
    }

    public function editar(): void
    {
        $this->requireRole('admin', 'superadmin');

        /** @var string $idRaw */
        $idRaw = $_GET['id'] ?? '0';
        $id = (int) $idRaw;

        if ($id <= 0) {
            $this->redirect('/funcionarios');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEditar($id);
            return;
        }

        $repo = new UsuarioRepository();
        $existing = $repo->findById($id);
        if ($existing === null || $existing->getTipo() !== 'funcionario') {
            $this->redirect('/funcionarios');
            return;
        }

        /** @var Funcionario $funcionario */
        $funcionario = $existing;

        $this->render('funcionarios/form', [
            'modo' => 'editar',
            'roles' => \Elyra\Domain\ValueObject\RolUsuario::valores(),
            'licencias' => LicenciaProfesional::obtenerTodas(),
            'categoriasLicenciaConducir' => CategoriaLicenciaConducir::todas(),
            'funcionario' => [
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
            $this->redirect('/funcionarios/editar?id=' . $id);
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
        /** @var string $rol */
        $rol = $_POST['rol'] ?? 'admin';
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

        $input = [
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
        ];
        if ($password !== '') {
            $input['password'] = $password;
        }

        try {
            $this->actualizarFuncionario->execute($input);
        } catch (\InvalidArgumentException | \DomainException $e) {
            $this->redirect('/funcionarios/editar?id=' . $id . '&error=' . urlencode($e->getMessage()));
            return;
        }

        $this->redirect('/funcionarios?actualizado=1');
    }

    public function desactivar(): void
    {
        $this->requireRole('admin', 'superadmin');

        /** @var string $idRaw */
        $idRaw = $_GET['id'] ?? $_POST['id'] ?? '0';
        $id = (int) $idRaw;

        if ($id <= 0) {
            $this->redirect('/funcionarios');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/funcionarios');
            return;
        }

        /** @var string $csrfTokenRaw */
        $csrfTokenRaw = $_POST['_csrf_token'] ?? '';
        $csrf = trim($csrfTokenRaw);
        /** @var string $csrfSession */
        $csrfSession = $_SESSION['_csrf_token'] ?? '';
        if ($csrf !== $csrfSession) {
            $this->redirect('/funcionarios?error=csrf');
            return;
        }

        try {
            $this->desactivarFuncionario->execute(['id' => $id]);
        } catch (\InvalidArgumentException $e) {
            $this->redirect('/funcionarios?error=' . urlencode($e->getMessage()));
            return;
        }

        $this->redirect('/funcionarios?desactivado=1');
    }
}
