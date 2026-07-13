<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Auth;

use Elyra\Domain\Entity\Funcionario;
use Elyra\Domain\Entity\Paciente;
use Elyra\Domain\Repository\UsuarioRepositoryInterface;

final class ListarFuncionariosUseCase
{
    public function __construct(
        private UsuarioRepositoryInterface $usuarioRepo,
    ) {
    }

    /**
     * @param array{activo?: bool|null, buscar?: string} $input
     *
     * @return array{items: list<array<string, mixed>>, total: int, activos: int, inactivos: int}
     */
    public function execute(array $input): array
    {
        $activo = $input['activo'] ?? null;
        $buscar = trim($input['buscar'] ?? '');

        /** @var list<Funcionario> $funcionarios */
        $funcionarios = $this->usuarioRepo->findAllFuncionarios();
        /** @var list<Paciente> $pacientes */
        $pacientes = $this->usuarioRepo->findAllPacientes();

        /** @var list<array<string, mixed>> $items */
        $items = [];

        $rolLabels = [
            'superadmin' => 'Super Administrador',
            'admin' => 'Administrador',
            'medico' => 'Médico',
            'enfermero' => 'Enfermero/a',
            'tecnico' => 'Técnico',
            'recepcionista' => 'Recepcionista',
            'farmaceutico' => 'Farmacéutico',
            'conductor' => 'Conductor',
            'copiloto' => 'Copiloto',
        ];

        foreach ($funcionarios as $f) {
            $rol = $f->getRol()->value();
            $items[] = [
                'id' => $f->getId(),
                'nombre' => $f->getNombre(),
                'apellido' => $f->getApellido(),
                'username' => $f->getUsername() ?? '',
                'email' => $f->getEmail() ?? '',
                'documento_identidad' => $f->getDocumentoIdentidad() ?? '',
                'telefono' => $f->getTelefono() ?? '',
                'activo' => $f->isActivo(),
                'rol' => $rol,
                'rol_label' => $rolLabels[$rol] ?? ucfirst($rol),
                'tipo' => 'funcionario',
            ];
        }

        foreach ($pacientes as $p) {
            $items[] = [
                'id' => $p->getId(),
                'nombre' => $p->getNombre(),
                'apellido' => $p->getApellido(),
                'username' => '',
                'email' => $p->getEmail() ?? '',
                'documento_identidad' => $p->getDocumentoIdentidad() ?? '',
                'telefono' => $p->getTelefono() ?? '',
                'activo' => $p->isActivo(),
                'rol' => 'paciente',
                'rol_label' => 'Paciente',
                'tipo' => 'paciente',
            ];
        }

        $total = count($items);
        $activos = 0;
        foreach ($items as $item) {
            if ($item['activo']) {
                $activos++;
            }
        }

        if ($buscar !== '') {
            $buscarLower = strtolower($buscar);
            $items = array_values(array_filter(
                $items,
                function (array $item) use ($buscarLower): bool {
                    /** @var string $nombre */
                    $nombre = $item['nombre'];
                    /** @var string $apellido */
                    $apellido = $item['apellido'];
                    /** @var string $username */
                    $username = $item['username'];
                    /** @var string $email */
                    $email = $item['email'];
                    return str_contains(strtolower($nombre), $buscarLower)
                        || str_contains(strtolower($apellido), $buscarLower)
                        || str_contains(strtolower($username), $buscarLower)
                        || str_contains(strtolower($email), $buscarLower);
                }
            ));
        }

        if ($activo !== null) {
            $items = array_values(array_filter(
                $items,
                fn(array $item) => $item['activo'] === $activo
            ));
        }

        $sortKeys = [];
        foreach ($items as $item) {
            $apellido = is_string($item['apellido']) ? $item['apellido'] : '';
            $nombre = is_string($item['nombre']) ? $item['nombre'] : '';
            $sortKeys[] = $apellido . $nombre;
        }
        array_multisort($sortKeys, SORT_STRING, $items);

        return [
            'items' => $items,
            'total' => $total,
            'activos' => $activos,
            'inactivos' => $total - $activos,
        ];
    }
}
