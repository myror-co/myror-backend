<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyColumnBookings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('phone')->after('email');
            $table->timestamp('checkin')->after('phone')->nullable();
            $table->timestamp('checkout')->after('checkin')->nullable();
            $table->integer('guests')->after('checkout');
            $table->string('receipt_url')->nullable()->after('guests');
            $table->string('reference_id')->nullable()->change();
            $table->string('payment_id')->nullable()->change();
            $table->string('currency')->nullable()->change();
            $table->string('gross_amount')->nullable()->change();
            $table->string('net_amount')->nullable()->change();
            $table->string('payment_fee')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            //
        });
    }
}
