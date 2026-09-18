<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->comment('jika bukan user terdaftar');
            $table->tinyInteger('rating')->unsigned()->comment('1-5');
            $table->text('comment');
            $table->string('photo')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            $table->index('is_approved');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
