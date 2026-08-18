<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCarreraIdToUsersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('users') || Schema::hasColumn('users', 'carrera_id')) {
            return;
        }

        $afterColumn = Schema::hasColumn('users', 'is_admin') ? 'is_admin' : 'password';

        Schema::table('users', function (Blueprint $table) use ($afterColumn) {
            $table->unsignedBigInteger('carrera_id')->nullable()->after($afterColumn);
            $table->foreign('carrera_id')->references('id')->on('carreras')->onDelete('set null');
        });
    }

    public function down()
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'carrera_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['carrera_id']);
            $table->dropColumn('carrera_id');
        });
    }
}
