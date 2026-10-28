<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('doctor', function (Blueprint $t) {
            $t->string('live_status', 30)->default('not_started');
            $t->unsignedInteger('current_serial')->nullable();
            $t->string('live_note', 250)->nullable();
            $t->dateTime('expected_return')->nullable();
            $t->dateTime('live_updated_at')->nullable();
        });
    }
    public function down(): void {
        Schema::table('doctor', fn(Blueprint $t) => $t->dropColumn(['live_status','current_serial','live_note','expected_return','live_updated_at']));
    }
};
