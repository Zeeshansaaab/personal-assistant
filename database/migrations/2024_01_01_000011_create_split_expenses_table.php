<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('split_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
            $table->foreignId('paid_by')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('description');
            $table->date('expense_date');
            $table->enum('split_type', ['equal', 'unequal', 'percentage'])->default('equal');
            $table->json('split_details')->nullable(); // {user_id: amount} for unequal splits
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('split_expenses');
    }
};

