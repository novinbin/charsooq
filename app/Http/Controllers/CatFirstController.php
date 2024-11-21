<?php

namespace App\Http\Controllers;

use App\Models\CatFirst;
use Illuminate\Http\Request;

class CatFirstController extends Controller
{
    public function getAllCategories()
    {
        $catFirst = CatFirst::all();
        $result = [];
        foreach ($catFirst as $cat) {
            foreach ($cat->catSeconds as $catSecond) {
                $result[$cat->name][$catSecond->name] = $catSecond->catThirds;
            }
        }
        return $result;
    }

    public function getAll(Request $request)
    {
        return CatFirst::latest()->get();
    }

    public function read(CatFirst $category)
    {
        return $category;
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $catFirst = CatFirst::create([
            'name' => $request->get('name'),
        ]);

        return response($catFirst, 201);
    }

    public function update(Request $request, CatFirst $category)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $category->update([
            'name' => $request->get('name')
        ]);

        return response($category, 200);
    }

    public function delete(CatFirst $category)
    {
        $category->delete();
        return response()->noContent();
    }
}
