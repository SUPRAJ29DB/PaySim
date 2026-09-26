<?php
/**
 * PaySim - Input Validation Utility
 */

class Validator {
    private array $errors = [];

    public function validate(array $data, array $rules): bool {
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $ruleList = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;

            foreach ($ruleList as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $methodName = 'validate' . str_replace(' ', '', ucwords(str_replace('_', ' ', $rule)));
                if (method_exists($this, $methodName)) {
                    $isValid = $this->$methodName($field, $value, $params, $data);
                    if (!$isValid) {
                        break; // Stop evaluating further rules for this field
                    }
                }
            }
        }

        return empty($this->errors);
    }

    public function getErrors(): array {
        return $this->errors;
    }

    public function getFirstError(): ?string {
        if (empty($this->errors)) {
            return null;
        }
        $firstField = array_key_first($this->errors);
        return $this->errors[$firstField][0] ?? null;
    }

    protected function addError(string $field, string $message): void {
        $this->errors[$field][] = $message;
    }

    // Validation Methods
    protected function validateRequired(string $field, $value): bool {
        if ($value === null || (is_string($value) && trim($value) === '') || (is_array($value) && empty($value))) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' is required.');
            return false;
        }
        return true;
    }

    protected function validateEmail(string $field, $value): bool {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'Please enter a valid email address.');
            return false;
        }
        return true;
    }

    protected function validateMin(string $field, $value, array $params): bool {
        $min = (int) ($params[0] ?? 0);
        if (is_numeric($value)) {
            if ($value < $min) {
                $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . " must be at least {$min}.");
                return false;
            }
        } elseif (is_string($value)) {
            if (mb_strlen($value) < $min) {
                $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . " must be at least {$min} characters.");
                return false;
            }
        }
        return true;
    }

    protected function validateMax(string $field, $value, array $params): bool {
        $max = (int) ($params[0] ?? 0);
        if (is_numeric($value)) {
            if ($value > $max) {
                $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . " cannot exceed {$max}.");
                return false;
            }
        } elseif (is_string($value)) {
            if (mb_strlen($value) > $max) {
                $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . " cannot exceed {$max} characters.");
                return false;
            }
        }
        return true;
    }

    protected function validateNumeric(string $field, $value): bool {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' must be a valid number.');
            return false;
        }
        return true;
    }

    protected function validatePositive(string $field, $value): bool {
        if ($value !== null && $value !== '' && (!is_numeric($value) || (float)$value <= 0)) {
            $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' must be greater than zero.');
            return false;
        }
        return true;
    }

    protected function validateUsername(string $field, $value): bool {
        if ($value !== null && $value !== '' && !preg_match('/^[a-zA-Z0-9_]{3,30}$/', $value)) {
            $this->addError($field, 'Username must be 3-30 characters with only letters, numbers, and underscores.');
            return false;
        }
        return true;
    }

    protected function validatePhone(string $field, $value): bool {
        $cleaned = preg_replace('/[^0-9]/', '', (string)$value);
        if (strlen($cleaned) < 10 || strlen($cleaned) > 13) {
            $this->addError($field, 'Please enter a valid 10-digit mobile number.');
            return false;
        }
        return true;
    }

    protected function validatePin(string $field, $value): bool {
        if (!preg_match('/^[0-9]{4,6}$/', (string)$value)) {
            $this->addError($field, 'UPI PIN must be 4 or 6 numeric digits.');
            return false;
        }
        return true;
    }
}
