<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnLastBuiltWebsites extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->foreignId('template_id')->after('user_id')->default(1)->constrained()->onDelete('cascade');
            $table->timestamp('last_built_at')->nullable()->after('stripe_account_id');
            $table->timestamp('last_update_request_at')->nullable()->after('last_built_at');
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
             $table->dropColumn('last_built_at');
             $table->dropColumn('template_id');
             $table->dropColumn('last_update_request_at');
        });
    }
}
