<?php

namespace App\Models;

class Action
{
    // On définit des constantes pour éviter les fautes de frappe sur les valeurs de "type"
    public const TYPE_DEPENSE = 'depense';
    public const TYPE_EPARGNE_VERSEMENT = 'epargne_versement';
    public const TYPE_EPARGNE_RETRAIT = 'epargne_retrait';

    public function __construct(
        private int $id,
        private float $amount,
        private string $actionDate,
        private string $type,
        private ?int $idCategories,
        private int $idUsers,
        private string $libelle,
        private ?string $categoryName = null,
        private ?string $categoryColor = null
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getFormattedAmount(): string
    {
        return number_format($this->amount, 2, ',', ' ') . ' €';
    }

    public function getActionDate(): string
    {
        return $this->actionDate;
    }

    public function getFormattedDate(): string
    {
        // Transforme "2026-08-16" en "16/08/2026"
        $date = new \DateTimeImmutable($this->actionDate);
        return $date->format('d/m/Y');
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isDepense(): bool
    {
        return $this->type === self::TYPE_DEPENSE;
    }

    public function isEpargneVersement(): bool
    {
        return $this->type === self::TYPE_EPARGNE_VERSEMENT;
    }

    public function isEpargneRetrait(): bool
    {
        return $this->type === self::TYPE_EPARGNE_RETRAIT;
    }


    public function getIdUsers(): int
    {
        return $this->idUsers;
    }

    public function getLibelle(): string
    {
        return $this->libelle;
    }

    public function getCategoryName(): ?string {
        return $this->categoryName;
    }

    public function getCategoryColor(): ?string {
        return $this->categoryColor;
    }
}
