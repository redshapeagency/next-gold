<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:view items');
    }

    public function index(Request $request)
    {
        $query = Category::query();

        // Search filter
        if ($request->filled('search')) {
            $query->where('name', 'ILIKE', '%' . $request->search . '%');
        }

        // Status filter
        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        $categories = $query->orderBy('name')->get();

        return response()->json([
            'data' => $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'active' => $category->active,
                    'items_count' => $category->items_count ?? 0,
                ];
            })
        ]);
    }

    public function show(Category $category)
    {
        return response()->json([
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'active' => $category->active,
                'items_count' => $category->items()->count(),
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
            ]
        ]);
    }
}
