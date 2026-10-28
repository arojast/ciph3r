<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_prices', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('product_id');
            $table->unsignedInteger('currency_id');

            $table->decimal('price', 15, 4);

            // Audit fields
            $table->timestamps();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Logical deletion
            $table->unsignedTinyInteger('state')->default(1);

            // Foreign keys
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->restrictOnDelete();

            $table->foreign('currency_id')
                ->references('id')
                ->on('currencies')
                ->restrictOnDelete();

            // Prevent duplicate currency prices per product
            $table->unique([
                'product_id',
                'currency_id'
            ]);

            $table->index('state');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};
