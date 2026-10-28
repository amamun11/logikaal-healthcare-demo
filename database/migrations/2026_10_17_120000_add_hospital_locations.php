<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor', function (Blueprint $table) {
            $table->string('chamber_floor', 100)->nullable();
        });
        Schema::table('lab_category', function (Blueprint $table) {
            $table->string('test_floor', 100)->nullable();
            $table->string('test_room', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('doctor', fn (Blueprint $table) => $table->dropColumn('chamber_floor'));
        Schema::table('lab_category', fn (Blueprint $table) => $table->dropColumn(['test_floor', 'test_room']));
    }
};
