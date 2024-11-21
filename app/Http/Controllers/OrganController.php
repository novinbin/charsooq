<?php

namespace App\Http\Controllers;

use App\Models\Organ;
use Illuminate\Http\Request;

class OrganController extends Controller
{
    public function getAll(Request $request)
    {
        return Organ::latest()->paginate($request->query('per_page', 10));
    }

    public function read(Organ $organ)
    {
        return $organ;
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $organ = Organ::create([
            'name' => $request->get('name'),
        ]);

        return response($organ, 201);
    }

    public function update(Request $request, Organ $organ)
    {
        $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $organ->update([
            'name' => $request->get('name', $organ->name),
        ]);

        return $organ;
    }

    public function delete(Organ $organ)
    {
        $organ->delete();
        return response()->noContent();
    }
}
