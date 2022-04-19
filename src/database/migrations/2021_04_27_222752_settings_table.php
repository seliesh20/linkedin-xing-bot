<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string("var_name");
            $table->string("var_full_name");
            $table->string("value");            
            $table->timestamps();
        });

        // Insert settings table data
        DB::table('settings')->insert([
            array(
                'var_name' => 'gender_api_token',
                'var_full_name' => 'Gender API Token',
                'value' => ''
            ),
            array(
                'var_name' => 'anti_captche_api_token',
                'var_full_name' => 'Anti Captcha API Token',
                'value' => ''
            ),
            array(
                'var_name' => 'max_withdraw_days',
                'var_full_name' => 'Maximum Withdraw Days',
                'value' => '7'
            )
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('failed_jobs');
    }
}
