<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactorItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'factor_id',
        'item_id',
        'dasht_info',
        'title',
        'code',
        'price',
        'count',
        'total',
    ];

    public function factor(): BelongsTo
    {
        return $this->belongsTo(Factor::class);
    }
}
