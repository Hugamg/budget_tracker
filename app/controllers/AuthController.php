<?php

namespace App\Controllers;

use App\Repositories\UserRepository;

class AuthController {
    private UserRepository $userRepo;

    public function __construct(UserRepository $userRepo) {
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

    public function register(): void {
        session_start();

        $surname = $_POST['surname'] ?? '';
        $mail = $_POST['mail'] ?? '';
        $firstName = $_POST['first_name'] ?? '';
        $password = $_POST['password'] ?? '';

        $this->userRepo->register($surname, $mail, $firstName, $password);

        if($this->userRepo->isExists($mail)) {
            header('Location: /register?error=1');
            exit;
        } else {
            header('Location: /login');
            exit;
        }
    }
    
}