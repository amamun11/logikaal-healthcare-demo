<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lab_category', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->date('discount_starts_at')->nullable();
            $table->date('discount_ends_at')->nullable();
            $table->string('discount_note', 160)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('lab_category', function (Blueprint $table) {
            $table->dropColumn(['price', 'discount_percent', 'discount_starts_at', 'discount_ends_at', 'discount_note']);
        });
    }
};
