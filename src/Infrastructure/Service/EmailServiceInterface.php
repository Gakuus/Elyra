<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Service;

interface EmailServiceInterface
{
    public function send(string $to, string $subject, string $htmlBody): bool;
}
