<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        // doctrine/dbal tidak terpasang, jadi gunakan ALTER TABLE langsung untuk MySQL.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE master_items MODIFY foto_barang VARCHAR(255) NULL');
            return;
        }

        Schema::table('master_items', function (Blueprint $table) {
            $table->string('foto_barang')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('master_items')->whereNull('foto_barang')->update(['foto_barang' => '']);
            DB::statement('ALTER TABLE master_items MODIFY foto_barang VARCHAR(255) NOT NULL');
            return;
        }

        Schema::table('master_items', function (Blueprint $table) {
            $table->string('foto_barang')->nullable(false)->change();
        });
    }
};
