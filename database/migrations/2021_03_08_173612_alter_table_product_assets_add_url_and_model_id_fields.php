<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableProductAssetsAddUrlAndModelIdFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_assets', function (Blueprint $table) {
            $table->string('url')->nullable()->after('path');

            $table->unsignedBigInteger('model_id')->nullable()->after('product_id');
        });

        Schema::table('product_assets', function(Blueprint $table) {
            $table->foreign('model_id')->references('id')->on('product_assets');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_assets', function (Blueprint $table) {
            $table->dropColumn('url');

            // $table->dropForeign(['model_id']);

            // $table->dropColumn('model_id');
        });
    }
}
