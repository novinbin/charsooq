<?php

namespace App\Http\Resources;

use App\Enums\InstallmentStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DelayedFactorResource extends JsonResource
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

        $total_delay = 0;
        foreach ($this->installments()->where('status', '!=', InstallmentStatus::Paid)->where('delay_days', '>', 0)->get() as $i) {
            $total_delay += $i->delay_fine + $i->amount;
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
            'products' => $this->items,
            'date' => $this->date,
            'installment_count' => $this->Installments()->count(),
            'installment_amount' => $this->Installments->isNotEmpty() ? $this->Installments->first()->amount : 0,
            'has_delayed_installment' => $delay,
            'unpaid_delayed_installment_count' => $this->Installments->isNotEmpty() ?
                $this->installments()->where('status', '!=', InstallmentStatus::Paid)->where('delay_days', '>', 0)->count() : 0,
            'unpaid_delayed_installment_amount' => $total_delay,
            'paid_installments' => $this->Installments->isNotEmpty() ?
                $this->installments()->where('status', '=', InstallmentStatus::Paid)->count() : 0,
        ];
    }
}
