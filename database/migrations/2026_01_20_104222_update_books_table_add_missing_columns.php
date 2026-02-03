<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBooksTableAddMissingColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('books', function (Blueprint $table) {
            // Check and add missing columns if they don't exist
            if (!Schema::hasColumn('books', 'title')) {
                $table->string('title')->after('isbn');
            }
            if (!Schema::hasColumn('books', 'author')) {
                $table->string('author')->after('title');
            }
            if (!Schema::hasColumn('books', 'publisher')) {
                $table->string('publisher')->nullable()->after('author');
            }
            if (!Schema::hasColumn('books', 'year_published')) {
                $table->year('year_published')->nullable()->after('publisher');
            }
            if (!Schema::hasColumn('books', 'category')) {
                $table->string('category')->after('year_published');
            }
            if (!Schema::hasColumn('books', 'copies')) {
                $table->integer('copies')->default(1)->after('category');
            }
            if (!Schema::hasColumn('books', 'available_copies')) {
                $table->integer('available_copies')->default(1)->after('copies');
            }
            if (!Schema::hasColumn('books', 'shelf_number')) {
                $table->string('shelf_number')->nullable()->after('available_copies');
            }
            if (!Schema::hasColumn('books', 'description')) {
                $table->text('description')->nullable()->after('shelf_number');
            }
            if (!Schema::hasColumn('books', 'book_cover')) {
                $table->string('book_cover')->nullable()->after('description');
            }
            if (!Schema::hasColumn('books', 'status')) {
                $table->enum('status', ['available', 'checked_out', 'reserved', 'lost', 'damaged'])->default('available')->after('book_cover');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Don't drop columns in down() to avoid data loss
        // If needed, columns can be dropped manually
    }
}
