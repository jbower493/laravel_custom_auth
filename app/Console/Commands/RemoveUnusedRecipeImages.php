<?php

namespace App\Console\Commands;

use App\Models\Recipe;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RemoveUnusedRecipeImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recipe:remove-unused-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Go through all stored recipe images and delete any that are not attached to a recipe.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        echo 'Running cron: RemoveUnusedRecipeImages' . "\n"; 

        // Get all files in the "recipe-images" folder (so we don't delete other images too)
        $files = Storage::allFiles('recipe-images');

        foreach ($files as $file) {
            $parentRecipe = Recipe::where('image_url', $file)->first();

            // If the image doesn't belong to any recipe, delete it
            if (!$parentRecipe) {
                Storage::delete($file);
                echo "Deleted unused image: " . $file . "\n";
            }
        }


        return Command::SUCCESS;
    }
}
