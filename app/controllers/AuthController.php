<?php

namespace App\Controllers;

use App\Repositeries\UserRepositery;

class AuthController {
    private UserRepositery $userRepo;

    public function __construct(UserRepositery $userRepo) {
        $this->userRepo = $userRepo;
    }

    public function login(): void {
        session_start();

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = $this->userRepo->findByEmail($email);

        if ($user && password_verify($password, $user->getPassword())) {
            $_SESSION['user_id'] = $user->getId();
            header('Location: /dashboard');
            exit;
        }

        // Erreur de connexion
        header('Location: /login?error=1');
        exit;
    }
}