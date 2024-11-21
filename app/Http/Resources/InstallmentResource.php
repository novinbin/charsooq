<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstallmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->calculateDelay();
        return [
            'id' => $this->id,
            'factor' => $this->factor,
            'amount' => $this->amount,
            'final_amount' => $this->amount + ($this->amount * ($this->profit_rate / 100)) + $this->delay_fine,
            'due_date' => $this->due_date,
            'status' => $this->status,
            'profit_rate' => $this->profit_rate,
            'delay_days' => $this->delay_days,
            'delay_fine' => $this->delay_fine,
            'total_price' => $this->amount
        ];
    }
}
