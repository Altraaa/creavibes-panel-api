<?php

namespace App\Services;

use App\Models\Volunteer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class VolunteerService
{
    protected const DEFAULT_PER_PAGE = 15;
    protected const MAX_PER_PAGE = 100;
    protected const MIN_PER_PAGE = 1;
    protected const DEFAULT_PAGE = 1;
    protected const DEFAULT_SORT_BY = 'created_at';
    protected const DEFAULT_SORT_ORDER = 'desc';

    /**
     * Get all volunteers with pagination and filtering.
     *
     * @param array $filters
     * @return array
     */
    public function getAllVolunteers(array $filters = []): array
    {
        try {
            $query = $this->buildVolunteerQuery($filters);
            $volunteers = $this->applyPagination($query, $filters);

            return $this->createSuccessResponse('Volunteers fetched successfully', [
                'data' => $volunteers,
                'meta' => $this->getPaginationMeta($volunteers),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch volunteers', [
                'filters' => $filters,
                'error' => $e->getMessage(),
            ]);

            return $this->createErrorResponse('Failed to fetch volunteers', ['server' => $e->getMessage()]);
        }
    }

    /**
     * Get volunteer by ID.
     *
     * @param int $id
     * @return array
     */
    public function getVolunteerById(int $id): array
    {
        try {
            $volunteer = Volunteer::find($id);

            if (!$volunteer) {
                return $this->createErrorResponse('Volunteer not found');
            }

            return $this->createSuccessResponse('Volunteer fetched successfully', ['data' => $volunteer]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch volunteer', [
                'volunteer_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return $this->createErrorResponse('Failed to fetch volunteer', ['server' => $e->getMessage()]);
        }
    }

    /**
     * Create a new volunteer.
     *
     * @param array $data
     * @return array
     */
    public function createVolunteer(array $data): array
    {
        try {
            $volunteer = Volunteer::create($data);

            return $this->createSuccessResponse('Volunteer created successfully', ['data' => $volunteer]);
        } catch (\Exception $e) {
            Log::error('Failed to create volunteer', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);

            return $this->createErrorResponse('Failed to create volunteer', ['server' => $e->getMessage()]);
        }
    }

    /**
     * Update volunteer.
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function updateVolunteer(int $id, array $data): array
    {
        try {
            $volunteer = Volunteer::find($id);

            if (!$volunteer) {
                return $this->createErrorResponse('Volunteer not found');
            }

            $volunteer->update($data);

            return $this->createSuccessResponse('Volunteer updated successfully', ['data' => $volunteer]);
        } catch (\Exception $e) {
            Log::error('Failed to update volunteer', [
                'volunteer_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return $this->createErrorResponse('Failed to update volunteer', ['server' => $e->getMessage()]);
        }
    }

    /**
     * Delete volunteer.
     *
     * @param int $id
     * @return array
     */
    public function deleteVolunteer(int $id): array
    {
        try {
            $volunteer = Volunteer::find($id);

            if (!$volunteer) {
                return $this->createErrorResponse('Volunteer not found');
            }

            $volunteer->delete();

            return $this->createSuccessResponse('Volunteer deleted successfully');
        } catch (\Exception $e) {
            Log::error('Failed to delete volunteer', [
                'volunteer_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return $this->createErrorResponse('Failed to delete volunteer', ['server' => $e->getMessage()]);
        }
    }

    /**
     * Build volunteer query with filters.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function buildVolunteerQuery(array $filters): \Illuminate\Database\Eloquent\Builder
    {
        $query = Volunteer::query();

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('current_activity', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('university', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('address', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['has_event_experience'])) {
            $query->where('has_event_experience', $filters['has_event_experience']);
        }

        if (isset($filters['university'])) {
            $query->where('university', $filters['university']);
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