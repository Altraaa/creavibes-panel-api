<?php

namespace App\Http\Controllers;

use App\Services\VolunteerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class VolunteerController extends Controller
{
    protected const SUCCESS_STATUS = 200;
    protected const CREATED_STATUS = 201;
    protected const NOT_FOUND_STATUS = 404;
    protected const VALIDATION_ERROR_STATUS = 422;
    protected const SERVER_ERROR_STATUS = 500;

    protected $volunteerService;

    /**
     * VolunteerController constructor.
     *
     * @param VolunteerService $volunteerService
     */
    public function __construct(VolunteerService $volunteerService)
    {
        $this->volunteerService = $volunteerService;
    }

    /**
     * Display a listing of volunteers.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $filters = $request->all();
            $result = $this->volunteerService->getAllVolunteers($filters);

            return response()->json($result, $result['success'] ? self::SUCCESS_STATUS : self::SERVER_ERROR_STATUS);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch volunteers',
                'errors' => ['server' => $e->getMessage()]
            ], self::SERVER_ERROR_STATUS);
        }
    }

    /**
     * Display the specified volunteer.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $result = $this->volunteerService->getVolunteerById($id);

            return response()->json($result, $result['success'] ? self::SUCCESS_STATUS : self::NOT_FOUND_STATUS);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch volunteer',
                'errors' => ['server' => $e->getMessage()]
            ], self::SERVER_ERROR_STATUS);
        }
    }

    /**
     * Store a newly created volunteer in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'age' => 'required|integer|min:1|max:120',
                'address' => 'required|string',
                'current_activity' => 'required|string|max:255',
                'university' => 'sometimes|string|max:255',
                'has_event_experience' => 'sometimes|boolean',
                'event_experience_details' => 'sometimes|string',
                'user_id' => 'sometimes|integer|exists:users,id',
            ]);

            $result = $this->volunteerService->createVolunteer($validatedData);

            return response()->json($result, $result['success'] ? self::CREATED_STATUS : self::SERVER_ERROR_STATUS);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], self::VALIDATION_ERROR_STATUS);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create volunteer',
                'errors' => ['server' => $e->getMessage()]
            ], self::SERVER_ERROR_STATUS);
        }
    }

    /**
     * Update the specified volunteer in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name' => 'sometimes|string|max:255',
                'age' => 'sometimes|integer|min:1|max:120',
                'address' => 'sometimes|string',
                'current_activity' => 'sometimes|string|max:255',
                'university' => 'sometimes|string|max:255',
                'has_event_experience' => 'sometimes|boolean',
                'event_experience_details' => 'sometimes|string',
                'user_id' => 'sometimes|integer|exists:users,id',
            ]);

            $result = $this->volunteerService->updateVolunteer($id, $validatedData);

            return response()->json($result, $result['success'] ? self::SUCCESS_STATUS : self::NOT_FOUND_STATUS);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], self::VALIDATION_ERROR_STATUS);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update volunteer',
                'errors' => ['server' => $e->getMessage()]
            ], self::SERVER_ERROR_STATUS);
        }
    }

    /**
     * Remove the specified volunteer from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $result = $this->volunteerService->deleteVolunteer($id);

            return response()->json($result, $result['success'] ? self::SUCCESS_STATUS : self::NOT_FOUND_STATUS);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete volunteer',
                'errors' => ['server' => $e->getMessage()]
            ], self::SERVER_ERROR_STATUS);
        }
    }
}
