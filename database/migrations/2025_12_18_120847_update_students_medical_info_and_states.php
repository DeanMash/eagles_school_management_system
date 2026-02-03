<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateStudentsMedicalInfoAndStates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add medical_info column ONLY if it doesn't exist
        if (!Schema::hasColumn('users', 'medical_info')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('medical_info')->nullable()->after('phone2');
            });
        }
        
        // Note: States update is moved to separate migration
        // Don't update states here anymore
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove medical_info column if it exists
        if (Schema::hasColumn('users', 'medical_info')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('medical_info');
            });
        }
    }
}