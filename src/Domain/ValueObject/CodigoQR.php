<?php

declare(strict_types=1);

namespace Elyra\Domain\ValueObject;

class CodigoQR
{
    private string $token;

    public function __construct(?string $token = null)
    {
        $this->token = $token ?? bin2hex(random_bytes(32));
    }

    public function value(): string
    {
        return $this->token;
    }

    public function __toString(): string
    {
        return $this->token;
    }

    public function equals(self $other): bool
    {
        return hash_equals($this->token, $other->token);
    }

    public static function fromString(string $token): self
    {
        return new self($token);
    }
}
