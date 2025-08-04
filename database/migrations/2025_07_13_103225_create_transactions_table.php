<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->date('transaction_date');
            $table->string('type');
            $table->decimal('amount', 10, 2);
            $table->string('notes')->nullable();
            $table->foreignId('expense_category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('shift_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('source_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('payment_method_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
