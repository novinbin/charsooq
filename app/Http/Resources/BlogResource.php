<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BlogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'],
            'author' => [
                'id' => $this['author']['id'],
                'name' => $this['author']['name'],
            ],
            'title' => $this['title'],
            'slug' => $this['slug'],
            'description' => $this['description'],
            'key_words' => $this['key_words'],
            'views' => $this['views'],
            'photo' => $this['photo'] ? Storage::disk('public')->url($this['photo']) : null,
            'body' => $this['body'],
            'created_at' => $this['created_at'],
            'updated_at' => $this['updated_at'],
        ];
    }
}
