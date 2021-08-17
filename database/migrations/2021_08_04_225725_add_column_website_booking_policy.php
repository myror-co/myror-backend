<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnWebsiteBookingPolicy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->text('cancellation_policy')->nullable()->after('meta_description');
            $table->text('no_show_policy')->nullable()->after('cancellation_policy');
            $table->text('deposit_policy')->nullable()->after('no_show_policy');
            $table->text('other_policy')->nullable()->after('deposit_policy');
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
            $table->dropColumn('cancellation_policy');
            $table->dropColumn('no_show_policy');
            $table->dropColumn('deposit_policy');
            $table->dropColumn('other_policy');
        });
    }
}
