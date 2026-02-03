<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixBooksTableNameColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Check if name column exists
        $hasNameColumn = Schema::hasColumn('books', 'name');
        
        if ($hasNameColumn) {
            // Make existing name column nullable
            Schema::table('books', function (Blueprint $table) {
                $table->string('name')->nullable()->change();
            });
        } else {
            // Add name column if it doesn't exist
            Schema::table('books', function (Blueprint $table) {
                $table->string('name')->nullable()->after('title');
            });
        }
        
        // Update existing records to set name = title where name is null or empty
        DB::statement("UPDATE books SET name = COALESCE(NULLIF(name, ''), title) WHERE name IS NULL OR name = ''");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
