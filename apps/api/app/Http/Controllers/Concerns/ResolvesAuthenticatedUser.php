<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

trait ResolvesAuthenticatedUser
{
    protected function authenticatedUser(Request $request): User
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new AuthenticationException();
        }

        return $user;
    }
}
