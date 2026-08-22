<?php

namespace App\Models;

class MonthlyBudget
{
    public function __construct(
        private int $id,
        private string $month,
        private float $salary,
        private ?float $savingsGoals,
        private int $idUsers
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getMonth(): string
    {
        return $this->month;
    }

    public function getSalary(): float
    {
        return $this->salary;
    }

    public function getFormattedSalary(): string
    {
        return number_format($this->salary, 2, ',', ' ') . ' €';
    }

    public function getSavingsGoals(): ?float
    {
        return $this->savingsGoals;
    }

    public function getIdUsers(): int
    {
        return $this->idUsers;
    }
}