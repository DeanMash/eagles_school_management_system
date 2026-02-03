<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingMedicalFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('users', 'medical_history')) {
                $table->text('medical_history')->nullable()->after('address');
            }
            if (!Schema::hasColumn('users', 'medical_conditions')) {
                $table->text('medical_conditions')->nullable()->after('medical_history');
            }
            if (!Schema::hasColumn('users', 'allergies')) {
                $table->text('allergies')->nullable()->after('medical_conditions');
            }
            if (!Schema::hasColumn('users', 'emergency_contact_name')) {
                $table->string('emergency_contact_name')->nullable()->after('allergies');
            }
            if (!Schema::hasColumn('users', 'emergency_contact_phone')) {
                $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            }
            if (!Schema::hasColumn('users', 'emergency_contact_relationship')) {
                $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_phone');
            }
            if (!Schema::hasColumn('users', 'province')) {
                $table->string('province')->nullable()->after('emergency_contact_relationship');
            }
            if (!Schema::hasColumn('users', 'district')) {
                $table->string('district')->nullable()->after('province');
            }
            if (!Schema::hasColumn('users', 'nationality')) {
                $table->string('nationality')->nullable()->after('district');
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
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'medical_history',
                'medical_conditions',
                'allergies',
                'emergency_contact_name',
                'emergency_contact_phone',
                'emergency_contact_relationship',
                'province',
                'district',
                'nationality'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
