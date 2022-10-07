<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ShoppingList;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id'
    ];

    /**
     * The list that an item appears in.
     */
    // public function lists()
    // {
    //     return $this->belongsToMany(ShoppingList::class, 'list_item', 'item_id', 'list_id');
    // }
}
