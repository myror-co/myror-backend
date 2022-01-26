<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtraFeesTableListing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->boolean('additional_guest_fee')->default(0)->after('monthly_factor');
            $table->integer('additional_guest_price')->nullable()->after('additional_guest_fee');
            $table->integer('additional_guest_threshold')->nullable()->after('additional_guest_price');
            $table->boolean('cleaning_fee')->default(0)->after('additional_guest_threshold');
            $table->integer('cleaning_price')->nullable()->after('cleaning_fee');
            $table->boolean('security_deposit_fee')->default(0)->after('cleaning_price');
            $table->integer('security_deposit_price')->nullable()->after('security_deposit_fee');
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
            $table->dropColumn('additional_guest_fee');
            $table->dropColumn('additional_guest_price');
            $table->dropColumn('additional_guest_threshold');
            $table->dropColumn('cleaning_fee');
            $table->dropColumn('cleaning_price');
            $table->dropColumn('security_deposit_fee');
            $table->dropColumn('security_deposit_price');
        });
    }
}
