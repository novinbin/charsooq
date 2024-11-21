<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatSecond extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'cat_first_id',
    ];

    public function catFirst()
    {
        return $this->belongsTo(CatFirst::class, 'cat_first_id');
    }

    public function catThirds(): HasMany
    {
        return $this->hasMany(CatThird::class, 'cat_second_id');
    }

    public function delete()
    {
        foreach ($this->catThirds as $catThird) {
            $catThird->delete();
        }
        return parent::delete();
    }
}
