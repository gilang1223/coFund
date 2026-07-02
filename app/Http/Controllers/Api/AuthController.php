<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;

class AuthController extends ApiController
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register($request->validated());

        return $this->sendCreated('User registered successfully', [
            'user' => $result['user'],
            'token' => $result['token'],
        ]);
    }

    /**
     * Login user and create token.
     */
    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated());

        if (!$result) {
            return $this->sendValidationError('Email atau password salah.');
        }

        return $this->sendResponse('Login successful', [
            'user' => $result['user'],
            'token' => $result['token'],
        ]);
    }

    /**
     * Logout user (revoke current token).
     */
    public function logout()
    {
        $this->authService->logout(auth()->user());

        return $this->sendResponse('Logged out successfully');
    }

    /**
     * Get the authenticated user.
     */
    public function user()
    {
        $user = $this->authService->getUser(auth()->user());

        return $this->sendResponse('User retrieved successfully', $user);
    }
}
