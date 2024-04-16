<?php

namespace App\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Models\QuantityUnit;
use App\Models\Item;

class MenuRecipePivot extends Pivot
{
    protected $table = 'menu_recipe';

    protected $with = ['day'];

    protected $hidden = ['recipe_id', 'menu_id'];
}
