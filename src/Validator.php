<?php

namespace Artemis;

class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function make(array $data, array $rules): self
    {
        $instance = new self($data);
        $instance->validate($rules);
        return $instance;
    }

    private function validate(array $rules): void
    {
        foreach ($rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);

            foreach ($rules as $rule) {
                $this->applyRule($field, $rule);
            }
        }
    }

    private function applyRule(string $field, string $rule): void
    {
        $value = $this->data[$field] ?? null;

        if ($rule === 'required') {
            if (empty($value) && $value !== '0') {
                $this->errors[$field][] = "Field $field is required";
            }
        }

        if (str_starts_with($rule, 'min:')) {
            $min = (int) explode(':', $rule)[1];
            if (strlen($value) < $min) {
                $this->errors[$field][] = "Field $field minimum $min characters";
            }
        }

        if (str_starts_with($rule, 'max:')) {
            $max = (int) explode(':', $rule)[1];
            if (strlen($value) > $max) {
                $this->errors[$field][] = "Field $field maximum $max characters";
            }
        }

        if ($rule === 'email') {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $this->errors[$field][] = "Field $field must be a valid email";
            }
        }

        if ($rule === 'numeric') {
            if (!is_numeric($value)) {
                $this->errors[$field][] = "Field $field must be numeric";
            }
        }
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): string
    {
        $first = array_values($this->errors)[0];
        return $first[0];
    }
}