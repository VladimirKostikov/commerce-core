<?php

namespace App\Contracts;

use App\Dto\AuthToken;
use App\Models\User;

interface AuthServiceInterface
{
    public function register(string $name, string $email, string $password): AuthToken;

    public function login(string $email, string $password): AuthToken;

    public function logout(User $user): void;
}
