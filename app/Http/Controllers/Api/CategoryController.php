<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::latest()->get();
        return response()->json($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $category = Category::create($request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:active,inactive',
        ]));
        return response()->json($category, 201);
    }

    public function show(Category $category): JsonResponse
    {
        $category->load('products');
        return response()->json($category);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $category->update($request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:active,inactive',
        ]));
        return response()->json($category);
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();
        return response()->json(['message' => 'Category deleted']);
    }
}
