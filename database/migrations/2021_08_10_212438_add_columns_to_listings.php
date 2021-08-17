<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToListings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->integer('minimum_nights')->nullable()->after('checkout_time');
            $table->integer('maximum_nights')->nullable()->after('minimum_nights');
            $table->decimal('weekly_factor')->nullable()->after('maximum_nights')->default(1.0);
            $table->decimal('monthly_factor')->nullable()->after('weekly_factor')->default(1.0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('minimum_nights');
            $table->dropColumn('maximum_nights');
            $table->dropColumn('weekly_factor');
            $table->dropColumn('monthly_factor');
        });
    }
}
