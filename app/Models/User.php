<?php

namespace App\Models;

use App\Enums\UserType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'gender',
        'phone',
        'balance',
        'role',
        'code',
        'address',
        'postal_code',
        'national_code',
        'state',
        'city',
        'email',
        'password',
        'organ_id',
        'user_category_id',
        'wallet',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserType::class,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role == UserType::Manager;
    }

    public function isEmployee(): bool
    {
        return $this->role == UserType::Employee;
    }

    public function isAuthor(): bool
    {
        return $this->role == UserType::Author;
    }

    public function isOrgan(): bool
    {
        return $this->role == UserType::Organ;
    }

    public function factors(): HasMany
    {
        return $this->hasMany(Factor::class, 'user_id');
    }

    public function userCategory(): BelongsTo
    {
        return $this->belongsTo(UserCategory::class, 'user_category_id');
    }

    public function BalanceInceases(): HasMany
    {
        return $this->hasMany(BalanceIncrease::class, 'user_id');
    }

    public function organ(): BelongsTo
    {
        return $this->belongsTo(Organ::class, 'organ_id');
    }

    static public function generateCode()
    {
        $code = rand(1000000, 9999999);
        while (User::where('code', $code)->get()->isNotEmpty()) {
            $code = rand(1000000, 9999999);
        }
        return $code;
    }

    public function subBalance($amount)
    {
        $this->balance -= $amount;
        $this->save();
    }

    public function addBalance($amount)
    {
        $this->balance += $amount;
        $this->save();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }
}
