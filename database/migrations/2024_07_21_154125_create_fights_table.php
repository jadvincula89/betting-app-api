<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateFightsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(' CREATE TABLE `fights` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fight_no` varchar(45) DEFAULT NULL,
  `event_id` int(11) NOT NULL,
  `total_meron_bet` decimal(15,2) DEFAULT NULL,
  `total_wala_bet` decimal(15,2) DEFAULT NULL,
  `created_by` varchar(45) DEFAULT NULL,
  `status` int(11) NOT NULL,
  `winner` int(5) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `bet_percentage` varchar(45) NOT NULL,
  `bet_on_meron` int(11) DEFAULT 0,
  `bet_on_wala` int(11) DEFAULT 0,
  `updated_by` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fights');
    }
}
