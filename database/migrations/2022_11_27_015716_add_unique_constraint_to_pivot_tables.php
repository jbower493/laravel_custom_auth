<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// This migration deletes all duplicate entries in the list_item and recipe_item table, removes the "id" column from both, and sets the new primary key as a combination of the 2 foreign keys, to prevent duplicated being added in future.
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('list_item', function (Blueprint $table) {
            // First, remove all duplicate entries
            $allRows = DB::table('list_item')->get();

            foreach($allRows as $row) {
                $rowArr = get_object_vars($row);
                $rowListId = $rowArr['list_id'];
                $rowItemId = $rowArr['item_id'];
                
                // Query each row, and find duplicated
                $duplicates = DB::table('list_item')->where('list_id', $rowListId)->where('item_id', $rowItemId)->get();

                if (count($duplicates) > 1) {
                    foreach($duplicates as $index => $duplicate)
                    // Don't remove the first entry
                    if ($index != 0) {
                        $duplicateArr = get_object_vars($duplicate);
                        DB::table('list_item')->where('id', $duplicateArr['id'])->delete();
                    }
                }
            }


            // Make primary key a combination of list_id and item_id, so that only unique entries are allowed
            $table->dropColumn('id');
            $table->primary(['list_id', 'item_id']);
        });

        Schema::table('recipe_item', function (Blueprint $table) {
            // First, remove all duplicate entries
            $allRows = DB::table('recipe_item')->get();

            foreach($allRows as $row) {
                $rowArr = get_object_vars($row);
                $rowListId = $rowArr['recipe_id'];
                $rowItemId = $rowArr['item_id'];
                
                // Query each row, and find duplicated
                $duplicates = DB::table('recipe_item')->where('recipe_id', $rowListId)->where('item_id', $rowItemId)->get();

                if (count($duplicates) > 1) {
                    foreach($duplicates as $index => $duplicate)
                    // Don't remove the first entry
                    if ($index != 0) {
                        $duplicateArr = get_object_vars($duplicate);
                        DB::table('recipe_item')->where('id', $duplicateArr['id'])->delete();
                    }
                }
            }


            // Make primary key a combination of recipe_id and item_id, so that only unique entries are allowed
            $table->dropColumn('id');
            $table->primary(['recipe_id', 'item_id']);
        });
    }
};
