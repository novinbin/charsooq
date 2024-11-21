<?php

namespace App\Models;

use App\Traits\Payable;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Factor extends Model
{
    use HasFactory, Payable;

    protected $fillable = [
        'user_id',
        'description',
        'total_price',
        'products',
        'final_price',
        'discount',
        'status',
        'installment_profit_rate',
        'dasht_info',
        'remaining',
        'date',
        'is_delayed',
    ];

    protected function casts(): array
    {
        return [
            'products' => AsCollection::class,
            'dasht_info' => AsCollection::class,
            'is_delayed' => 'boolean',
        ];
    }

    public function setPaid()
    {
        $this->status = 'paid';
        $this->save();
    }

    public function setFailed()
    {
        $this->status = 'failed';
        $this->save();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function Installments(): HasMany
    {
        return $this->hasMany(Installment::class, 'factor_id');
    }

    public function delay(): HasOne
    {
        return $this->hasOne(Delay::class, 'factor_id');
    }

    public function hasInstallments(): bool
    {
        return $this->installments()->get()->isNotEmpty();
    }

    public function items(): HasMany
    {
        return $this->hasMany(FactorItem::class, 'factor_id');
    }
}
