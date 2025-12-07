<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lendings', function (Blueprint $table) {
            $table->id();
            $table->string('person_name');
            $table->string('item_type'); // 'money' or 'item'
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('item_description')->nullable();
            $table->date('date_given');
            $table->date('expected_return_date');
            $table->date('actual_return_date')->nullable();
            $table->boolean('is_returned')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lendings');
    }
};

