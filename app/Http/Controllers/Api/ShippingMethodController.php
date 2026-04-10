<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShippingMethodController extends Controller
{
    public function index()
    {
        return response()->json(ShippingMethod::orderBy('id', 'desc')->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:shipping_methods,name',
            'description' => 'nullable|string',
            'base_fee' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $method = ShippingMethod::create($validator->validated());
        return response()->json($method, 201);
    }

    public function show($id)
    {
        $method = ShippingMethod::find($id);
        if (!$method) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return response()->json($method);
    }

    public function update(Request $request, $id)
    {
        $method = ShippingMethod::find($id);
        if (!$method) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|unique:shipping_methods,name,' . $id,
            'description' => 'nullable|string',
            'base_fee' => 'sometimes|required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $method->update($validator->validated());
        return response()->json($method);
    }

    public function destroy($id)
    {
        $method = ShippingMethod::find($id);
        if (!$method) {
            return response()->json(['message' => 'Not found'], 404);
        }
        
        $method->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
