<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user->update(['name' => $request->name]);

        return $this->sendResponse('Profile updated successfully', $user->fresh());
    }

    /**
     * Top-up user balance (simulation).
     */
    public function topUp(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000|max:10000000',
        ]);

        $user = auth()->user();
        $amount = (float) $request->amount;

        $user->addBalance($amount);

        // Create top-up transaction
        Transaction::create([
            'user_id' => $user->id,
            'backing_id' => null,
            'type' => 'top_up',
            'amount' => $amount,
            'status' => 'success',
            'reference' => 'TOPUP-' . strtoupper(uniqid()),
        ]);

        return $this->sendResponse('Top-up berhasil!', [
            'user' => $user->fresh(),
            'amount' => $amount,
        ]);
    }

    /**
     * Delete user account (only for non-admin users with zero balance).
     */
    public function deleteAccount()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return $this->sendForbidden('Admin tidak dapat menghapus akun.');
        }

        if ($user->balance > 0) {
            return $this->sendError('Saldo Anda masih Rp ' . number_format($user->balance, 0, ',', '.') . '. Silakan tarik saldo terlebih dahulu sebelum menghapus akun.', 400);
        }

        // Check for pending withdrawals
        $hasPendingWithdrawals = $user->withdrawals()->where('status', 'pending')->exists();
        if ($hasPendingWithdrawals) {
            return $this->sendError('Anda masih memiliki penarikan yang sedang diproses. Tunggu hingga selesai.', 400);
        }

        // Delete user data
        $user->tokens()->delete();
        $user->delete();

        return $this->sendResponse('Akun berhasil dihapus. Terima kasih telah menggunakan CoFund.');
    }
}
