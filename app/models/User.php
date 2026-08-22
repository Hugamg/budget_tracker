<?php

namespace App\Models;

class User
{
    public function __construct(
        private int $id,
        private string $surname,
        private string $mail,
        private string $firstName,
        private string $password
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function getMail(): string
    {
        return $this->mail;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->surname;
    }

    // ⚠️ Attention : ne JAMAIS créer de getPassword() qui expose le hash publiquement
    // Vous en aurez besoin uniquement en interne pour la vérification (password_verify)
    public function getPassword(): string
    {
        return $this->password;
    }
}