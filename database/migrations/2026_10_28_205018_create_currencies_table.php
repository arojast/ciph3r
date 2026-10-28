<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name', 100)->unique();
            $table->string('symbol', 10);
            $table->decimal('exchange_rate', 18, 6);

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

            $table->index('state');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
