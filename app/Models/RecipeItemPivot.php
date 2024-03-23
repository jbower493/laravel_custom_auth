<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\QuantityUnit;
use App\Models\Item;

// Eager loading extra data from pivot tables
// https://laracasts.com/discuss/channels/eloquent/eager-loading-pivot-tables
class RecipeItemPivot extends Model
{
    protected $table = 'recipe_item';

    // Eager load by default
    protected $with = ['item', 'quantityUnit'];

    protected $hidden = ['created_at', 'updated_at', 'quantity_unit_id', 'recipe_id', 'item_id'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function quantityUnit()
    {
        return $this->belongsTo(QuantityUnit::class);
    }
}
