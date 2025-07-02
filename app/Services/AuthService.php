<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class AuthService
{
    /**
     * Register a new user.
     *
     * @param array $data
     * @return User
     * @throws Exception
     */
    public function register(array $data): User
    {
        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            Log::info('User registered successfully: ' . $user->email);

            return $user;
        } catch (Exception $e) {
            Log::error('Failed to register user: ' . $e->getMessage());
            throw new Exception('Error al registrar el usuario: ' . $e->getMessage());
        }
    }

    /**
     * Authenticate a user.
     *
     * @param array $credentials
     * @return bool
     * @throws Exception
     */
    public function login(array $credentials): bool
    {
        try {
            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                Log::info('User logged in successfully: ' . $user->email);
                return true;
            }

            Log::warning('Failed login attempt for email: ' . $credentials['email']);
            return false;
        } catch (Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            throw new Exception('Error al iniciar sesión: ' . $e->getMessage());
        }
    }

    /**
     * Log out the current user.
     *
     * @return void
     * @throws Exception
     */
    public function logout(): void
    {
        try {
            $user = Auth::user();
            Auth::logout();
            Log::info('User logged out successfully: ' . ($user->email ?? 'unknown'));
        } catch (Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            throw new Exception('Error al cerrar sesión: ' . $e->getMessage());
        }
    }
}
