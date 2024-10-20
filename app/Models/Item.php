<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\QuantityUnit;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'user_id',
        'default_quantity_unit_id',
        'image_url'
    ];

    // Eager load the item's category by default
    protected $with = ['category', 'defaultQuantityUnit'];

    // Omits the "category_id" from any collection of Items that is retrieved
    protected $hidden = ['category_id', 'default_quantity_unit_id', 'created_at', 'updated_at', 'user_id'];

    // Accessor for "image_url" property of the recipe
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn (string | null $value) => $value ? Storage::url($value) : null,
        );
    }

    // Returns the actual url stored in the DB (without the Minio url prepended, like what the "imageUrl" accessor returns). This is used for deleting the old image when it's been replaced. Access this as $item->short_image_url
    protected function getShortImageUrlAttribute() {
        return $this->attributes['image_url'];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function defaultQuantityUnit()
    {
        return $this->belongsTo(QuantityUnit::class);
    }

    public function removeFromAllLists()
    {
        DB::table('list_item')->where('item_id', $this->id)->delete();
    }

    public function removeFromAllRecipes()
    {
        DB::table('recipe_item')->where('item_id', $this->id)->delete();
    }

    public function assignToCategory($categoryId)
    {
        $category = Category::find($categoryId);

        if (!$category) {
            return [
                'success' => false,
                'error' => 'Category with that id doesn\'t exist.'
            ];
        }

        $this->category_id = $categoryId;
        $this->save();

        return [
            'success' => true
        ];
    }
}
