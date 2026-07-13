<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Service;

/**
 * Immutable audit log service for non-repudiation compliance.
 * No UPDATE or DELETE operations are permitted on audit_log.
 */
class AuditLogger
{
    /**
     * @param array<string, mixed>|null $details
     */
    public static function log(
        string $action,
        string $entityType,
        ?string $entityId = null,
        ?array $details = null,
    ): void {
        try {
            $pdo = self::getPdo();
            $userId = SessionManager::getUserId();
            $userRole = SessionManager::getUserRole() ?? 'anonymous';
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            if (!is_string($ip)) {
                $ip = '127.0.0.1';
            }
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            if (!is_string($userAgent)) {
                $userAgent = '';
            }
            $userAgent = substr($userAgent, 0, 500);

            $username = self::resolveUsername($userId, $userRole);

            $stmt = $pdo->prepare(
                'INSERT INTO audit_log (user_id, user_type, username, ip_address, user_agent, action, entity_type, entity_id, details)
                 VALUES (:user_id, :user_type, :username, :ip_address, :user_agent, :action, :entity_type, :entity_id, :details)'
            );

            $stmt->execute([
                ':user_id' => $userId,
                ':user_type' => $userRole,
                ':username' => $username,
                ':ip_address' => $ip,
                ':user_agent' => $userAgent,
                ':action' => $action,
                ':entity_type' => $entityType,
                ':entity_id' => $entityId,
                ':details' => $details !== null ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
            ]);
        } catch (\Throwable) {
            ErrorHandler::log('ERROR', "Audit log falló: {$action} {$entityType}");
        }
    }

    public static function logLogin(int $userId, string $role, string $username): void
    {
        self::log('login', 'auth', (string) $userId, [
            'role' => $role,
            'username' => $username,
        ]);
    }

    public static function logLogout(): void
    {
        self::log('logout', 'auth', SessionManager::getUserId() !== null ? (string) SessionManager::getUserId() : null);
    }

    /**
     * @param array<string, mixed>|null $details
     */
    public static function logCreate(string $entityType, ?string $entityId, ?array $details = null): void
    {
        self::log("create_{$entityType}", $entityType, $entityId, $details);
    }

    /**
     * @param array<string, mixed>|null $details
     */
    public static function logUpdate(string $entityType, ?string $entityId, ?array $details = null): void
    {
        self::log("update_{$entityType}", $entityType, $entityId, $details);
    }

    /**
     * @param array<string, mixed>|null $details
     */
    public static function logDelete(string $entityType, ?string $entityId, ?array $details = null): void
    {
        self::log("delete_{$entityType}", $entityType, $entityId, $details);
    }

    public static function logStateChange(
        string $entityType,
        ?string $entityId,
        string $fromState,
        string $toState,
    ): void {
        self::log("state_change_{$entityType}", $entityType, $entityId, [
            'from' => $fromState,
            'to' => $toState,
        ]);
    }

    private static function resolveUsername(?int $userId, string $role): ?string
    {
        if ($userId === null) {
            return null;
        }

        try {
            $pdo = self::getPdo();

            if ($role === 'paciente') {
                $stmt = $pdo->prepare('SELECT username FROM paciente WHERE id = :id LIMIT 1');
            } else {
                $stmt = $pdo->prepare('SELECT username FROM funcionario WHERE id = :id LIMIT 1');
            }

            $stmt->execute([':id' => $userId]);
            /** @var array{username?: string}|false $row */
            $row = $stmt->fetch();
            if ($row === false || !isset($row['username'])) {
                return null;
            }
            return $row['username'];
        } catch (\Throwable) {
            return null;
        }
    }

    private static function getPdo(): \PDO
    {
        return \Elyra\Infrastructure\Persistence\MySQL\Connection::get();
    }
}
