<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrainingCompletion;
use App\Models\TrainingContent;
use App\Models\TrainingModule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class TrainingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Training modules retrieved successfully',
            'data'    => TrainingModule::getAll($request->all()),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $module = TrainingModule::with(['contents', 'completions'])->find($id);

        if (!$module) {
            return response()->json(['success' => false, 'message' => 'Module not found'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Module retrieved successfully', 'data' => $module]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = TrainingModule::validate($request->all());
        $module    = TrainingModule::createModule($validated);

        return response()->json(['success' => true, 'message' => 'Module created successfully', 'data' => $module], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $module = TrainingModule::find($id);

        if (!$module) {
            return response()->json(['success' => false, 'message' => 'Module not found'], 404);
        }

        $validated = TrainingModule::validate($request->all());
        $module->update($validated);

        return response()->json(['success' => true, 'message' => 'Module updated successfully', 'data' => $module->fresh()]);
    }

    public function destroy(int $id): JsonResponse
    {
        $module = TrainingModule::find($id);

        if (!$module) {
            return response()->json(['success' => false, 'message' => 'Module not found'], 404);
        }

        $module->delete();

        return response()->json(['success' => true, 'message' => 'Module deleted successfully', 'data' => null]);
    }

    public function indexContent(int $moduleId): JsonResponse
    {
        $module = TrainingModule::find($moduleId);

        if (!$module) {
            return response()->json(['success' => false, 'message' => 'Module not found'], 404);
        }

        $contents = TrainingContent::where('module_id', $moduleId)->orderBy('sort_order')->get();

        return response()->json(['success' => true, 'message' => 'Contents retrieved successfully', 'data' => $contents]);
    }

    public function storeContent(Request $request, int $moduleId): JsonResponse
    {
        $data              = $request->all();
        $data['module_id'] = $moduleId;
        $validated         = TrainingContent::validate($data);
        $content           = TrainingContent::create($validated);

        return response()->json(['success' => true, 'message' => 'Content created successfully', 'data' => $content], 201);
    }

    public function updateContent(Request $request, int $moduleId, int $contentId): JsonResponse
    {
        $content = TrainingContent::where('module_id', $moduleId)->find($contentId);

        if (!$content) {
            return response()->json(['success' => false, 'message' => 'Content not found'], 404);
        }

        $data              = $request->all();
        $data['module_id'] = $moduleId;
        $validated         = TrainingContent::validate($data);
        $content->update($validated);

        return response()->json(['success' => true, 'message' => 'Content updated successfully', 'data' => $content->fresh()]);
    }

    public function destroyContent(int $moduleId, int $contentId): JsonResponse
    {
        $content = TrainingContent::where('module_id', $moduleId)->find($contentId);

        if (!$content) {
            return response()->json(['success' => false, 'message' => 'Content not found'], 404);
        }

        $content->delete();

        return response()->json(['success' => true, 'message' => 'Content deleted successfully', 'data' => null]);
    }

    /**
     * List all training completions with optional filters.
     */
    public function completions(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Completions retrieved successfully',
            'data'    => TrainingCompletion::getAll($request->all()),
        ]);
    }

    /**
     * Record a training completion for a specific module and user.
     */
    public function recordCompletion(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id'   => 'required|string',
            'score'     => 'nullable|integer|min:0|max:100',
            'certified' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $module = TrainingModule::find($id);

        if (!$module) {
            return response()->json(['success' => false, 'message' => 'Module not found'], 404);
        }

        $completion = TrainingCompletion::record(
            $id,
            $request->user_id,
            $request->score !== null ? (int) $request->score : null,
            (bool) ($request->certified ?? false)
        );

        return response()->json([
            'success' => true,
            'message' => 'Completion recorded successfully',
            'data'    => $completion,
        ], 201);
    }
}
