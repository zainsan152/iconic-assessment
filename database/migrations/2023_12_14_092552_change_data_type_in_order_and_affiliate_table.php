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
        // Decimal can 100% accurately represent any number within the precision of the decimal format, whereas Float, cannot accurately represent all numbers
        Schema::table('affiliates', function (Blueprint $table) {
            $table->decimal('commission_rate', 10, 2)->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->change();
            $table->decimal('commission_owed', 10, 2)->change()->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
};
