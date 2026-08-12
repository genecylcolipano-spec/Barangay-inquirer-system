<?php

namespace App\Services;

class PasswordStrengthService
{
    /**
     * Password strength requirements
     */
    private const MIN_LENGTH = 8;
    private const HAS_UPPERCASE = '/[A-Z]/';
    private const HAS_LOWERCASE = '/[a-z]/';
    private const HAS_NUMBER = '/[0-9]/';
    private const HAS_SPECIAL = '/[!@#$%^&*()_+\-=\[\]{};:\'",.<>?\/\\|`~]/';

    /**
     * Validate if password meets all requirements
     */
    public function isStrong(string $password): bool
    {
        return $this->meetsMinLength($password)
            && $this->hasUppercase($password)
            && $this->hasLowercase($password)
            && $this->hasNumber($password)
            && $this->hasSpecialChar($password);
    }

    /**
     * Get detailed strength status
     */
    public function getStrengthStatus(string $password): array
    {
        return [
            'is_strong' => $this->isStrong($password),
            'length' => strlen($password),
            'min_length' => self::MIN_LENGTH,
            'meets_requirements' => [
                'min_length' => $this->meetsMinLength($password),
                'uppercase' => $this->hasUppercase($password),
                'lowercase' => $this->hasLowercase($password),
                'number' => $this->hasNumber($password),
                'special_char' => $this->hasSpecialChar($password),
            ],
            'strength_score' => $this->calculateStrength($password),
        ];
    }

    /**
     * Get all requirement messages
     */
    public function getRequirements(): array
    {
        return [
            'min_length' => 'At least 8 characters',
            'uppercase' => 'At least one uppercase letter (A-Z)',
            'lowercase' => 'At least one lowercase letter (a-z)',
            'number' => 'At least one number (0-9)',
            'special_char' => 'At least one special character (!@#$%^&*)',
        ];
    }

    /**
     * Check if password meets minimum length
     */
    private function meetsMinLength(string $password): bool
    {
        return strlen($password) >= self::MIN_LENGTH;
    }

    /**
     * Check if password has uppercase letter
     */
    private function hasUppercase(string $password): bool
    {
        return preg_match(self::HAS_UPPERCASE, $password) === 1;
    }

    /**
     * Check if password has lowercase letter
     */
    private function hasLowercase(string $password): bool
    {
        return preg_match(self::HAS_LOWERCASE, $password) === 1;
    }

    /**
     * Check if password has number
     */
    private function hasNumber(string $password): bool
    {
        return preg_match(self::HAS_NUMBER, $password) === 1;
    }

    /**
     * Check if password has special character
     */
    private function hasSpecialChar(string $password): bool
    {
        return preg_match(self::HAS_SPECIAL, $password) === 1;
    }

    /**
     * Calculate password strength score (0-100)
     */
    private function calculateStrength(string $password): int
    {
        $score = 0;

        // Base 20 points for minimum length
        if ($this->meetsMinLength($password)) {
            $score += 20;
        }

        // 20 points for each requirement met
        if ($this->hasUppercase($password)) {
            $score += 20;
        }

        if ($this->hasLowercase($password)) {
            $score += 20;
        }

        if ($this->hasNumber($password)) {
            $score += 20;
        }

        if ($this->hasSpecialChar($password)) {
            $score += 20;
        }

        return min(100, $score);
    }

    /**
     * Get strength level label
     */
    public function getStrengthLevel(string $password): string
    {
        $score = $this->calculateStrength($password);

        if ($score < 40) {
            return 'weak';
        }

        if ($score < 60) {
            return 'fair';
        }

        if ($score < 80) {
            return 'good';
        }

        return 'strong';
    }

    /**
     * Get strength level color
     */
    public function getStrengthColor(string $password): string
    {
        $level = $this->getStrengthLevel($password);

        return match ($level) {
            'weak' => '#e74c3c',
            'fair' => '#f39c12',
            'good' => '#3498db',
            'strong' => '#27ae60',
            default => '#95a5a6',
        };
    }
}
