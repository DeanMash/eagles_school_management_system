<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateClassNamesAndAddNewClasses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Get class type IDs
        $nurseryTypeId = DB::table('class_types')->where('code', 'N')->value('id');
        $primaryTypeId = DB::table('class_types')->where('code', 'P')->value('id');
        $juniorSecondaryTypeId = DB::table('class_types')->where('code', 'J')->value('id');
        $seniorSecondaryTypeId = DB::table('class_types')->where('code', 'S')->value('id');

        // Update existing class names
        $updates = [
            ['old_name' => 'JSS 2', 'new_name' => '1A'],
            ['old_name' => 'JSS 3', 'new_name' => '1B'],
            ['old_name' => 'Nursery 1', 'new_name' => '1C'],
            ['old_name' => 'Nursery 2', 'new_name' => '1D'],
            ['old_name' => 'Nursery 3', 'new_name' => '2A'],
            ['old_name' => 'Primary 1', 'new_name' => '2B'],
            ['old_name' => 'Primary 2', 'new_name' => '2C'],
            ['old_name' => 'SSS 1', 'new_name' => '2D'],
            ['old_name' => 'SSS 2', 'new_name' => '3A'],
            ['old_name' => 'SSS 3', 'new_name' => '3B'],
        ];

        foreach ($updates as $update) {
            DB::table('my_classes')
                ->where('name', $update['old_name'])
                ->update(['name' => $update['new_name']]);
        }

        // Add new classes
        $newClasses = [
            // Numbered classes - using Senior Secondary type
            ['name' => '3C', 'class_type_id' => $seniorSecondaryTypeId],
            ['name' => '3D', 'class_type_id' => $seniorSecondaryTypeId],
            ['name' => '4A', 'class_type_id' => $seniorSecondaryTypeId],
            ['name' => '4B', 'class_type_id' => $seniorSecondaryTypeId],
            ['name' => '4C', 'class_type_id' => $seniorSecondaryTypeId],
            ['name' => '4D', 'class_type_id' => $seniorSecondaryTypeId],
            // Lower 6 classes
            ['name' => 'Lower 6 Arts', 'class_type_id' => $seniorSecondaryTypeId],
            ['name' => 'Lower 6 Commercials', 'class_type_id' => $seniorSecondaryTypeId],
            ['name' => 'Lower 6 Sciences', 'class_type_id' => $seniorSecondaryTypeId],
            // Upper 6 classes
            ['name' => 'Upper 6 Arts', 'class_type_id' => $seniorSecondaryTypeId],
            ['name' => 'Upper 6 Commercials', 'class_type_id' => $seniorSecondaryTypeId],
            ['name' => 'Upper 6 Sciences', 'class_type_id' => $seniorSecondaryTypeId],
        ];

        // Insert new classes only if they don't exist
        foreach ($newClasses as $class) {
            $exists = DB::table('my_classes')
                ->where('name', $class['name'])
                ->exists();
            
            if (!$exists) {
                DB::table('my_classes')->insert([
                    'name' => $class['name'],
                    'class_type_id' => $class['class_type_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Create default sections for new classes
        $newClassIds = DB::table('my_classes')
            ->whereIn('name', array_column($newClasses, 'name'))
            ->pluck('id');

        foreach ($newClassIds as $classId) {
            $sectionExists = DB::table('sections')
                ->where('my_class_id', $classId)
                ->where('name', 'A')
                ->exists();
            
            if (!$sectionExists) {
                DB::table('sections')->insert([
                    'name' => 'A',
                    'my_class_id' => $classId,
                    'active' => 1,
                    'teacher_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert class name updates
        $reverts = [
            ['old_name' => '1A', 'new_name' => 'JSS 2'],
            ['old_name' => '1B', 'new_name' => 'JSS 3'],
            ['old_name' => '1C', 'new_name' => 'Nursery 1'],
            ['old_name' => '1D', 'new_name' => 'Nursery 2'],
            ['old_name' => '2A', 'new_name' => 'Nursery 3'],
            ['old_name' => '2B', 'new_name' => 'Primary 1'],
            ['old_name' => '2C', 'new_name' => 'Primary 2'],
            ['old_name' => '2D', 'new_name' => 'SSS 1'],
            ['old_name' => '3A', 'new_name' => 'SSS 2'],
            ['old_name' => '3B', 'new_name' => 'SSS 3'],
        ];

        foreach ($reverts as $revert) {
            DB::table('my_classes')
                ->where('name', $revert['old_name'])
                ->update(['name' => $revert['new_name']]);
        }

        // Delete new classes
        $newClassNames = [
            '3C', '3D', '4A', '4B', '4C', '4D',
            'Lower 6 Arts', 'Lower 6 Commercials', 'Lower 6 Sciences',
            'Upper 6 Arts', 'Upper 6 Commercials', 'Upper 6 Sciences',
        ];

        // Get IDs of classes to delete
        $classIds = DB::table('my_classes')
            ->whereIn('name', $newClassNames)
            ->pluck('id');

        // Delete sections first (due to foreign key)
        DB::table('sections')
            ->whereIn('my_class_id', $classIds)
            ->delete();

        // Delete classes
        DB::table('my_classes')
            ->whereIn('name', $newClassNames)
            ->delete();
    }
}
