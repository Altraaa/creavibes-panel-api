<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    protected const SUCCESS_STATUS = 200;
    protected const CREATED_STATUS = 201;
    protected const NOT_FOUND_STATUS = 404;
    protected const VALIDATION_ERROR_STATUS = 422;
    protected const SERVER_ERROR_STATUS = 500;

    protected $userService;

    /**
     * UserController constructor.
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->all();
            $result = $this->userService->getAllUsers($filters);

            return response()->json($result, $result['success'] ? self::SUCCESS_STATUS : self::SERVER_ERROR_STATUS);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users',
                'errors' => ['server' => $e->getMessage()]
            ], self::SERVER_ERROR_STATUS);
        }
    }

    /**
     * Display the specified user.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $result = $this->userService->getUserById($id);

            return response()->json($result, $result['success'] ? self::SUCCESS_STATUS : self::NOT_FOUND_STATUS);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user',
                'errors' => ['server' => $e->getMessage()]
            ], self::SERVER_ERROR_STATUS);
        }
    }

    /**
     * Store a newly created user in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|string|max:255|unique:users,email',
                'password' => 'required|string|min:8',
                'is_active' => 'sometimes|boolean',
            ]);

            $result = $this->userService->createUser($validatedData);

            return response()->json($result, $result['success'] ? self::CREATED_STATUS : self::SERVER_ERROR_STATUS);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], self::VALIDATION_ERROR_STATUS);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'errors' => ['server' => $e->getMessage()]
            ], self::SERVER_ERROR_STATUS);
        }
    }

    /**
     * Update the specified user in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|string|max:255|unique:users,email,' . $id,
                'password' => 'sometimes|string|min:8',
                'is_active' => 'sometimes|boolean',
            ]);

            $result = $this->userService->updateUser($id, $validatedData);

            return response()->json($result, $result['success'] ? self::SUCCESS_STATUS : self::NOT_FOUND_STATUS);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], self::VALIDATION_ERROR_STATUS);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'errors' => ['server' => $e->getMessage()]
            ], self::SERVER_ERROR_STATUS);
        }
    }

    /**
     * Remove the specified user from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->userService->deleteUser($id);

            return response()->json($result, $result['success'] ? self::SUCCESS_STATUS : self::NOT_FOUND_STATUS);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'errors' => ['server' => $e->getMessage()]
            ], self::SERVER_ERROR_STATUS);
        }
    }
}