<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportedRecipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'data'
    ];

    protected $hidden = ['created_at', 'updated_at', 'user_id'];
}
