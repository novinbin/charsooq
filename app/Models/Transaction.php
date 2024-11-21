<?php

namespace App\Models;

use App\Enums\PaymentType;
use App\Enums\TransactionStatus;
use App\Traits\Payable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory, Payable;

    protected $fillable = [
        'user_id',
        'transaction_id',
        'type',
        'object_id',
        'amount',
        'status',
        'reference_id'
    ];

    protected function casts(): array
    {
        return [
            'type' => PaymentType::class,
            'status' => TransactionStatus::class,
        ];
    }

    public function setPaid()
    {
        $this->update([
            'status' => 'paid'
        ]);
    }

    public function setFailed()
    {
        $this->update([
            'status' => 'failed'
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function object()
    {
        return match ($this->type) {
            PaymentType::Factor,
            PaymentType::AllInstallments => Factor::find($this->object_id),
            PaymentType::SingleInstallment => Installment::find($this->object_id),
            PaymentType::Balance => User::find($this->object_id),
        };
    }
}
