<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DistributorProfile;
use App\Models\ResellerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Public registration endpoints -- no authentication required.
 *
 * These routes are exposed under /api/register and allow prospective resellers
 * to look up a distributor's public profile and submit a registration application
 * via the distributor's unique referral link.
 */
final class RegistrationController extends Controller
{
    /**
     * Return the public-facing profile of an approved, active distributor.
     * Used by the registration page to display context before submitting a form.
     */
    public function distributorProfile(string $referralCode): JsonResponse
    {
        $distributor = DistributorProfile::where('referral_code', $referralCode)->first();

        if (
            $distributor === null
            || $distributor->approved_at === null
            || $distributor->suspended_at !== null
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Distributor not found or not accepting registrations',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Distributor profile retrieved',
            'data'    => [
                'distributor_code' => $distributor->distributor_code,
                'referral_code'    => $distributor->referral_code,
                'assigned_city'    => $distributor->assigned_city,
                'rank'             => $distributor->rank,
            ],
        ]);
    }

    /**
     * Submit a reseller registration application under a specific distributor.
     *
     * The distributor is identified by their referral_code (from the shareable link).
     * Resellers may register from any city -- no geographic restriction applies.
     * The application is created in a pending state and must be approved by an admin.
     */
    public function registerReseller(Request $request, string $referralCode): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|max:200|unique:reseller_profiles,email',
            'phone'      => 'nullable|string|max:30',
            'city'       => 'nullable|string|max:150',
        ], [
            'first_name.required' => 'First name is required',
            'last_name.required'  => 'Last name is required',
            'email.required'      => 'Email address is required',
            'email.email'         => 'Please enter a valid email address',
            'email.unique'        => 'This email address is already registered',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $distributor = DistributorProfile::where('referral_code', $referralCode)->first();

        if (
            $distributor === null
            || $distributor->approved_at === null
            || $distributor->suspended_at !== null
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive distributor registration link',
            ], 404);
        }

        $reseller = ResellerProfile::registerViaDistributor($distributor, $validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Registration submitted successfully. Your application is pending review.',
            'data'    => [
                'reseller_code' => $reseller->reseller_code,
            ],
        ], 201);
    }
}
