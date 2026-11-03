<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_item_master_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_item_id')->constrained('category_items')->cascadeOnDelete();
            $table->foreignId('master_item_id')->constrained('master_items')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['category_item_id', 'master_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category_item_master_item');
    }
};
