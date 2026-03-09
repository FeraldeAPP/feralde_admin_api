<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\MarketingAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MarketingController extends Controller
{
    // Marketing Assets

    public function assets(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Marketing assets retrieved successfully',
            'data'    => MarketingAsset::getAll($request->all()),
        ]);
    }

    public function storeAsset(Request $request): JsonResponse
    {
        $userId    = (string) $request->attributes->get('auth_user')['id'];
        $validated = MarketingAsset::validate($request->all());
        $asset     = MarketingAsset::create(array_merge($validated, ['uploaded_by' => $userId]));

        return response()->json(['success' => true, 'message' => 'Asset created successfully', 'data' => $asset], 201);
    }

    public function updateAsset(Request $request, int $id): JsonResponse
    {
        $asset = MarketingAsset::find($id);

        if (!$asset) {
            return response()->json(['success' => false, 'message' => 'Asset not found'], 404);
        }

        $validated = MarketingAsset::validate($request->all());
        $asset->update($validated);

        return response()->json(['success' => true, 'message' => 'Asset updated successfully', 'data' => $asset->fresh()]);
    }

    public function destroyAsset(int $id): JsonResponse
    {
        $asset = MarketingAsset::find($id);

        if (!$asset) {
            return response()->json(['success' => false, 'message' => 'Asset not found'], 404);
        }

        $asset->delete();

        return response()->json(['success' => true, 'message' => 'Asset deleted successfully', 'data' => null]);
    }

    // Announcements

    public function announcements(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Announcements retrieved successfully',
            'data'    => Announcement::getAll($request->all()),
        ]);
    }

    public function storeAnnouncement(Request $request): JsonResponse
    {
        $validated     = Announcement::validate($request->all());
        $announcement  = Announcement::create($validated);

        return response()->json(['success' => true, 'message' => 'Announcement created successfully', 'data' => $announcement], 201);
    }

    public function updateAnnouncement(Request $request, int $id): JsonResponse
    {
        $announcement = Announcement::find($id);

        if (!$announcement) {
            return response()->json(['success' => false, 'message' => 'Announcement not found'], 404);
        }

        $validated = Announcement::validate($request->all());
        $announcement->update($validated);

        return response()->json(['success' => true, 'message' => 'Announcement updated successfully', 'data' => $announcement->fresh()]);
    }

    public function publishAnnouncement(Request $request, int $id): JsonResponse
    {
        $announcement = Announcement::find($id);

        if (!$announcement) {
            return response()->json(['success' => false, 'message' => 'Announcement not found'], 404);
        }

        $userId = (string) $request->attributes->get('auth_user')['id'];
        $announcement->publish($userId);

        return response()->json(['success' => true, 'message' => 'Announcement published', 'data' => $announcement->fresh()]);
    }

    public function destroyAnnouncement(int $id): JsonResponse
    {
        $announcement = Announcement::find($id);

        if (!$announcement) {
            return response()->json(['success' => false, 'message' => 'Announcement not found'], 404);
        }

        $announcement->delete();

        return response()->json(['success' => true, 'message' => 'Announcement deleted successfully', 'data' => null]);
    }
}
