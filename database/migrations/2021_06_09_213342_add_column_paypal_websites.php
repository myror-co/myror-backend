<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnPaypalWebsites extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->string('paypal_client_id')->nullable()->after('google_gtag_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn('paypal_client_id');
        });
    }
}
