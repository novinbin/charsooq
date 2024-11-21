<?php

namespace App\Models;

use App\Enums\BalanceRequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BalanceIncrease extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'user_id',
        'amount',
        'description',
        'status',
        'check_photo'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => BalanceRequestStatus::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function delete()
    {
        Storage::disk('public')->delete($this->check_photo ?? '');
        return parent::delete();
    }
}
