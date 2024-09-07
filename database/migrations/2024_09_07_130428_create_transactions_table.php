<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id(); // Equivalent to `id` with auto-increment
            $table->unsignedInteger('fight_id');
            $table->unsignedInteger('event_id');
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('created_by', 155)->nullable();
            $table->timestamps(); // Includes `created_at` and `updated_at`
            $table->string('ticket_no', 155)->nullable();
            $table->tinyInteger('type')->nullable();
            $table->unsignedInteger('teller_id')->nullable();

            $table->unique(['ticket_no', 'type']);
            $table->index('fight_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
