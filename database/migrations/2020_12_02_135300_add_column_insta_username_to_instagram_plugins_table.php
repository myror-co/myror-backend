<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnInstaUsernameToInstagramPluginsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('instagram_plugins', function (Blueprint $table) {
            $table->string('instagram_username')->nullable()->after('instagram_user_id');
            $table->bigInteger('expires_in')->nullable()->after('access_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('instagram_plugins', function (Blueprint $table) {
            $table->dropColumn('instagram_username');
            $table->dropColumn('expires_in');
        });
    }
}
