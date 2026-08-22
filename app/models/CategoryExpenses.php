<?php

namespace App\Models;

class CategoryExpense
{
    public function __construct(
        private int $idCategories,
        private string $categoryName,
        private string $categoryColor,
        private float $totalAmount
    ) {}

    public function getIdCategories(): int
    {
        return $this->idCategories;
    }

    public function getCategoryName(): string
    {
        return $this->categoryName;
    }

    public function getCategoryColor(): string
    {
        return $this->categoryColor;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function getFormattedAmount(): string
    {
        return number_format($this->totalAmount, 2, '.', ' ') . ' €';
    }
}