<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LeaderboardEntry;
use App\Models\Notification;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SystemController extends Controller
{
    // System Settings

    public function settings(Request $request): JsonResponse
    {
        $group    = is_string($request->query('group')) ? $request->query('group') : null;
        $settings = SystemSetting::getAll($group);

        return response()->json(['success' => true, 'message' => 'Settings retrieved successfully', 'data' => $settings]);
    }

    public function updateSetting(Request $request, string $key): JsonResponse
    {
        $validated = SystemSetting::validate(array_merge($request->all(), ['key' => $key]));
        $userId    = (string) $request->attributes->get('auth_user')['id'];
        $setting   = SystemSetting::set($key, $validated['value'], $validated['group'] ?? null, $userId);

        return response()->json(['success' => true, 'message' => 'Setting updated successfully', 'data' => $setting]);
    }

    // Audit Logs

    public function auditLogs(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Audit logs retrieved successfully',
            'data'    => AuditLog::getAll($request->all()),
        ]);
    }

    // Leaderboard

    public function leaderboard(Request $request): JsonResponse
    {
        $period = is_string($request->query('period')) ? $request->query('period') : now()->format('Y-m');

        return response()->json([
            'success' => true,
            'message' => 'Leaderboard retrieved successfully',
            'data'    => ['period' => $period, 'entries' => LeaderboardEntry::getForPeriod($period)],
        ]);
    }

    public function upsertLeaderboardEntry(Request $request): JsonResponse
    {
        $validated = LeaderboardEntry::validate($request->all());
        $entry     = LeaderboardEntry::upsertEntry($validated);

        return response()->json(['success' => true, 'message' => 'Leaderboard entry updated', 'data' => $entry]);
    }

    // Notifications (admin broadcast)

    public function sendNotification(Request $request): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'user_id' => 'required|string',
            'type'    => 'required|string|in:ORDER,COMMISSION,WALLET,INVENTORY,ANNOUNCEMENT,TRAINING,SYSTEM',
            'title'   => 'required|string|max:255',
            'body'    => 'nullable|string',
            'data'    => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $notification = Notification::send(
            $request->user_id,
            $request->type,
            $request->title,
            $request->body,
            $request->data
        );

        return response()->json(['success' => true, 'message' => 'Notification sent', 'data' => $notification], 201);
    }

    public function notifications(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Notifications retrieved successfully',
            'data'    => Notification::getAll($request->all()),
        ]);
    }
}
