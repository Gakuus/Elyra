<?php

declare(strict_types=1);

namespace Elyra\Domain\Repository;

use Elyra\Domain\Entity\Funcionario;
use Elyra\Domain\Entity\Paciente;
use Elyra\Domain\Entity\Usuario;

interface UsuarioRepositoryInterface
{
    public function findById(int $id): ?Usuario;
    public function findFuncionarioByUsername(string $username): ?Funcionario;
    public function findFuncionarioByEmail(string $email): ?Funcionario;
    public function findPacienteByToken(string $token): ?Paciente;
    public function findPacienteByUsername(string $username): ?Paciente;
    public function saveFuncionario(Funcionario $funcionario): Funcionario;
    public function savePaciente(Paciente $paciente): Paciente;
    public function updateFuncionario(Funcionario $funcionario): void;
    public function updatePaciente(Paciente $paciente): void;
    public function findAllFuncionarios(?bool $activo = null): array;
    public function findAllPacientes(): array;
    public function delete(int $id): void;
}
