<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToTableListings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->text('user')->nullable()->after('smart_location');
            $table->text('hosts')->nullable()->after('user');
            $table->decimal('lat', 9, 6)->nullable()->after('smart_location');
            $table->decimal('lng', 9, 6)->nullable()->after('lat');
            $table->integer('reviews_count')->nullable()->after('recent_review');
            $table->decimal('rating', 2, 1)->nullable()->after('reviews_count');
            $table->text('rules')->nullable()->after('capacity');
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
            $table->dropColumn('user');
            $table->dropColumn('hosts');
            $table->dropColumn('lat');
            $table->dropColumn('lng');
            $table->dropColumn('reviews_count');
            $table->dropColumn('rating');
            $table->dropColumn('rules');
        });
    }
}
