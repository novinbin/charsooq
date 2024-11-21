<?php

namespace App\Http\Resources;

use App\Enums\PaymentType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $type = match ($this->type) {
            PaymentType::SingleInstallment => 'تک قسط',
            PaymentType::AllInstallments => 'همه ی اقساط',
            PaymentType::Factor => 'فاکتور',
        };
        return [
            'user' => $this->user,
            'type' => $type,
            'amount' => $this->amount,
            'status' => $this->status,
            'reference_id' => $this->reference_id,
            'updated_at' => $this->updated_at,
        ];
    }
}
