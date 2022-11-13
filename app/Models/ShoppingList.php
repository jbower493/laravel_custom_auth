<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

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
        return $this->belongsToMany(Item::class, 'list_item', 'list_id', 'item_id');
    }

    public function removeAllItems()
    {
        DB::table('list_item')->where('list_id', $this->id)->delete();
    }
}
