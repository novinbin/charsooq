<?php

namespace App\Http\Controllers;

use App\Models\CatSecond;
use App\Models\CatThird;
use Illuminate\Http\Request;

class CatThirdController extends Controller
{
    public function getAll(CatSecond $catSecond)
    {
        return $catSecond->catThirds()->latest()->get();
    }

    public function read(CatThird $category)
    {
        return $category;
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cat_second_id' => ['required', 'integer', 'exists:cat_seconds,id'],
        ]);
        $catSecond = CatSecond::find($request->get('cat_second_id'));

        $catThird = CatThird::create([
            'name' => $request->get('name'),
            'cat_second_id' => $request->get('cat_second_id'),
            'cat_first_id' => $catSecond->catFirst->id,
        ]);

        return response($catThird, 201);
    }

    public function update(Request $request, CatThird $category)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $category->update($request->get('name'));

        return response($category, 200);
    }

    public function delete(CatThird $category)
    {
        $category->delete();
        return response()->noContent();
    }
}
