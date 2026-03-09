<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DistributorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class DistributorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Distributors retrieved successfully',
            'data'    => DistributorProfile::getAll($request->all()),
        ]);
    }

    public function pending(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Pending applications retrieved successfully',
            'data'    => DistributorProfile::getPending($request->all()),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $distributor = DistributorProfile::with(['wallet', 'resellers', 'rankHistory', 'commissions'])->find($id);

        if (!$distributor) {
            return response()->json(['success' => false, 'message' => 'Distributor not found'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Distributor retrieved successfully', 'data' => $distributor]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated   = DistributorProfile::validate($request->all());
        $distributor = DistributorProfile::create($validated);

        return response()->json(['success' => true, 'message' => 'Distributor created successfully', 'data' => $distributor], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $distributor = DistributorProfile::find($id);

        if (!$distributor) {
            return response()->json(['success' => false, 'message' => 'Distributor not found'], 404);
        }

        $validated = DistributorProfile::validate($request->all(), $id);
        $distributor->update($validated);

        return response()->json(['success' => true, 'message' => 'Distributor updated successfully', 'data' => $distributor->fresh()]);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $distributor = DistributorProfile::find($id);

        if (!$distributor) {
            return response()->json(['success' => false, 'message' => 'Distributor not found'], 404);
        }

        $userId = (string) $request->attributes->get('auth_user')['id'];
        $distributor->approve($userId);

        return response()->json(['success' => true, 'message' => 'Distributor approved', 'data' => $distributor->fresh()]);
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $distributor = DistributorProfile::find($id);

        if (!$distributor) {
            return response()->json(['success' => false, 'message' => 'Distributor not found'], 404);
        }

        $userId = (string) $request->attributes->get('auth_user')['id'];
        $distributor->reject($userId, $request->reason);

        return response()->json(['success' => true, 'message' => 'Distributor application rejected', 'data' => $distributor->fresh()]);
    }

    public function suspend(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $distributor = DistributorProfile::find($id);

        if (!$distributor) {
            return response()->json(['success' => false, 'message' => 'Distributor not found'], 404);
        }

        $userId = (string) $request->attributes->get('auth_user')['id'];
        $distributor->suspend($userId, $request->reason);

        return response()->json(['success' => true, 'message' => 'Distributor suspended', 'data' => $distributor->fresh()]);
    }

    public function unsuspend(int $id): JsonResponse
    {
        $distributor = DistributorProfile::find($id);

        if (!$distributor) {
            return response()->json(['success' => false, 'message' => 'Distributor not found'], 404);
        }

        $distributor->unsuspend();

        return response()->json(['success' => true, 'message' => 'Distributor unsuspended', 'data' => $distributor->fresh()]);
    }

    public function updateRank(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rank'   => 'required|string|in:STARTER,BRONZE,SILVER,GOLD,PLATINUM,DIAMOND',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $distributor = DistributorProfile::find($id);

        if (!$distributor) {
            return response()->json(['success' => false, 'message' => 'Distributor not found'], 404);
        }

        $userId = (string) $request->attributes->get('auth_user')['id'];
        $distributor->updateRank($request->rank, $userId, $request->reason);

        return response()->json(['success' => true, 'message' => 'Distributor rank updated', 'data' => $distributor->fresh()]);
    }

    /**
     * Assign a Philippine city to this distributor.
     * Only one distributor may hold a city at a time.
     */
    public function assignCity(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'city' => 'required|string|max:150',
        ], [
            'city.required' => 'City is required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $distributor = DistributorProfile::find($id);

        if (!$distributor) {
            return response()->json(['success' => false, 'message' => 'Distributor not found'], 404);
        }

        $distributor->assignCity($request->city);

        return response()->json([
            'success' => true,
            'message' => "City '{$request->city}' assigned to distributor {$distributor->distributor_code}",
            'data'    => $distributor->fresh(),
        ]);
    }

    /**
     * Remove this distributor's city assignment.
     * Resellers previously in that city revert to direct ordering.
     */
    public function unassignCity(int $id): JsonResponse
    {
        $distributor = DistributorProfile::find($id);

        if (!$distributor) {
            return response()->json(['success' => false, 'message' => 'Distributor not found'], 404);
        }

        $previousCity = $distributor->assigned_city;
        $distributor->unassignCity();

        return response()->json([
            'success' => true,
            'message' => $previousCity
                ? "City '{$previousCity}' unassigned from distributor {$distributor->distributor_code}"
                : 'Distributor had no city assignment',
            'data' => $distributor->fresh(),
        ]);
    }

    /**
     * List all resellers in this distributor's network:
     * - directly invited (any city)
     * - in the distributor's assigned city
     */
    public function networkResellers(int $id): JsonResponse
    {
        $distributor = DistributorProfile::find($id);

        if (!$distributor) {
            return response()->json(['success' => false, 'message' => 'Distributor not found'], 404);
        }

        $resellers = $distributor->networkResellers();

        return response()->json([
            'success' => true,
            'message' => 'Network resellers retrieved successfully',
            'data'    => [
                'distributor_code' => $distributor->distributor_code,
                'assigned_city'    => $distributor->assigned_city,
                'total'            => $resellers->count(),
                'resellers'        => $resellers,
            ],
        ]);
    }

    /**
     * Find the distributor assigned to a specific city (if any).
     */
    public function cityDistributor(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'city' => 'required|string|max:150',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $distributor = DistributorProfile::getForCity($request->city);

        if (!$distributor) {
            return response()->json([
                'success' => true,
                'message' => "No distributor assigned to city '{$request->city}'. Resellers in this city order directly.",
                'data'    => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'City distributor found',
            'data'    => $distributor->load(['wallet']),
        ]);
    }
}
