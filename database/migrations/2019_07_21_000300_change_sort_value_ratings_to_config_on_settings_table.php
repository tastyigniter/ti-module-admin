<?php

namespace Admin\Database\Migrations;

use DB;
use Illuminate\Database\Migrations\Migration;

class ChangeSortValueRatingsToConfigOnSettingsTable extends Migration
{
    public function up()
    {
        DB::table('settings')
          ->where('sort', 'ratings')
          ->update(['sort' => 'config']);

        DB::table('menus_specials')
          ->update([
              'type' => 'F',
              'validity' => 'period',
          ]);
    }

    public function down()
    {
    }
}
