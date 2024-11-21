<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'expiration',
        'discount_rate',
        'max_discount',
        'code',
        'type',
        'amount',
    ];

    public function doseOwn(User $user): bool
    {
        if ($this->user_id === null) {
            return true;
        }

        return $user->id == $this->user_id;
    }

    static public function generateCode(): string
    {
        $code = strtoupper(Str::random(6));
        while(Discount::where('code', $code)->get()->isNotEmpty()) {
            $code = strtoupper(Str::random(5));
        }
        return $code;
    }
}
