<?php namespace Radiantweb\Flexiblediscounts\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateDiscountsTable extends Migration
{

    public function up()
    {
        Schema::create('radiantweb_flodiscounts_discount', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->text('name');
            $table->text('description');
            $table->date('published')->nullable();
            $table->timestamps();
        });

        Schema::create('radiantweb_flodiscounts_discounts', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('discount_id')->nullable();
            $table->text('type')->nullable();
            $table->text('application')->nullable();
            $table->integer('expires')->nullable();
            $table->date('expire_date')->nullable();
            $table->text('code')->nullable();
            $table->integer('code_qty')->nullable();
            $table->integer('code_limit')->nullable();
            $table->integer('product_id')->nullable();
            $table->text('comp_target')->nullable();
            $table->text('comp_type')->nullable();
            $table->text('comp_oporator')->nullable();
            $table->decimal('comp_value',8,2)->nullable();
            $table->decimal('mod_value',8,2)->nullable();
            $table->text('mod_type')->nullable();
            $table->text('mod_target')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('radiantweb_flodiscounts_discount');
        Schema::drop('radiantweb_flodiscounts_discounts');
    }

}
