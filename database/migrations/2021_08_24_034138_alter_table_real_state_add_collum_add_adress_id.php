<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableRealStateAddCollumAddAdressId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('real_state', function (Blueprint $table) {
            $table->foreignId('adress_id')->nullable();
            $table->foreign('adress_id')->references('id')->on('adresses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('real_state', function (Blueprint $table) {
            $table->dropForeign('real_state_adress_id_foreign');
            $table->dropColumn('adress_id');
        });
    }
}
