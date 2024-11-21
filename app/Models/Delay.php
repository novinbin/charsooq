<?php

namespace App\Models;

use App\Enums\DelayStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delay extends Model
{
    use HasFactory;

    protected $fillable = [
        'factor_id',
        'user_id',
        'status',
        'days_count'
    ];

    protected function casts(): array
    {
        return [
            'status' => DelayStatus::class,
        ];
    }

    public function factor(): BelongsTo
    {
        return $this->belongsTo(Factor::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
