<?php

namespace App\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Models\QuantityUnit;
use App\Models\Item;


// Eager loading extra data from pivot tables
// https://laracasts.com/discuss/channels/eloquent/eager-loading-pivot-tables
class ShoppingListItemPivot extends Pivot
{
    protected $table = 'list_item';

    // Eager load by default
    protected $with = ['item', 'quantityUnit'];

    protected $hidden = ['created_at', 'updated_at', 'quantity_unit_id', 'list_id', 'item_id'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function quantityUnit()
    {
        return $this->belongsTo(QuantityUnit::class, 'quantity_unit_id');
    }
}
