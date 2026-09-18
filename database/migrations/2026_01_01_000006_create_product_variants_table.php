<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('size', 20);
            $table->string('color', 50)->nullable();
            $table->string('sku_variant')->unique();
            $table->decimal('price_adjustment', 12, 2)->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['product_id', 'size', 'color'], 'product_size_color_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
