<?php

namespace App\Models;

use App\Enums\InstallmentStatus;
use App\Traits\Payable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Installment extends Model
{
    use HasFactory, Payable;

    protected $fillable = [
        'user_id',
        'factor_id',
        'amount',
        'due_date',
        'status',
        'profit_rate',
        'delay_days',
        'delay_fine'
    ];

    public function setPaid()
    {
        $this->status = InstallmentStatus::Paid;
        $this->save();
        $this->factor->user->addBalance($this->amount);
    }

    public function setFailed()
    {

    }

    protected function casts(): array
    {
        return [
            'status' => InstallmentStatus::class,
        ];
    }

    public function factor(): BelongsTo
    {
        return $this->belongsTo(Factor::class);
    }

    public function calculateDelay(): array
    {
        if ($this->status == InstallmentStatus::Paid) {
            return [
                'delay_days' => $this->delay_days,
                'delay_fine' => $this->delay_fine
            ];
        }
        $delay = round(now()->diffInDays($this->due_date));
        if ($delay < 0) {
            $this->update([
                'status' => InstallmentStatus::Delayed,
                'delay_days' => abs($delay),
                'delay_fine' => round((abs($delay) * 0.00117) * $this->amount)
            ]);
        }
        return [
            'delay_days' => $this->delay_days,
            'delay_fine' => $this->delay_fine
        ];
    }

    public function hasDelay(): bool
    {
        if ($this->status == InstallmentStatus::Paid) {
            return false;
        }
        $delay = round(now()->diffInDays($this->due_date));
        if ($this->status == InstallmentStatus::Delayed) {
            return true;
        } elseif ($delay < 0) {
            $this->update([
                'status' => InstallmentStatus::Delayed
            ]);
            return true;
        }
        return false;
    }
}
