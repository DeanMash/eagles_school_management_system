<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksTable extends Migration
{
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('isbn')->unique()->nullable();
            $table->string('title');
            $table->string('author');
            $table->string('publisher')->nullable();
            $table->year('year_published')->nullable();
            $table->string('category');
            $table->integer('copies')->default(1);
            $table->integer('available_copies')->default(1);
            $table->string('shelf_number')->nullable();
            $table->text('description')->nullable();
            $table->string('book_cover')->nullable();
            $table->enum('status', ['available', 'checked_out', 'reserved', 'lost', 'damaged'])->default('available');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('title');
            $table->index('author');
            $table->index('category');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('books');
    }
}