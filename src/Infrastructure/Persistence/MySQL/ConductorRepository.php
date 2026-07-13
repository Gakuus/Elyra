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
            WHERE u.id = ? AND f.rol IN ('conductor', 'copiloto')
        ");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$id]);
        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch();

        if (!$row) return null;

        return $this->hydrate($row);
    }

    public function findAll(?bool $activo = null, string $buscar = ''): array
    {
        $sql = "SELECT u.*, f.* FROM usuario u JOIN funcionario f ON f.id = u.id WHERE f.rol IN ('conductor', 'copiloto')";
        $params = [];

        if ($activo !== null) {
            $sql .= " AND f.activo = ?";
            $params[] = $activo ? 1 : 0;
        }

        if ($buscar !== '') {
            $buscarLower = strtolower($buscar);
            $escaped = addcslashes($buscarLower, '%_');
            $sql .= " AND (
                LOWER(u.nombre) LIKE ? OR LOWER(u.apellido) LIKE ?
                OR LOWER(f.username) LIKE ? OR u.documento_identidad LIKE ?
            )";
            $like = '%' . $escaped . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " ORDER BY u.apellido, u.nombre";

        $stmt = $this->pdo->prepare($sql);
        /** @var \PDOStatement $stmt */
        $stmt->execute($params);
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $stmt->fetchAll();

        return array_map(fn (array $row) => $this->hydrate($row), $rows);
    }

    public function countTotal(): int
    {
        /** @var \PDOStatement $stmt */
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM funcionario WHERE rol IN ('conductor', 'copiloto')");
        return (int) $stmt->fetchColumn();
    }

    public function countActivos(): int
    {
        /** @var \PDOStatement $stmt */
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM funcionario WHERE rol IN ('conductor', 'copiloto') AND activo = 1");
        return (int) $stmt->fetchColumn();
    }

    public function findDisponibles(): array
    {
        /** @var \PDOStatement $stmt */
        $stmt = $this->pdo->query("
            SELECT u.*, f.*
            FROM usuario u
            JOIN funcionario f ON f.id = u.id
            WHERE f.rol IN ('conductor', 'copiloto') AND f.activo = 1
            ORDER BY u.apellido, u.nombre
        ");
        /** @var array<int, array<string, mixed>> $rows */
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
            /** @var \PDOStatement $stmt */
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
            /** @var \PDOStatement $stmt */
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
        /** @var \PDOStatement $stmt */
        $stmt->execute([
            $conductor->getNombre(),
            $conductor->getApellido(),
            $conductor->getEmail(),
            $conductor->getDocumentoIdentidad(),
            $conductor->getId(),
        ]);

        $stmt = $this->pdo->prepare("
            UPDATE funcionario SET username = ?, licencia = ?, telefono = ?, activo = ?
            WHERE id = ? AND rol IN ('conductor', 'copiloto')
        ");
        /** @var \PDOStatement $stmt */
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
            $this->pdo->prepare("DELETE FROM funcionario WHERE id = ? AND rol IN ('conductor', 'copiloto')")->execute([$id]);
            $this->pdo->prepare("DELETE FROM usuario WHERE id = ?")->execute([$id]);
            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): Funcionario
    {
        /** @var int $id */
        $id = $row['id'];
        /** @var string $nombre */
        $nombre = $row['nombre'];
        /** @var string $apellido */
        $apellido = $row['apellido'];
        /** @var string $rol */
        $rol = $row['rol'];
        /** @var string|null $username */
        $username = $row['username'];
        /** @var string|null $passwordHash */
        $passwordHash = $row['password_hash'];
        /** @var string|null $email */
        $email = $row['email'];
        /** @var string|null $documentoIdentidad */
        $documentoIdentidad = $row['documento_identidad'];
        /** @var string|null $licencia */
        $licencia = $row['licencia'];
        /** @var string|null $telefono */
        $telefono = $row['telefono'];
        $activo = (bool) $row['activo'];
        /** @var string|null $foto */
        $foto = $row['foto'] ?? null;
        /** @var string|null $createdAt */
        $createdAt = $row['created_at'];

        return new Funcionario(
            id: $id,
            nombre: $nombre,
            apellido: $apellido,
            rol: new RolUsuario($rol),
            username: $username,
            passwordHash: $passwordHash,
            email: $email,
            documentoIdentidad: $documentoIdentidad,
            licencia: $licencia,
            telefono: $telefono,
            activo: $activo,
            foto: $foto,
            createdAt: $createdAt,
        );
    }
}
