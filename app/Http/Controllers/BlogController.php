<?php

namespace App\Http\Controllers;

use App\Http\Resources\BlogListResource;
use App\Http\Resources\BlogResource;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    public function getAll(Request $request)
    {
        return BlogListResource::collection(Blog::orderBy('created_at', 'desc')->paginate($request->per_page ?? 10));
    }

    public function show(Request $request, Blog $blog)
    {
        $blog->views++;
        $blog->save();
        return new BlogResource($blog);
    }

    public function create(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'key_words' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'slug' => ['required', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
        ]);
        $input = [
            'author_id' => $request->user()->id,
            'title' => $request->get('title'),
            'description' => $request->get('description'),
            'key_words' => $request->get('key_words'),
            'body' => $request->get('body'),
            'slug' => $request->get('slug'),
        ];
        if ($request->hasFile('photo')) {
            $input['photo'] = $request->file('photo')->store('blogs', 'public');
        }
        $blog = Blog::create($input);

        return response(new BlogResource($blog), 201);
    }

    public function update(Request $request, Blog $blog)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'key_words' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'slug' => ['required', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
        ]);
        $input = $request->all();
        if ($request->hasFile('photo')) {
            Storage::disk('public')->delete($blog->photo);
            $input['photo'] = $request->file('photo')->store('blogs', 'public');
        }
        $blog->update($input);
        return response(new BlogResource($blog), 200);
    }

    public function destroy(Request $request, Blog $blog)
    {
        $blog->delete();
        return response(null, 204);
    }
}
