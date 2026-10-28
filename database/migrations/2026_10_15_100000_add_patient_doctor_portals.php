<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('doctor', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->nullable()->unique();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->text('biography')->nullable();
            $table->string('chamber_room', 100)->nullable();
            $table->json('working_days')->nullable();
            $table->time('chamber_start')->nullable();
            $table->time('chamber_end')->nullable();
            $table->unsignedSmallInteger('slot_minutes')->default(15);
            $table->boolean('accepting_bookings')->default(false);
        });
        Schema::table('patient', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->nullable()->unique();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('patient', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
        Schema::table('doctor', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'biography', 'chamber_room', 'working_days', 'chamber_start', 'chamber_end', 'slot_minutes', 'accepting_bookings']);
        });
    }
};
