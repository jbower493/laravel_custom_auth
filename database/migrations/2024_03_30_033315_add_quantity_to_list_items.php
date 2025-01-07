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
        Schema::table('list_item', function (Blueprint $table) {
            $table->float('quantity')->nullable()->default(1);
            $table->foreignId('quantity_unit_id')->nullable()->constrained('quantity_units');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('list_item', function (Blueprint $table) {
            $table->dropColumn('quantity');
            $table->dropColumn('quantity_unit_id');
        });
    }
};
