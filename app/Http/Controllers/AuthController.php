<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected const SUCCESS_STATUS = 200;
    protected const UNAUTHORIZED_STATUS = 401;
    protected const VALIDATION_ERROR_STATUS = 422;
    protected const CREATED_STATUS = 201;

    protected $authService;

    /**
     * AuthController constructor.
     *
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle user login request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'email' => 'required|email|string|max:255',
                'password' => 'required|string|min:8',
            ]);

            $result = $this->authService->login($validatedData);

            return $this->buildResponse($result, self::SUCCESS_STATUS);
        } catch (ValidationException $e) {
            return $this->buildErrorResponse(
                'Validation failed',
                $e->errors(),
                self::VALIDATION_ERROR_STATUS
            );
        }
    }

    /**
     * Handle user logout request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $result = $this->authService->logout($request->user());

            return $this->buildResponse($result, self::SUCCESS_STATUS);
        } catch (\Exception $e) {
            return $this->buildErrorResponse(
                'Logout failed',
                ['server' => $e->getMessage()],
                self::UNAUTHORIZED_STATUS
            );
        }
    }

    /**
     * Get authenticated user information.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        return $this->buildSuccessResponse($request->user());
    }

    /**
     * Refresh user authentication token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function refreshToken(Request $request): JsonResponse
    {
        try {
            $result = $this->authService->refreshToken($request->user());

            return $this->buildResponse($result, self::SUCCESS_STATUS);
        } catch (\Exception $e) {
            return $this->buildErrorResponse(
                'Token refresh failed',
                ['server' => $e->getMessage()],
                self::UNAUTHORIZED_STATUS
            );
        }
    }

    /**
     * Change user password.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'current_password' => 'required|string|min:8',
                'new_password' => 'required|string|min:8|confirmed',
                'new_password_confirmation' => 'required|string|min:8',
            ]);

            $result = $this->authService->changePassword($request->user(), $validatedData);

            return $this->buildResponse($result, self::SUCCESS_STATUS);
        } catch (ValidationException $e) {
            return $this->buildErrorResponse(
                'Validation failed',
                $e->errors(),
                self::VALIDATION_ERROR_STATUS
            );
        }
    }

    /**
     * Build standardized success response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $status
     * @return JsonResponse
     */
    protected function buildSuccessResponse($data, string $message = 'Success', int $status = self::SUCCESS_STATUS): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Build standardized error response.
     *
     * @param string $message
     * @param array|null $errors
     * @param int $status
     * @return JsonResponse
     */
    protected function buildErrorResponse(string $message, ?array $errors = null, int $status = self::UNAUTHORIZED_STATUS): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Build response from service result.
     *
     * @param array $result
     * @param int $status
     * @return JsonResponse
     */
    protected function buildResponse(array $result, int $status = self::SUCCESS_STATUS): JsonResponse
    {
        if (!$result['success']) {
            return $this->buildErrorResponse(
                $result['message'],
                $result['errors'] ?? null,
                $status
            );
        }

        return $this->buildSuccessResponse(
            $result['data'] ?? null,
            $result['message'] ?? 'Success',
            $status
        );
    }
}