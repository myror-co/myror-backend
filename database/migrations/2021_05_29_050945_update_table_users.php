<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTableUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('address_line1')->nullable()->after('avatar');
            $table->string('address_line2')->nullable()->after('address_line1');
            $table->string('address_city')->nullable()->after('address_line2');
            $table->string('address_state')->nullable()->after('address_city');
            $table->string('address_country')->nullable()->after('address_state');
            $table->string('address_postal_code')->nullable()->after('address_country');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('address_line1');
            $table->dropColumn('address_line2');
            $table->dropColumn('address_city');
            $table->dropColumn('address_state');
            $table->dropColumn('address_country');
            $table->dropColumn('address_postal_code');
        });
    }
}
