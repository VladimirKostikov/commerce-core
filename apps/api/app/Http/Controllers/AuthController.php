<?php

namespace App\Http\Controllers;

use App\Contracts\AuthServiceInterface;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthController extends Controller
{
    public function register(RegisterRequest $request, AuthServiceInterface $auth): JsonResponse
    {
        $result = $auth->register(
            $request->string('name')->toString(),
            $request->string('email')->toString(),
            $request->string('password')->toString(),
        );

        return response()->json($result->toArray(), 201);
    }

    public function login(LoginRequest $request, AuthServiceInterface $auth): JsonResponse
    {
        $result = $auth->login(
            $request->string('email')->toString(),
            $request->string('password')->toString(),
        );

        return response()->json($result->toArray());
    }

    public function logout(Request $request, AuthServiceInterface $auth): JsonResponse
    {
        $auth->logout($this->authenticatedUser($request));

        return response()->json(['ok' => true]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }
}
