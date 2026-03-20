<?php
/**
 * Input Validation Helper
 * Uaddara Basic School — SBA Management System
 */

class Validator {

    private array $errors = [];
    private array $data   = [];

    public function __construct(array $data) {
        $this->data = $data;
    }

    /**
     * Run all rules and return true if no errors.
     *
     * @param array $rules  ['field' => 'required|max:100|email', ...]
     */
    public function validate(array $rules): bool {
        foreach ($rules as $field => $ruleStr) {
            $value = $this->data[$field] ?? null;
            $parts = explode('|', $ruleStr);
            foreach ($parts as $rule) {
                [$ruleName, $param] = array_pad(explode(':', $rule, 2), 2, null);
                $this->applyRule($field, $value, $ruleName, $param);
            }
        }
        return empty($this->errors);
    }

    private function applyRule(string $field, mixed $value, string $rule, ?string $param): void {
        $label = ucfirst(str_replace('_', ' ', $field));
        switch ($rule) {
            case 'required':
                if ($value === null || $value === '') {
                    $this->errors[$field][] = "{$label} is required.";
                }
                break;

            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = "{$label} must be a valid email address.";
                }
                break;

            case 'min':
                if ($value !== null && strlen((string)$value) < (int)$param) {
                    $this->errors[$field][] = "{$label} must be at least {$param} characters.";
                }
                break;

            case 'max':
                if ($value !== null && strlen((string)$value) > (int)$param) {
                    $this->errors[$field][] = "{$label} cannot exceed {$param} characters.";
                }
                break;

            case 'numeric':
                if ($value !== null && $value !== '' && !is_numeric($value)) {
                    $this->errors[$field][] = "{$label} must be a number.";
                }
                break;

            case 'min_val':
                if ($value !== null && $value !== '' && (float)$value < (float)$param) {
                    $this->errors[$field][] = "{$label} must be at least {$param}.";
                }
                break;

            case 'max_val':
                if ($value !== null && $value !== '' && (float)$value > (float)$param) {
                    $this->errors[$field][] = "{$label} cannot exceed {$param}.";
                }
                break;

            case 'integer':
                if ($value !== null && $value !== '' && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->errors[$field][] = "{$label} must be a whole number.";
                }
                break;

            case 'date':
                if ($value && !strtotime($value)) {
                    $this->errors[$field][] = "{$label} must be a valid date.";
                }
                break;

            case 'in':
                $allowed = explode(',', $param ?? '');
                if ($value !== null && $value !== '' && !in_array($value, $allowed, true)) {
                    $this->errors[$field][] = "{$label} contains an invalid value.";
                }
                break;

            case 'digits':
                if ($value !== null && $value !== '' && (!ctype_digit((string)$value) || strlen($value) !== (int)$param)) {
                    $this->errors[$field][] = "{$label} must be exactly {$param} digits.";
                }
                break;

            case 'phone':
                if ($value && !preg_match('/^[\d\+\-\(\)\s]{7,15}$/', $value)) {
                    $this->errors[$field][] = "{$label} must be a valid phone number.";
                }
                break;
        }
    }

    public function errors(): array { return $this->errors; }

    public function firstError(string $field): ?string {
        return $this->errors[$field][0] ?? null;
    }

    public function hasError(string $field): bool {
        return !empty($this->errors[$field]);
    }

    public function allErrors(): array {
        $flat = [];
        foreach ($this->errors as $msgs) {
            foreach ($msgs as $msg) $flat[] = $msg;
        }
        return $flat;
    }

    /** Sanitise a string value */
    public static function sanitise(string $value): string {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }

    /** Sanitise an array of string values */
    public static function sanitiseAll(array $data): array {
        return array_map([self::class, 'sanitise'], $data);
    }

    /** Quick one-shot static validation */
    public static function make(array $data, array $rules): self {
        $v = new self($data);
        $v->validate($rules);
        return $v;
    }
}
