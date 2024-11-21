<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatFirst extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function catSeconds(): HasMany
    {
        return $this->hasMany(CatSecond::class, 'cat_first_id');
    }

    public function catThirds(): HasMany
    {
        return $this->hasMany(CatThird::class, 'cat_first_id');
    }

    public function delete()
    {
        foreach ($this->catSeconds as $catSecond) {
            $catSecond->delete();
        }
        return parent::delete();
    }
}
