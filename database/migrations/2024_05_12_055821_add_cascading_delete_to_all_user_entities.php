<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // additional users table
        Schema::table('additional_users', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->dropForeign(['additional_user_id']);
            $table->foreign('additional_user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // categories table
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // items table
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->dropForeign(['default_quantity_unit_id']);
            $table->foreign('default_quantity_unit_id')->references('id')->on('quantity_units')->onDelete('cascade');

            $table->dropForeign(['category_id']);
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });

        // lists table
        Schema::table('lists', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // menus table
        Schema::table('menus', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // quantity_units table
        Schema::table('quantity_units', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // recipes table
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->dropForeign(['recipe_category_id']);
            $table->foreign('recipe_category_id')->references('id')->on('recipe_categories')->onDelete('cascade');
        });

        // recipe_categories table
        Schema::table('recipe_categories', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // list_items table
        Schema::table('list_item', function (Blueprint $table) {
            $table->dropForeign(['list_id']);
            $table->foreign('list_id')->references('id')->on('lists')->onDelete('cascade');

            $table->dropForeign(['item_id']);
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });

        // menu_recipe table
        Schema::table('menu_recipe', function (Blueprint $table) {
            $table->dropForeign(['menu_id']);
            $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade');

            $table->dropForeign(['recipe_id']);
            $table->foreign('recipe_id')->references('id')->on('recipes')->onDelete('cascade');
        });

        // recipe_item table
        Schema::table('recipe_item', function (Blueprint $table) {
            $table->dropForeign(['recipe_id']);
            $table->foreign('recipe_id')->references('id')->on('recipes')->onDelete('cascade');

            $table->dropForeign(['item_id']);
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
