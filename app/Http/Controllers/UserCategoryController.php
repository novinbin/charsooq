<?php

namespace App\Http\Controllers;

use App\Models\UserCategory;
use Illuminate\Http\Request;

class UserCategoryController extends Controller
{
    public function getAll(Request $request)
    {
        return UserCategory::latest()->paginate($request->query('per_page', 10));
    }

    public function read(UserCategory $category)
    {
        return $category;
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $category = UserCategory::create([
            'name' => $request->get('name'),
        ]);

        return response($category, 201);
    }

    public function update(Request $request, UserCategory $category)
    {
        $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $category->update([
            'name' => $request->get('name', $category->name),
        ]);

        return $category;
    }

    public function delete(UserCategory $category)
    {
        $category->delete();
        return response()->noContent();
    }
}
