<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateStatesToZimbabweanProvinces extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // IMPORTANT: First update existing users to avoid foreign key issues
        // Set state_id and lga_id to NULL for all users
        DB::table('users')->update([
            'state_id' => null,
            'lga_id' => null
        ]);
        
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear tables in correct order (child first, then parent)
        DB::table('lgas')->delete();
        DB::table('states')->delete();
        
        // Reset auto-increment counters
        DB::statement('ALTER TABLE states AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE lgas AUTO_INCREMENT = 1');
        
        // Insert Zimbabwean provinces
        $provinces = [
            ['name' => 'Bulawayo', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Harare', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Manicaland', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mashonaland Central', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mashonaland East', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mashonaland West', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Masvingo', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Matabeleland North', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Matabeleland South', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Midlands', 'created_at' => now(), 'updated_at' => now()],
        ];
        
        DB::table('states')->insert($provinces);
        
        // Create basic districts for each province
        $states = DB::table('states')->orderBy('id')->get();
        $districts = [];
        
        foreach ($states as $state) {
            $districts[] = [
                'name' => $state->name . ' Central District',
                'state_id' => $state->id,
                'created_at' => now(),
                'updated_at' => now()
            ];
            $districts[] = [
                'name' => $state->name . ' East District',
                'state_id' => $state->id,
                'created_at' => now(),
                'updated_at' => now()
            ];
            $districts[] = [
                'name' => $state->name . ' West District',
                'state_id' => $state->id,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        
        DB::table('lgas')->insert($districts);
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Warning: This won't restore Nigerian states
        // It will just clear Zimbabwean data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('lgas')->delete();
        DB::table('states')->delete();
        DB::statement('ALTER TABLE states AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE lgas AUTO_INCREMENT = 1');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}