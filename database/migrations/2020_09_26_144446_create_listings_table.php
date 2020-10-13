<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateListingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('airbnb_id')->unique();
            $table->string('name')->unique();
            $table->string('picture_sm', 500)->nullable();
            $table->string('picture_xl', 500)->nullable();
            $table->integer('price')->nullable();
            $table->string('currency')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('smart_location')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->integer('bedrooms')->nullable();
            $table->integer('beds')->nullable();
            $table->integer('capacity')->nullable();
            $table->string('property_type')->nullable();
            $table->string('room_type')->nullable();
            $table->text('summary')->nullable();
            $table->text('description')->nullable();
            $table->text('space')->nullable();
            $table->text('neighborhood')->nullable();
            $table->text('amenities')->nullable();
            $table->integer('checkout_time')->nullable();
            $table->text('photos')->nullable();
            $table->text('recent_review')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('listings');
    }
}
