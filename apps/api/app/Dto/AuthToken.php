<?php

namespace App\Dto;

use App\Models\User;

final readonly class AuthToken
{
    public function __construct(
        public string $token,
        public User $user,
    ) {
    }

    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
        ];
    }
};
