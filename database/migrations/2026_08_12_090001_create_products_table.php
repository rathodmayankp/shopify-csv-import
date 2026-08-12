<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upload_id')->constrained()->cascadeOnDelete();

            $table->unsignedInteger('row_number');
            $table->string('handle')->nullable();
            $table->string('title');
            $table->longText('body_html')->nullable();
            $table->string('vendor')->nullable();
            $table->string('product_type')->nullable();
            $table->string('tags')->nullable();
            $table->boolean('published')->default(true);

            $table->string('sku')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('compare_at_price', 10, 2)->nullable();
            $table->unsignedInteger('inventory_qty')->default(0);
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('weight_unit')->nullable();

            $table->string('image_src')->nullable();
            $table->string('image_alt')->nullable();

            $table->string('status')->default('pending');
            // status: pending, processing, success, failed

            $table->string('shopify_product_id')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->index(['upload_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
