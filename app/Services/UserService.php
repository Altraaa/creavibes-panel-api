<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    protected const DEFAULT_PER_PAGE = 15;
    protected const MAX_PER_PAGE = 100;
    protected const MIN_PER_PAGE = 1;
    protected const DEFAULT_PAGE = 1;
    protected const DEFAULT_SORT_BY = 'created_at';
    protected const DEFAULT_SORT_ORDER = 'desc';

    /**
     * Get all users with pagination and filtering.
     *
     * @param array $filters
     * @return array
     */
    public function getAllUsers(array $filters = []): array
    {
        try {
            $query = $this->buildUserQuery($filters);
            $users = $this->applyPagination($query, $filters);

            return $this->createSuccessResponse('Users fetched successfully', [
                'data' => $users,
                'meta' => $this->getPaginationMeta($users),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch users', [
                'filters' => $filters,
                'error' => $e->getMessage(),
            ]);

            return $this->createErrorResponse('Failed to fetch users', ['server' => $e->getMessage()]);
        }
    }

    /**
     * Get user by ID.
     *
     * @param int $id
     * @return array
     */
    public function getUserById(int $id): array
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return $this->createErrorResponse('User not found');
            }

            return $this->createSuccessResponse('User fetched successfully', ['data' => $user]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch user', [
                'user_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return $this->createErrorResponse('Failed to fetch user', ['server' => $e->getMessage()]);
        }
    }

    /**
     * Create a new user.
     *
     * @param array $data
     * @return array
     */
    public function createUser(array $data): array
    {
        try {
            $this->validateEmailUniqueness($data['email'], null);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'is_active' => $data['is_active'] ?? true,
            ]);

            return $this->createSuccessResponse('User created successfully', ['data' => $user]);
        } catch (\Exception $e) {
            Log::error('Failed to create user', [
                'email' => $data['email'],
                'error' => $e->getMessage(),
            ]);

            return $this->createErrorResponse('Failed to create user', ['server' => $e->getMessage()]);
        }
    }

    /**
     * Update user.
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function updateUser(int $id, array $data): array
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return $this->createErrorResponse('User not found');
            }

            if (isset($data['email']) && $data['email'] !== $user->email) {
                $this->validateEmailUniqueness($data['email'], $id);
            }

            $updateData = $this->prepareUpdateData($data);
            $user->update($updateData);

            return $this->createSuccessResponse('User updated successfully', ['data' => $user]);
        } catch (\Exception $e) {
            Log::error('Failed to update user', [
                'user_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return $this->createErrorResponse('Failed to update user', ['server' => $e->getMessage()]);
        }
    }

    /**
     * Delete user.
     *
     * @param int $id
     * @return array
     */
    public function deleteUser(int $id): array
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return $this->createErrorResponse('User not found');
            }

            $user->delete();

            return $this->createSuccessResponse('User deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to delete user', [
                'user_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return $this->createErrorResponse('Failed to delete user', ['server' => $e->getMessage()]);
        }
    }

    /**
     * Toggle user active status.
     *
     * @param int $id
     * @return array
     */
    public function toggleUserStatus(int $id): array
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return $this->createErrorResponse('User not found');
            }

            $user->update([
                'is_active' => !$user->is_active
            ]);

            return $this->createSuccessResponse('User status updated successfully', ['data' => $user]);
        } catch (\Exception $e) {
            Log::error('Failed to update user status', [
                'user_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return $this->createErrorResponse('Failed to update user status', ['server' => $e->getMessage()]);
        }
    }

    /**
     * Get user statistics.
     *
     * @return array
     */
    public function getUserStats(): array
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'inactive_users' => User::where('is_active', false)->count(),
            ];

            return $this->createSuccessResponse('User statistics fetched successfully', ['data' => $stats]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch user statistics', [
                'error' => $e->getMessage(),
            ]);

            return $this->createErrorResponse('Failed to fetch user statistics', ['server' => $e->getMessage()]);
        }
    }

    /**
     * Build user query with filters.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function buildUserQuery(array $filters): \Illuminate\Database\Eloquent\Builder
    {
        $query = User::query();

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        $sortBy = $filters['sort_by'] ?? self::DEFAULT_SORT_BY;
        $sortOrder = $filters['sort_order'] ?? self::DEFAULT_SORT_ORDER;
        $query->orderBy($sortBy, $sortOrder);

        return $query;
    }

    /**
     * Apply pagination to query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return LengthAwarePaginator
     */
    protected function applyPagination(\Illuminate\Database\Eloquent\Builder $query, array $filters): LengthAwarePaginator
    {
        $perPage = min(
            max((int)($filters['per_page'] ?? self::DEFAULT_PER_PAGE), self::MIN_PER_PAGE),
            self::MAX_PER_PAGE
        );
        $page = max((int)($filters['page'] ?? self::DEFAULT_PAGE), self::DEFAULT_PAGE);

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get pagination metadata.
     *
     * @param LengthAwarePaginator $paginator
     * @return array
     */
    protected function getPaginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }

    /**
     * Validate email uniqueness.
     *
     * @param string $email
     * @param int|null $excludeId
     * @return void
     */
    protected function validateEmailUniqueness(string $email, ?int $excludeId = null): void
    {
        $query = User::where('email', $email);
        
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw new \Exception('Email already exists');
        }
    }

    /**
     * Prepare user update data.
     *
     * @param array $data
     * @return array
     */
    protected function prepareUpdateData(array $data): array
    {
        return array_filter([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'password' => isset($data['password']) ? Hash::make($data['password']) : null,
            'is_active' => $data['is_active'] ?? null,
        ]);
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
            $response = array_merge($response, $data);
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