<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automations', function (Blueprint $table) {
            $table->id();
            $table->boolean('warning')->default(false);
            $table->string('automation');
            $table->string('customerKey');
            $table->integer('status');
            $table->string('statusMessage');
            $table->dateTime('startTime');
            $table->string('dataExtension');
            $table->integer('dataExtension_count');
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
        Schema::dropIfExists('automations');
    }
}
