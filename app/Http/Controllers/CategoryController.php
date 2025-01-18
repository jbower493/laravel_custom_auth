<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Repositories\AuthedUserRepositoryInterface;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $authedUserRepo;

    public function __construct(AuthedUserRepositoryInterface $authedUserRepo)
    {
        $this->authedUserRepo = $authedUserRepo;
    }

    public function index()
    {
        $loggedInUserId = $this->authedUserRepo->getUser()->id;

        $categories = Category::where('user_id', $loggedInUserId)->orderBy('name')->get()->toArray();

        return [
            'message' => 'Successfully retreived categories.',
            'data' => [
                'categories' => $categories
            ]
        ];
    }

    public function store(Request $request)
    {
        $loggedInUserId = $this->authedUserRepo->getUser()->id;

        $validatedCategory = $request->validate([
            'name' => ['required']
        ]);

        $category = Category::create([
            'name' => $validatedCategory['name'],
            'user_id' => $loggedInUserId
        ]);

        $category->save();

        return [
            'message' => 'Category successfully created.'
        ];
    }

    public function update(Request $request, Category $category)
    {
        $validatedRequest = $request->validate([
            'name' => ['required']
        ]);

        $category->name = $validatedRequest['name'];

        $category->save();

        return [
            'message' => 'Category successfully updated'
        ];
    }

    public function delete(Category $category)
    {
        // set all items with that category to have a category of null, before deleting the category
        Item::where('category_id', $category->id)->update(['category_id' => null]);

        $category->delete();

        return [
            'message' => 'Category successfully deleted.'
        ];
    }
}
