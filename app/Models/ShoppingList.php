<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Item;

class ShoppingList extends Model
{
    use HasFactory;

    protected $table = 'lists';

    protected $fillable = [
        'name',
        'user_id'
    ];

    /**
     * The items that belong to a shopping list.
     */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'item_list', 'list_id', 'item_id');
    }
}
