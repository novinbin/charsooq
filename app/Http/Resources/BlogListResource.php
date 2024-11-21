<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BlogListResource extends JsonResource
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
            'title' => $this['title'],
            'author' => [
                'id' => $this['author']['id'],
                'name' => $this['author']['name'],
            ],
            'description' => $this['description'],
            'key_words' => $this['key_words'],
            'slug' => $this['slug'],
            'views' => $this['views'],
            'photo' => $this['photo'] ? Storage::disk('public')->url($this['photo']) : null,
            'created_at' => $this['created_at'],
            'updated_at' => $this['updated_at'],
        ];
    }
}
