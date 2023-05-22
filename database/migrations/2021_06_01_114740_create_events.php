<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->integer('Sample_id');
            $table->integer('user_id');
            $table->string('title');
            $table->timestamp('start_date_time')->useCurrent();
            $table->timestamp('end_date_time')->useCurrent();
            $table->string('repeat')->nullable();
            $table->timestamp('repeat_end_date_time')->nullable();
            $table->tinyInteger('is_deleted')->default(0);
            $table->timestamp('deleted_at')->nullable();
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
        Schema::dropIfExists('events');
    }
}
