<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Auth;

use Elyra\Domain\Repository\UsuarioRepositoryInterface;
use Elyra\Infrastructure\Service\EmailServiceInterface;

final class SolicitarResetPasswordUseCase
{
    public function __construct(
        private UsuarioRepositoryInterface $usuarioRepo,
        private EmailServiceInterface $emailService,
    ) {
    }

    /**
     * @param array{email: string} $input
     *
     * @return array{success: bool}
     */
    public function execute(array $input): array
    {
        $email = trim($input['email']);

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Ingresá un email válido.');
        }

        $user = $this->usuarioRepo->findUserByEmail($email);

        if ($user === null) {
            return ['success' => true];
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);

        $this->usuarioRepo->saveResetToken($user->getId() ?? 0, $tokenHash, $expiresAt);

        /** @var string $appUrl */
        $appUrl = $_ENV['APP_URL'] ?? 'http://localhost';
        $resetUrl = rtrim($appUrl, '/') . '/restablecer-contrasena?token=' . $token;

        $nombre = htmlspecialchars($user->getNombre(), ENT_QUOTES, 'UTF-8');
        $escapedUrl = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');
        $html = "
        <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
            <h2 style='color:#1a5276;'>Recuperar contraseña — Elyra</h2>
            <p>Hola <strong>{$nombre}</strong>,</p>
            <p>Recibimos una solicitud para restablecer tu contraseña.</p>
            <p>Hacé clic en el siguiente enlace (válido por 1 hora):</p>
            <p style='margin:20px 0;'>
                <a href='{$escapedUrl}' style='background:#1a5276;color:#fff;padding:12px 24px;text-decoration:none;border-radius:6px;'>Restablecer contraseña</a>
            </p>
            <p style='color:#666;font-size:13px;'>Si no solicitaste este cambio, ignorá este email.</p>
            <hr style='border:none;border-top:1px solid #eee;'>
            <p style='color:#999;font-size:12px;'>Hospital de Clínicas — Sistema Elyra</p>
        </div>";

        $this->emailService->send($email, 'Restablecer contraseña — Elyra', $html);

        return ['success' => true];
    }
}
