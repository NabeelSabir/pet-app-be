<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterSamplesAddLengthColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Samples', function (Blueprint $table) {
            $table->string('length')->nullable();
            $table->enum('length_unit',['CM', 'IN'])->default('CM');
            $table->enum('weight_unit',['KG', 'LB'])->default('KG');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
