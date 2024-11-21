<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatThird extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'cat_second_id',
        'cat_first_id',
    ];
}
