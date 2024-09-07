<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_events', function (Blueprint $table) {
            $table->increments('id')->unsigned()->zerofill();
            $table->string('description', 255)->nullable();
            $table->integer('isActive')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->string('created_by', 100)->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->integer('isOpen')->unsigned()->default(0);
            $table->integer('isDelete')->default(0);
            $table->string('event_no', 45)->nullable();
            $table->date('event_date')->nullable();
            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_events');
    }
}
