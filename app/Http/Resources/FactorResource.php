<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FactorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $delay = false;
        foreach ($this->installments ?? [] as $installment) {
            if ($delay = $installment->hasDelay()) {
                break;
            }
        }

        return [
            'id' => $this->id,
            'user' => $this->user,
            'description' => $this->description,
            'total_price' => $this->total_price,
            'final_price' => $this->final_price,
            'remaining' => $this->remaining,
            'discount' => $this->discount,
            'status' => $this->status,
            'installment_profit_rate' => $this->installment_profit_rate,
            'dasht_info' => $this->dasht_info,
            'installments' => $this->installments,
            'products' => $this->items,
            'date' => $this->date,
            'installment_count' => $this->Installments()->count(),
            'installment_amount' => $this->Installments->isNotEmpty() ? $this->Installments->first()->amount : 0,
            'has_delayed_installment' => $delay,
        ];
    }
}
