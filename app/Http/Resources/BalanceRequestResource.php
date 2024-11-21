<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BalanceRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee' => $this->employee,
            'user' => $this->user,
            'amount' => $this->amount,
            'description' => $this->description,
            'status' => $this->status,
            'check_photo' => Storage::disk('public')->url($this->check_photo),
        ];
    }
}
