<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->increments('id')->unsigned()->zerofill();
            $table->integer('fight_id')->notNull();
            $table->integer('event_id')->notNull();
            $table->decimal('amount_bet', 15, 2)->nullable();
            $table->string('created_by', 45)->nullable();
            $table->integer('team')->notNull();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->integer('is_claimed')->nullable();
            $table->decimal('amount_claimed', 15, 2)->nullable();
            $table->integer('released_by')->nullable();
            $table->string('ticket_no', 155)->nullable();
            $table->timestamp('claimed_on')->nullable();
            $table->primary('id');
            $table->integer('winner')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tickets');
    }
}
