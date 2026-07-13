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
        /** @var \PDOStatement $stmt */
        $stmt->execute([$id]);
        /** @var array<string, mixed>|false $row */
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
        /** @var \PDOStatement $stmt */
        $stmt->execute([$username]);
        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch();

        if (!$row) return null;

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
        $fotoRaw = $row['foto'] ?? null;
        $foto = is_string($fotoRaw) ? $fotoRaw : null;
        /** @var string|null $createdAt */
        $createdAt = $row['created_at'];
        /** @var string|null $resetToken */
        $resetToken = $row['reset_token'] ?? null;
        /** @var string|null $resetTokenExpiresAt */
        $resetTokenExpiresAt = $row['reset_token_expires_at'] ?? null;

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
            resetToken: $resetToken,
            resetTokenExpiresAt: $resetTokenExpiresAt
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
        /** @var \PDOStatement $stmt */
        $stmt->execute([$email]);
        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch();

        if (!$row) return null;

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
        /** @var string|null $emailVal */
        $emailVal = $row['email'];
        /** @var string|null $documentoIdentidad */
        $documentoIdentidad = $row['documento_identidad'];
        /** @var string|null $licencia */
        $licencia = $row['licencia'];
        /** @var string|null $licenciaConducir */
        $licenciaConducir = $row['licencia_conducir'] ?? null;
        /** @var string|null $telefono */
        $telefono = $row['telefono'];
        $activo = (bool) $row['activo'];
        $fotoRaw = $row['foto'] ?? null;
        $foto = is_string($fotoRaw) ? $fotoRaw : null;
        /** @var string|null $createdAt */
        $createdAt = $row['created_at'];
        /** @var string|null $resetToken */
        $resetToken = $row['reset_token'] ?? null;
        /** @var string|null $resetTokenExpiresAt */
        $resetTokenExpiresAt = $row['reset_token_expires_at'] ?? null;

        return new Funcionario(
            id: $id,
            nombre: $nombre,
            apellido: $apellido,
            rol: new RolUsuario($rol),
            username: $username,
            passwordHash: $passwordHash,
            email: $emailVal,
            documentoIdentidad: $documentoIdentidad,
            licencia: $licencia,
            licenciaConducir: $licenciaConducir,
            telefono: $telefono,
            activo: $activo,
            foto: $foto,
            createdAt: $createdAt,
            resetToken: $resetToken,
            resetTokenExpiresAt: $resetTokenExpiresAt
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
        /** @var \PDOStatement $stmt */
        $stmt->execute([$token]);
        /** @var array<string, mixed>|false $row */
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
        /** @var \PDOStatement $stmt */
        $stmt->execute([$username]);
        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch();

        if (!$row) return null;

        return $this->hydratePaciente($row);
    }

    public function saveFuncionario(Funcionario $funcionario): Funcionario
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO usuario (tipo, nombre, apellido, email, documento_identidad, foto)
                VALUES ('funcionario', ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $funcionario->getNombre(),
                $funcionario->getApellido(),
                $funcionario->getEmail(),
                $funcionario->getDocumentoIdentidad(),
                $funcionario->getFoto(),
            ]);
            $id = (int) $this->pdo->lastInsertId();

            $stmt = $this->pdo->prepare("
                INSERT INTO funcionario (id, username, password_hash, licencia, licencia_conducir, telefono, activo, rol)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $id,
                $funcionario->getUsername(),
                $funcionario->getPasswordHash(),
                $funcionario->getLicencia(),
                $funcionario->getLicenciaConducir(),
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
                INSERT INTO usuario (tipo, nombre, apellido, email, documento_identidad, foto)
                VALUES ('paciente', ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $paciente->getNombre(),
                $paciente->getApellido(),
                $paciente->getEmail(),
                $paciente->getDocumentoIdentidad(),
                $paciente->getFoto(),
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
            UPDATE funcionario SET username = ?, licencia = ?, licencia_conducir = ?, telefono = ?, activo = ?, rol = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $funcionario->getUsername(),
            $funcionario->getLicencia(),
            $funcionario->getLicenciaConducir(),
            $funcionario->getTelefono(),
            $funcionario->isActivo() ? 1 : 0,
            $funcionario->getRol()->value(),
            $funcionario->getId(),
        ]);
    }

    public function updateFoto(int $userId, ?string $foto): void
    {
        $stmt = $this->pdo->prepare("UPDATE usuario SET foto = ? WHERE id = ?");
        $stmt->execute([$foto, $userId]);
    }

    public function findAllFuncionarios(?bool $activo = null): array
    {
        $sql = "
            SELECT u.*, f.*
            FROM usuario u
            JOIN funcionario f ON f.id = u.id
            WHERE f.rol NOT IN ('conductor', 'copiloto')
        ";
        $params = [];
        if ($activo !== null) {
            $sql .= " AND f.activo = ?";
            $params[] = $activo ? 1 : 0;
        }
        $sql .= " ORDER BY u.apellido, u.nombre";

        /** @var \PDOStatement $stmt */
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        /** @var array<int, array<string, mixed>> $rows */
        $rows = $stmt->fetchAll();

        $result = [];
        foreach ($rows as $row) {
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
            /** @var string|null $licenciaConducir */
            $licenciaConducir = $row['licencia_conducir'] ?? null;
            /** @var string|null $telefono */
            $telefono = $row['telefono'];
            $activo = (bool) $row['activo'];
            $fotoRaw = $row['foto'] ?? null;
            $foto = is_string($fotoRaw) ? $fotoRaw : null;
            /** @var string|null $createdAt */
            $createdAt = $row['created_at'];
            /** @var string|null $resetToken */
            $resetToken = $row['reset_token'] ?? null;
            /** @var string|null $resetTokenExpiresAt */
            $resetTokenExpiresAt = $row['reset_token_expires_at'] ?? null;

            $result[] = new Funcionario(
                id: $id,
                nombre: $nombre,
                apellido: $apellido,
                rol: new RolUsuario($rol),
                username: $username,
                passwordHash: $passwordHash,
                email: $email,
                documentoIdentidad: $documentoIdentidad,
                licencia: $licencia,
                licenciaConducir: $licenciaConducir,
                telefono: $telefono,
                activo: $activo,
                foto: $foto,
                createdAt: $createdAt,
                resetToken: $resetToken,
                resetTokenExpiresAt: $resetTokenExpiresAt
            );
        }
        return $result;
    }

    public function findByDocumentoIdentidad(string $documento): ?Paciente
    {
        $stmt = $this->pdo->prepare("
            SELECT u.*, p.*
            FROM usuario u
            JOIN paciente p ON p.id = u.id
            WHERE u.documento_identidad = ?
        ");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$documento]);
        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch();
        return $row ? $this->hydratePaciente($row) : null;
    }

    public function findAllPacientes(): array
    {
        /** @var \PDOStatement $stmt */
        $stmt = $this->pdo->query("
            SELECT u.*, p.*
            FROM usuario u
            JOIN paciente p ON p.id = u.id
            ORDER BY u.apellido, u.nombre
        ");
        /** @var array<int, array<string, mixed>> $rows */
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

    /** @param array<string, mixed> $row */
    private function hydratePaciente(array $row): Paciente
    {
        /** @var int $id */
        $id = $row['id'];
        /** @var string $nombre */
        $nombre = $row['nombre'];
        /** @var string $apellido */
        $apellido = $row['apellido'];
        /** @var string|null $email */
        $email = $row['email'];
        /** @var string|null $documentoIdentidad */
        $documentoIdentidad = $row['documento_identidad'];
        /** @var string|null $tokenAcceso */
        $tokenAcceso = $row['token_acceso'] ?? null;
        /** @var string|null $codigoQrIdRaw */
        $codigoQrIdRaw = $row['codigo_qr_id'] ?? null;
        /** @var int|null $codigoQrId */
        $codigoQrId = $codigoQrIdRaw !== null ? (int) $codigoQrIdRaw : null;
        /** @var string|null $username */
        $username = $row['username'] ?? null;
        /** @var string|null $passwordHash */
        $passwordHash = $row['password_hash'] ?? null;
        /** @var string|null $telefono */
        $telefono = $row['telefono'] ?? null;
        /** @var bool $activo */
        $activo = isset($row['activo']) ? (bool) $row['activo'] : true;
        $fotoRaw = $row['foto'] ?? null;
        $foto = is_string($fotoRaw) ? $fotoRaw : null;
        /** @var string|null $createdAt */
        $createdAt = $row['created_at'];
        /** @var string|null $resetToken */
        $resetToken = $row['reset_token'] ?? null;
        /** @var string|null $resetTokenExpiresAt */
        $resetTokenExpiresAt = $row['reset_token_expires_at'] ?? null;

        return new Paciente(
            id: $id,
            nombre: $nombre,
            apellido: $apellido,
            email: $email,
            documentoIdentidad: $documentoIdentidad,
            tokenAcceso: $tokenAcceso,
            codigoQrId: $codigoQrId,
            username: $username,
            passwordHash: $passwordHash,
            telefono: $telefono,
            activo: $activo,
            foto: $foto,
            createdAt: $createdAt,
            resetToken: $resetToken,
            resetTokenExpiresAt: $resetTokenExpiresAt
        );
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): Usuario
    {
        if (!empty($row['rol'])) {
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
        /** @var string|null $licenciaConducir */
        $licenciaConducir = $row['licencia_conducir'] ?? null;
        /** @var string|null $telefono */
        $telefono = $row['telefono'];
        $activo = (bool) $row['activo'];
        $fotoRaw = $row['foto'] ?? null;
        $foto = is_string($fotoRaw) ? $fotoRaw : null;
        /** @var string|null $createdAt */
        $createdAt = $row['created_at'];
        /** @var string|null $resetToken */
        $resetToken = $row['reset_token'] ?? null;
        /** @var string|null $resetTokenExpiresAt */
        $resetTokenExpiresAt = $row['reset_token_expires_at'] ?? null;

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
            licenciaConducir: $licenciaConducir,
            telefono: $telefono,
            activo: $activo,
            foto: $foto,
            createdAt: $createdAt,
                resetToken: $resetToken,
                resetTokenExpiresAt: $resetTokenExpiresAt
            );
        }

        return $this->hydratePaciente($row);
    }

    public function findUserByEmail(string $email): ?\Elyra\Domain\Entity\Usuario
    {
        $stmt = $this->pdo->prepare("SELECT id FROM usuario WHERE email = ?");
        /** @var \PDOStatement $stmt */
        $stmt->execute([$email]);
        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        /** @var int $id */
        $id = $row['id'];
        return $this->findById($id);
    }

    public function saveResetToken(int $userId, ?string $token, ?string $expiresAt): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE usuario SET reset_token = ?, reset_token_expires_at = ? WHERE id = ?
        ");
        $stmt->execute([$token, $expiresAt, $userId]);
    }

    public function findUserByResetToken(string $token): ?\Elyra\Domain\Entity\Usuario
    {
        $stmt = $this->pdo->prepare("
            SELECT u.id, u.tipo FROM usuario u
            LEFT JOIN funcionario f ON f.id = u.id
            WHERE u.reset_token = ? AND (u.tipo != 'funcionario' OR f.activo = 1)
        ");
        $stmt->execute([$token]);
        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        /** @var int $id */
        $id = $row['id'];
        return $this->findById($id);
    }

    public function updatePasswordHash(int $userId, string $passwordHash): void
    {
        $stmt = $this->pdo->prepare("SELECT tipo FROM usuario WHERE id = ?");
        $stmt->execute([$userId]);
        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch();

        if (!$row) {
            return;
        }

        /** @var string $tipo */
        $tipo = $row['tipo'];

        if ($tipo === 'funcionario') {
            $stmt2 = $this->pdo->prepare("UPDATE funcionario SET password_hash = ? WHERE id = ?");
            $stmt2->execute([$passwordHash, $userId]);
        } else {
            $stmt2 = $this->pdo->prepare("UPDATE paciente SET password_hash = ? WHERE id = ?");
            $stmt2->execute([$passwordHash, $userId]);
        }
    }

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollback(): void
    {
        $this->pdo->rollBack();
    }
}
