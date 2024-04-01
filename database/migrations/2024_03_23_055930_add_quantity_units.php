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
        Schema::create('quantity_units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('symbol');
            $table->timestamps();
        });

        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('default_quantity_unit_id')->nullable()->constrained('quantity_units');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(('quantity_units'));

        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('default_quantity_unit_id');
        });
    }
};
