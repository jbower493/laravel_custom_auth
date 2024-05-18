<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecipeShareRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'recipient_email',
        'recipe_id'
    ];

    protected $hidden = ['created_at', 'updated_at'];
}
