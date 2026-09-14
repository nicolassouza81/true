<?php

declare(strict_types=1);

namespace Core;

/**
 * Validador simples baseado em regras por campo.
 *
 * Exemplo:
 *   $v = new Validator($request->all(), [
 *       'email'    => ['required', 'email'],
 *       'password' => ['required', 'min:8'],
 *   ]);
 *
 *   if (!$v->passes()) {
 *       $errors = $v->errors();
 *   }
 */
final class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->validate();
    }

    private function validate(): void
    {
        foreach ($this->rules as $field => $rules) {
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                [$ruleName, $param] = $this->parseRule($rule);
                $this->applyRule($field, $value, $ruleName, $param);
            }
        }
    }

    private function parseRule(string $rule): array
    {
        if (str_contains($rule, ':')) {
            [$name, $param] = explode(':', $rule, 2);
            return [$name, $param];
        }

        return [$rule, null];
    }

    private function applyRule(string $field, mixed $value, string $rule, ?string $param): void
    {
        $label = ucfirst(str_replace('_', ' ', $field));

        switch ($rule) {
            case 'required':
                if ($value === null || $value === '') {
                    $this->addError($field, "{$label} é obrigatório.");
                }
                break;

            case 'email':
                if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "{$label} deve ser um e-mail válido.");
                }
                break;

            case 'min':
                $min = (int) $param;
                if ($value !== null && mb_strlen((string) $value) < $min) {
                    $this->addError($field, "{$label} deve ter no mínimo {$min} caracteres.");
                }
                break;

            case 'max':
                $max = (int) $param;
                if ($value !== null && mb_strlen((string) $value) > $max) {
                    $this->addError($field, "{$label} deve ter no máximo {$max} caracteres.");
                }
                break;

            case 'numeric':
                if ($value !== null && $value !== '' && !is_numeric($value)) {
                    $this->addError($field, "{$label} deve ser numérico.");
                }
                break;

            case 'confirmed':
                $confirmField = "{$field}_confirmation";
                if (($this->data[$confirmField] ?? null) !== $value) {
                    $this->addError($field, "Confirmação de {$label} não corresponde.");
                }
                break;

            case 'in':
                $allowed = $param !== null ? explode(',', $param) : [];
                if ($value !== null && $value !== '' && !in_array((string) $value, $allowed, true)) {
                    $this->addError($field, "{$label} possui um valor inválido.");
                }
                break;

            case 'alpha_dash':
                if ($value !== null && $value !== '' && !preg_match('/^[a-zA-Z0-9_-]+$/', (string) $value)) {
                    $this->addError($field, "{$label} deve conter apenas letras, números, hífen ou underscore.");
                }
                break;

            case 'url':
                if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->addError($field, "{$label} deve ser uma URL válida.");
                }
                break;

            case 'hex_color':
                if ($value !== null && $value !== '' && !preg_match('/^#[0-9a-fA-F]{6}$/', (string) $value)) {
                    $this->addError($field, "{$label} deve ser uma cor hexadecimal válida (ex: #5b8def).");
                }
                break;

            case 'image_url':
                if ($value !== null && $value !== '') {
                    if (!filter_var($value, FILTER_VALIDATE_URL)) {
                        $this->addError($field, "{$label} deve ser uma URL válida.");
                        break;
                    }
                    $ext = strtolower(pathinfo((string) parse_url($value, PHP_URL_PATH), PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif'];
                    if (!in_array($ext, $allowed, true)) {
                        $this->addError($field, "{$label} deve apontar para uma imagem (jpg, png, gif, webp, svg, avif).");
                    }
                }
                break;
        }
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        foreach ($this->errors as $messages) {
            return $messages[0] ?? null;
        }

        return null;
    }

    /** Sanitiza uma string: remove tags e espaços extras. */
    public static function sanitize(string $value): string
    {
        return trim(strip_tags($value));
    }
}
