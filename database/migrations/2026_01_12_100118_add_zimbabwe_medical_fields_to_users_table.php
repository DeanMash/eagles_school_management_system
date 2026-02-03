<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddZimbabweMedicalFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Medical fields
            $table->text('medical_history')->nullable()->after('address');
            $table->text('medical_conditions')->nullable()->after('medical_history');
            $table->text('allergies')->nullable()->after('medical_conditions');
            
            // Emergency contact fields
            $table->string('emergency_contact_name')->nullable()->after('allergies');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_phone');
            
            // Zimbabwean location fields
            $table->string('province')->nullable()->after('emergency_contact_relationship');
            $table->string('district')->nullable()->after('province');
            $table->string('nationality')->nullable()->after('district');
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
            $table->dropColumn([
                'medical_history',
                'medical_conditions',
                'allergies',
                'emergency_contact_name',
                'emergency_contact_phone',
                'emergency_contact_relationship',
                'province',
                'district',
                'nationality'
            ]);
        });
    }
}
