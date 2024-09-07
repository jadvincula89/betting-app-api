<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('configurations', function (Blueprint $table) {
            $table->id(); // Creates an auto-incrementing `id` column
            $table->string('name', 155); // Creates a VARCHAR column for `name`
            $table->string('value', 45)->nullable(); // Creates a VARCHAR column for `value` with nullable
            $table->string('bet_percentage', 45); // Creates a VARCHAR column for `bet_percentage`

            $table->primary('id'); // Sets `id` as the primary key
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configurations');
    }
};
