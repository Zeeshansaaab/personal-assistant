<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('split_expenses', function (Blueprint $table) {
            $table->boolean('is_settled')->default(false)->after('split_details');
        });
    }

    public function down(): void
    {
        Schema::table('split_expenses', function (Blueprint $table) {
            $table->dropColumn('is_settled');
        });
    }
};
