<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AuthService
{
    protected const TOKEN_NAME = 'auth_token';
    protected const TOKEN_TYPE = 'Bearer';
    protected const TEMP_PASSWORD_LENGTH = 12;

    /**
     * Handle user login.
     *
     * @param array $credentials
     * @return array
     */
    public function login(array $credentials): array
    {
        try {
            $user = $this->findUserByEmail($credentials['email']);

            if (!$user) {
                return $this->createErrorResponse('Invalid credentials');
            }

            if (!$this->validateCredentials($user, $credentials['password'])) {
                return $this->createErrorResponse('Invalid credentials');
            }

            if (!$this->isUserActive($user)) {
                return $this->createErrorResponse('Account is disabled');
            }

            $token = $this->generateToken($user);

            return $this->createSuccessResponse('Login successful', [
                'user' => $user,
                'token' => $token,
                'token_type' => self::TOKEN_TYPE,
            ]);
        } catch (\Exception $e) {
            Log::error('Login failed', [
                'email' => $credentials['email'],
                'error' => $e->getMessage(),
            ]);

            return $this->createErrorResponse('Login failed', ['server' => $e->getMessage()]);
        }
    }

    /**
     * Handle user logout.
     *
     * @param User $user
     * @return array
     */
    public function logout(User $user): array
    {
        try {
            $this->revokeAllTokens($user);

            return $this->createSuccessResponse('Logout successful');
        } catch (\Exception $e) {
            Log::error('Logout failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return $this->createErrorResponse('Logout failed', ['server' => $e->getMessage()]);
        }
    }

    /**
     * Refresh user token.
     *
     * @param User $user
     * @return array
     */
    public function refreshToken(User $user): array
    {
        try {
            $this->revokeAllTokens($user);
            $token = $this->generateToken($user);

            return $this->createSuccessResponse('Token refreshed', [
                'token' => $token,
                'token_type' => self::TOKEN_TYPE,
            ]);
        } catch (\Exception $e) {
            Log::error('Token refresh failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return $this->createErrorResponse('Token refresh failed', ['server' => $e->getMessage()]);
        }
    }

    /**
     * Change user password.
     *
     * @param User $user
     * @param array $data
     * @return array
     */
    public function changePassword(User $user, array $data): array
    {
        try {
            if (!$this->validateCurrentPassword($user, $data['current_password'])) {
                return $this->createErrorResponse('Current password is incorrect');
            }

            $this->updateUserPassword($user, $data['new_password']);

            return $this->createSuccessResponse('Password changed successfully');
        } catch (\Exception $e) {
            Log::error('Password change failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return $this->createErrorResponse('Password change failed', ['server' => $e->getMessage()]);
        }
    }

    /**
     * Reset user password.
     *
     * @param string $email
     * @return array
     */
    public function resetPassword(string $email): array
    {
        try {
            $user = $this->findUserByEmail($email);

            if (!$user) {
                return $this->createErrorResponse('User not found');
            }

            $tempPassword = $this->generateTemporaryPassword();
            $this->updateUserPassword($user, $tempPassword);

            // In a real application, you would send an email with the temporary password
            // Here we'll just return it for demonstration purposes
            return $this->createSuccessResponse('Password reset successful', [
                'temporary_password' => $tempPassword,
            ]);
        } catch (\Exception $e) {
            Log::error('Password reset failed', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return $this->createErrorResponse('Password reset failed', ['server' => $e->getMessage()]);
        }
    }

    /**
     * Find user by email.
     *
     * @param string $email
     * @return User|null
     */
    protected function findUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Validate user credentials.
     *
     * @param User $user
     * @param string $password
     * @return bool
     */
    protected function validateCredentials(User $user, string $password): bool
    {
        return Hash::check($password, $user->password);
    }

    /**
     * Check if user is active.
     *
     * @param User $user
     * @return bool
     */
    protected function isUserActive(User $user): bool
    {
        return !isset($user->is_active) || $user->is_active;
    }

    /**
     * Generate authentication token.
     *
     * @param User $user
     * @return string
     */
    protected function generateToken(User $user): string
    {
        return $user->createToken(self::TOKEN_NAME)->plainTextToken;
    }

    /**
     * Revoke all user tokens.
     *
     * @param User $user
     * @return void
     */
    protected function revokeAllTokens(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Validate current password.
     *
     * @param User $user
     * @param string $password
     * @return bool
     */
    protected function validateCurrentPassword(User $user, string $password): bool
    {
        return Hash::check($password, $user->password);
    }

    /**
     * Update user password.
     *
     * @param User $user
     * @param string $password
     * @return void
     */
    protected function updateUserPassword(User $user, string $password): void
    {
        $user->update([
            'password' => Hash::make($password),
        ]);
    }

    /**
     * Generate temporary password.
     *
     * @return string
     */
    protected function generateTemporaryPassword(): string
    {
        return Str::random(self::TEMP_PASSWORD_LENGTH);
    }

    /**
     * Create success response.
     *
     * @param string $message
     * @param array|null $data
     * @return array
     */
    protected function createSuccessResponse(string $message, ?array $data = null): array
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return $response;
    }

    /**
     * Create error response.
     *
     * @param string $message
     * @param array|null $errors
     * @return array
     */
    protected function createErrorResponse(string $message, ?array $errors = null): array
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return $response;
    }
}