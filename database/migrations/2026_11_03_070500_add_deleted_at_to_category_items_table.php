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
        // Model CategoryItem memakai trait SoftDeletes, jadi tabel butuh kolom deleted_at.
        if (Schema::hasColumn('category_items', 'deleted_at')) {
            return;
        }

        Schema::table('category_items', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (! Schema::hasColumn('category_items', 'deleted_at')) {
            return;
        }

        Schema::table('category_items', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
