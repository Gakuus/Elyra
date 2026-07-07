<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Persistence\MySQL;

use Elyra\Domain\Entity\Funcionario;
use Elyra\Domain\Entity\Paciente;
use Elyra\Domain\Entity\Usuario;
use Elyra\Domain\Repository\UsuarioRepositoryInterface;
use Elyra\Domain\ValueObject\RolUsuario;

class UsuarioRepository implements UsuarioRepositoryInterface
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::get();
    }

    public function findById(int $id): ?Usuario
    {
        $stmt = $this->pdo->prepare("
            SELECT u.*,
                   f.id as f_id, f.rol, f.username as f_username, f.password_hash as f_password_hash,
                   f.licencia, f.telefono as f_telefono, f.activo as f_activo,
                   p.token_acceso, p.codigo_qr_id,
                   COALESCE(f.username, p.username) as username,
                   COALESCE(f.password_hash, p.password_hash) as password_hash,
                   COALESCE(f.telefono, p.telefono) as telefono,
                   COALESCE(f.activo, p.activo) as activo
            FROM usuario u
            LEFT JOIN funcionario f ON f.id = u.id
            LEFT JOIN paciente p ON p.id = u.id
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return $this->hydrate($row);
    }

    public function findFuncionarioByUsername(string $username): ?Funcionario
    {
        $stmt = $this->pdo->prepare("
            SELECT u.*, f.*
            FROM usuario u
            JOIN funcionario f ON f.id = u.id
            WHERE f.username = ?
        ");
        $stmt->execute([$username]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return new Funcionario(
            id: (int) $row['id'],
            nombre: $row['nombre'],
            apellido: $row['apellido'],
            rol: new RolUsuario($row['rol']),
            username: $row['username'],
            passwordHash: $row['password_hash'],
            email: $row['email'],
            documentoIdentidad: $row['documento_identidad'],
            licencia: $row['licencia'],
            telefono: $row['telefono'],
            activo: (bool) $row['activo'],
            createdAt: $row['created_at']
        );
    }

    public function findFuncionarioByEmail(string $email): ?Funcionario
    {
        $stmt = $this->pdo->prepare("
            SELECT u.*, f.*
            FROM usuario u
            JOIN funcionario f ON f.id = u.id
            WHERE u.email = ?
        ");
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return new Funcionario(
            id: (int) $row['id'],
            nombre: $row['nombre'],
            apellido: $row['apellido'],
            rol: new RolUsuario($row['rol']),
            username: $row['username'],
            passwordHash: $row['password_hash'],
            email: $row['email'],
            documentoIdentidad: $row['documento_identidad'],
            licencia: $row['licencia'],
            telefono: $row['telefono'],
            activo: (bool) $row['activo'],
            createdAt: $row['created_at']
        );
    }

    public function findPacienteByToken(string $token): ?Paciente
    {
        $stmt = $this->pdo->prepare("
            SELECT u.*, p.*
            FROM usuario u
            JOIN paciente p ON p.id = u.id
            WHERE p.token_acceso = ?
        ");
        $stmt->execute([$token]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return $this->hydratePaciente($row);
    }

    public function findPacienteByUsername(string $username): ?Paciente
    {
        $stmt = $this->pdo->prepare("
            SELECT u.*, p.*
            FROM usuario u
            JOIN paciente p ON p.id = u.id
            WHERE p.username = ?
        ");
        $stmt->execute([$username]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return $this->hydratePaciente($row);
    }

    public function saveFuncionario(Funcionario $funcionario): Funcionario
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO usuario (tipo, nombre, apellido, email, documento_identidad)
                VALUES ('funcionario', ?, ?, ?, ?)
            ");
            $stmt->execute([
                $funcionario->getNombre(),
                $funcionario->getApellido(),
                $funcionario->getEmail(),
                $funcionario->getDocumentoIdentidad(),
            ]);
            $id = (int) $this->pdo->lastInsertId();

            $stmt = $this->pdo->prepare("
                INSERT INTO funcionario (id, username, password_hash, licencia, telefono, activo, rol)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $id,
                $funcionario->getUsername(),
                $funcionario->getPasswordHash(),
                $funcionario->getLicencia(),
                $funcionario->getTelefono(),
                $funcionario->isActivo() ? 1 : 0,
                $funcionario->getRol()->value(),
            ]);

            $this->pdo->commit();
            $funcionario->setId($id);
            return $funcionario;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function savePaciente(Paciente $paciente): Paciente
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO usuario (tipo, nombre, apellido, email, documento_identidad)
                VALUES ('paciente', ?, ?, ?, ?)
            ");
            $stmt->execute([
                $paciente->getNombre(),
                $paciente->getApellido(),
                $paciente->getEmail(),
                $paciente->getDocumentoIdentidad(),
            ]);
            $id = (int) $this->pdo->lastInsertId();

            $stmt = $this->pdo->prepare("
                INSERT INTO paciente (id, token_acceso, codigo_qr_id, username, password_hash, telefono, activo)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $id,
                $paciente->getTokenAcceso(),
                $paciente->getCodigoQrId(),
                $paciente->getUsername(),
                $paciente->getPasswordHash(),
                $paciente->getTelefono(),
                $paciente->isActivo() ? 1 : 0,
            ]);

            $this->pdo->commit();
            $paciente->setId($id);
            return $paciente;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function updatePaciente(Paciente $paciente): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE usuario SET nombre = ?, apellido = ?, email = ?, documento_identidad = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $paciente->getNombre(),
            $paciente->getApellido(),
            $paciente->getEmail(),
            $paciente->getDocumentoIdentidad(),
            $paciente->getId(),
        ]);

        $stmt = $this->pdo->prepare("
            UPDATE paciente SET username = ?, telefono = ?, activo = ?, password_hash = COALESCE(?, password_hash)
            WHERE id = ?
        ");
        $stmt->execute([
            $paciente->getUsername(),
            $paciente->getTelefono(),
            $paciente->isActivo() ? 1 : 0,
            $paciente->getPasswordHash(),
            $paciente->getId(),
        ]);
    }

    public function updateFuncionario(Funcionario $funcionario): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE usuario SET nombre = ?, apellido = ?, email = ?, documento_identidad = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $funcionario->getNombre(),
            $funcionario->getApellido(),
            $funcionario->getEmail(),
            $funcionario->getDocumentoIdentidad(),
            $funcionario->getId(),
        ]);

        $stmt = $this->pdo->prepare("
            UPDATE funcionario SET username = ?, licencia = ?, telefono = ?, activo = ?, rol = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $funcionario->getUsername(),
            $funcionario->getLicencia(),
            $funcionario->getTelefono(),
            $funcionario->isActivo() ? 1 : 0,
            $funcionario->getRol()->value(),
            $funcionario->getId(),
        ]);
    }

    public function findAllFuncionarios(?bool $activo = null): array
    {
        $sql = "
            SELECT u.*, f.*
            FROM usuario u
            JOIN funcionario f ON f.id = u.id
        ";
        $params = [];
        if ($activo !== null) {
            $sql .= " WHERE f.activo = ?";
            $params[] = $activo ? 1 : 0;
        }
        $sql .= " ORDER BY u.apellido, u.nombre";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[] = new Funcionario(
                id: (int) $row['id'],
                nombre: $row['nombre'],
                apellido: $row['apellido'],
                rol: new RolUsuario($row['rol']),
                username: $row['username'],
                passwordHash: $row['password_hash'],
                email: $row['email'],
                documentoIdentidad: $row['documento_identidad'],
                licencia: $row['licencia'],
                telefono: $row['telefono'],
                activo: (bool) $row['activo'],
                createdAt: $row['created_at']
            );
        }
        return $result;
    }

    public function findAllPacientes(): array
    {
        $stmt = $this->pdo->query("
            SELECT u.*, p.*
            FROM usuario u
            JOIN paciente p ON p.id = u.id
            ORDER BY u.apellido, u.nombre
        ");
        $rows = $stmt->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->hydratePaciente($row);
        }
        return $result;
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM usuario WHERE id = ?");
        $stmt->execute([$id]);
    }

    private function hydratePaciente(array $row): Paciente
    {
        return new Paciente(
            id: (int) $row['id'],
            nombre: $row['nombre'],
            apellido: $row['apellido'],
            email: $row['email'],
            documentoIdentidad: $row['documento_identidad'],
            tokenAcceso: $row['token_acceso'] ?? null,
            codigoQrId: isset($row['codigo_qr_id']) ? (int) $row['codigo_qr_id'] : null,
            username: $row['username'] ?? null,
            passwordHash: $row['password_hash'] ?? null,
            telefono: $row['telefono'] ?? null,
            activo: isset($row['activo']) ? (bool) $row['activo'] : true,
            createdAt: $row['created_at']
        );
    }

    private function hydrate(array $row): Usuario
    {
        if (!empty($row['rol'])) {
            return new Funcionario(
                id: (int) $row['id'],
                nombre: $row['nombre'],
                apellido: $row['apellido'],
                rol: new RolUsuario($row['rol']),
                username: $row['username'],
                passwordHash: $row['password_hash'],
                email: $row['email'],
                documentoIdentidad: $row['documento_identidad'],
                licencia: $row['licencia'],
                telefono: $row['telefono'],
                activo: (bool) $row['activo'],
                createdAt: $row['created_at']
            );
        }

        return $this->hydratePaciente($row);
    }
}
