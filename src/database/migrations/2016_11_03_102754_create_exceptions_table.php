<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExceptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exceptions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('host')->nullable();
            $table->string('method')->nullable();
            $table->text('fullUrl')->nullable();
            $table->text('exception')->nullable();
            $table->text('error')->nullable();
            $table->string('line')->nullable();
            $table->string('file')->nullable();
            $table->string('class')->nullable();
            $table->string('status')->nullable();
            $table->text('user')->nullable();
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
        Schema::dropIfExists('exceptions');
    }
}
