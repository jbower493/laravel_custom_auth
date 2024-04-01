<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('quantity_units')->insert([
            // Mass
            ["name" => "grams", "symbol" => 'g'],
            ["name" => "pounds", "symbol" => 'lbs'],
            ["name" => "ounces", "symbol" => 'oz'],

            // Volume
            ["name" => "cups", "symbol" => 'cups'],
            ["name" => "millilitres", "symbol" => 'mL'],
            ["name" => "litres", "symbol" => 'L'],
            ["name" => "fluid ounces", "symbol" => 'fl.oz'],
            ["name" => "teaspoon", "symbol" => 'tsp'],
            ["name" => "tablespoon", "symbol" => 'tbsp'],

        ]);
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
