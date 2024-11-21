<?php

namespace App\Http\Controllers;

use App\Models\CatFirst;
use App\Models\CatSecond;
use Illuminate\Http\Request;

class CatSecondController extends Controller
{
    public function getAll(Request $request, CatFirst $catFirst)
    {
        return $catFirst->catSeconds()->latest()->get();
    }

    public function read(CatSecond $category)
    {
        return $category;
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cat_first_id' => ['required', 'exists:cat_firsts,id']
        ]);

        $cat = CatSecond::create([
            'name' => $request->get('name'),
            'cat_first_id' => $request->get('cat_first_id'),
        ]);

        return response($cat, 201);
    }

    public function update(Request $request, CatSecond $category)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $category->update([
            'name' => $request->get('name'),
        ]);

        return response($category, 200);
    }

    public function delete(CatSecond $category)
    {
        $category->delete();
        return response()->noContent();
    }
}
