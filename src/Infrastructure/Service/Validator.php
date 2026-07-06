<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Service;

class Validator
{
    private array $errors = [];

    public function required(string $field, mixed $value, string $label = ''): self
    {
        $label = $label ?: $field;
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->errors[$field][] = "{$label} es requerido";
        }
        return $this;
    }

    public function email(string $field, ?string $value, string $label = ''): self
    {
        if ($value === null || $value === '') {
            return $this;
        }
        $label = $label ?: $field;
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "{$label} no es un email válido";
        }
        return $this;
    }

    public function minLength(string $field, ?string $value, int $min, string $label = ''): self
    {
        if ($value === null || $value === '') {
            return $this;
        }
        $label = $label ?: $field;
        if (mb_strlen($value) < $min) {
            $this->errors[$field][] = "{$label} debe tener al menos {$min} caracteres";
        }
        return $this;
    }

    public function maxLength(string $field, ?string $value, int $max, string $label = ''): self
    {
        if ($value === null || $value === '') {
            return $this;
        }
        $label = $label ?: $field;
        if (mb_strlen($value) > $max) {
            $this->errors[$field][] = "{$label} no puede tener más de {$max} caracteres";
        }
        return $this;
    }

    public function numeric(string $field, mixed $value, string $label = ''): self
    {
        if ($value === null || $value === '') {
            return $this;
        }
        $label = $label ?: $field;
        if (!is_numeric($value)) {
            $this->errors[$field][] = "{$label} debe ser un valor numérico";
        }
        return $this;
    }

    public function inArray(string $field, mixed $value, array $allowed, string $label = ''): self
    {
        if ($value === null || $value === '') {
            return $this;
        }
        $label = $label ?: $field;
        if (!in_array($value, $allowed, true)) {
            $this->errors[$field][] = "{$label} contiene un valor no permitido";
        }
        return $this;
    }

    public function date(string $field, ?string $value, string $format = 'Y-m-d', string $label = ''): self
    {
        if ($value === null || $value === '') {
            return $this;
        }
        $label = $label ?: $field;
        $d = \DateTime::createFromFormat($format, $value);
        if (!$d || $d->format($format) !== $value) {
            $this->errors[$field][] = "{$label} no es una fecha válida";
        }
        return $this;
    }

    public function fileSize(string $field, ?array $file, int $maxBytes, string $label = ''): self
    {
        if ($file === null || !isset($file['size'])) {
            return $this;
        }
        $label = $label ?: $field;
        if ($file['size'] > $maxBytes) {
            $maxMb = $maxBytes / 1024 / 1024;
            $this->errors[$field][] = "{$label} no puede superar los {$maxMb}MB";
        }
        return $this;
    }

    public function fileMime(string $field, ?array $file, array $allowedMimes, string $label = ''): self
    {
        if ($file === null || !isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return $this;
        }
        $label = $label ?: $field;
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $allowedMimes, true)) {
            $this->errors[$field][] = "{$label} debe ser un tipo de archivo válido";
        }
        return $this;
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return $fieldErrors[0];
            }
        }
        return null;
    }

    public function reset(): void
    {
        $this->errors = [];
    }

    public static function sanitize(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    public static function sanitizeArray(array $data, array $allowedKeys): array
    {
        $result = [];
        foreach ($allowedKeys as $key) {
            if (isset($data[$key])) {
                if (is_string($data[$key])) {
                    $result[$key] = self::sanitize($data[$key]);
                } else {
                    $result[$key] = $data[$key];
                }
            }
        }
        return $result;
    }
}
