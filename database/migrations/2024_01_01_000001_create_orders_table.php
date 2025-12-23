<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('book_id');
            $table->string('book_name')->nullable();
            $table->datetime('order_date');
            $table->string('status')->default('Completed');
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}

