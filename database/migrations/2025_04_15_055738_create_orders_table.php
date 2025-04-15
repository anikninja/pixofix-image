<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create the employees table for the order employee management
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('user_id')->index()->constrained();
            $table->timestamps();
            $table->softDeletes();
        });

        // Create the categories table for the order categories management
        // This table is used to categorize orders and items
        // It has a self-referencing foreign key to allow for nested categories
        // The parent_id column references the id of the parent category
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->index()->constrained('categories');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Create the orders table for the order management
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->date('order_date')->default(now());
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('employee_id')->nullable()->index()->constrained();
            $table->foreignId('category_id')->nullable()->index()->constrained();
            $table->timestamps();
            $table->softDeletes();        
        });

        // Create the order_items table for the order items management
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->index()->constrained();
            $table->string('upload');
            $table->string('image_title')->nullable();
            $table->longText('image_description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('categories');
        // Drop the foreign key constraints
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['category_id']);
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
        });
    }
};
