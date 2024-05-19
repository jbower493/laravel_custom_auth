<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Recipe;
use App\Models\User;

class RecipeShareRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'recipient_email',
        'recipe_id'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }
}
