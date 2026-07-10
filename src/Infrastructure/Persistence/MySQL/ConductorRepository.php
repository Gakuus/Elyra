<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Persistence\MySQL;

use Elyra\Domain\Entity\Funcionario;
use Elyra\Domain\Repository\ConductorRepositoryInterface;
use Elyra\Domain\ValueObject\RolUsuario;

class ConductorRepository implements ConductorRepositoryInterface
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::get();
    }

    public function findById(int $id): ?Funcionario
    {
        $stmt = $this->pdo->prepare("
            SELECT u.*, f.*
            FROM usuario u
            JOIN funcionario f ON f.id = u.id
            WHERE u.id = ? AND f.rol = 'conductor'
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return $this->hydrate($row);
    }

    public function findAll(?bool $activo = null): array
    {
        $sql = "SELECT u.*, f.* FROM usuario u JOIN funcionario f ON f.id = u.id WHERE f.rol = 'conductor'";
        $params = [];

        if ($activo !== null) {
            $sql .= " AND f.activo = ?";
            $params[] = $activo ? 1 : 0;
        }

        $sql .= " ORDER BY u.apellido, u.nombre";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    public function countTotal(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM funcionario WHERE rol = 'conductor'");
        return (int) $stmt->fetchColumn();
    }

    public function countActivos(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM funcionario WHERE rol = 'conductor' AND activo = 1");
        return (int) $stmt->fetchColumn();
    }

    public function findDisponibles(): array
    {
        $stmt = $this->pdo->query("
            SELECT u.*, f.*
            FROM usuario u
            JOIN funcionario f ON f.id = u.id
            WHERE f.rol = 'conductor' AND f.activo = 1
            ORDER BY u.apellido, u.nombre
        ");
        $rows = $stmt->fetchAll();

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    public function save(Funcionario $conductor): Funcionario
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO usuario (tipo, nombre, apellido, email, documento_identidad, foto)
                VALUES ('funcionario', ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $conductor->getNombre(),
                $conductor->getApellido(),
                $conductor->getEmail(),
                $conductor->getDocumentoIdentidad(),
                $conductor->getFoto(),
            ]);
            $id = (int) $this->pdo->lastInsertId();

            $stmt = $this->pdo->prepare("
                INSERT INTO funcionario (id, username, password_hash, licencia, telefono, activo, rol)
                VALUES (?, ?, ?, ?, ?, ?, 'conductor')
            ");
            $stmt->execute([
                $id,
                $conductor->getUsername(),
                $conductor->getPasswordHash(),
                $conductor->getLicencia(),
                $conductor->getTelefono(),
                $conductor->isActivo() ? 1 : 0,
            ]);

            $this->pdo->commit();
            $conductor->setId($id);
            return $conductor;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update(Funcionario $conductor): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE usuario SET nombre = ?, apellido = ?, email = ?, documento_identidad = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $conductor->getNombre(),
            $conductor->getApellido(),
            $conductor->getEmail(),
            $conductor->getDocumentoIdentidad(),
            $conductor->getId(),
        ]);

        $stmt = $this->pdo->prepare("
            UPDATE funcionario SET username = ?, licencia = ?, telefono = ?, activo = ?
            WHERE id = ? AND rol = 'conductor'
        ");
        $stmt->execute([
            $conductor->getUsername(),
            $conductor->getLicencia(),
            $conductor->getTelefono(),
            $conductor->isActivo() ? 1 : 0,
            $conductor->getId(),
        ]);
    }

    public function delete(int $id): void
    {
        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare("DELETE FROM funcionario WHERE id = ? AND rol = 'conductor'")->execute([$id]);
            $this->pdo->prepare("DELETE FROM usuario WHERE id = ?")->execute([$id]);
            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function hydrate(array $row): Funcionario
    {
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
            foto: $row['foto'] ?? null,
            createdAt: $row['created_at'],
        );
    }
}
