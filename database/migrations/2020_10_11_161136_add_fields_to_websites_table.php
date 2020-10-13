<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToWebsitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->string('title')->nullable()->after('name');
            $table->text('meta_description')->nullable()->after('description');
            $table->enum('status', ['initiated', 'sending_files', 'deploying', 'built'])->after('user_id');
            $table->string('vercel_project_id')->nullable()->after('status');
            $table->text('description')->change();
            $table->string('main_picture', 500)->change();
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
            $table->dropColumn('title');
            $table->dropColumn('meta_description');
            $table->dropColumn('status');
            $table->dropColumn('vercel_project_id');
        });
    }
}
