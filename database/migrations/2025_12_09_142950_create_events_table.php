<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Check if table doesn't exist before creating
        if (!Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->date('event_date');
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->string('venue')->nullable();
                $table->enum('event_type', [
                    'academic', 
                    'exam', 
                    'sports', 
                    'cultural', 
                    'holiday', 
                    'meeting', 
                    'submission',  // ADDED: For assignment/project deadlines
                    'workshop',    // ADDED: For workshops/seminars
                    'event'        // ADDED: General events
                ])->default('event'); // CHANGED: Default to 'event' instead of 'academic'
                
                $table->unsignedBigInteger('created_by');
                $table->boolean('is_public')->default(true);
                
                // ADDED: For recurring events
                $table->enum('repeat', ['none', 'daily', 'weekly', 'monthly', 'yearly'])->default('none');
                $table->date('repeat_until')->nullable();
                
                // ADDED: For student-specific events
                $table->unsignedBigInteger('class_id')->nullable();
                $table->unsignedBigInteger('section_id')->nullable();
                
                $table->timestamps();
                
                // ADDED: Foreign key constraints
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('class_id')->references('id')->on('my_classes')->onDelete('cascade');
                $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade');
                
                // ADDED: Indexes for better performance
                $table->index('event_date');
                $table->index('event_type');
                $table->index('created_by');
                $table->index(['class_id', 'section_id']);
            });
        } else {
            // If table exists, add missing columns
            Schema::table('events', function (Blueprint $table) {
                // Check if columns exist before adding
                if (!Schema::hasColumn('events', 'repeat')) {
                    $table->enum('repeat', ['none', 'daily', 'weekly', 'monthly', 'yearly'])->default('none')->after('is_public');
                }
                if (!Schema::hasColumn('events', 'repeat_until')) {
                    $table->date('repeat_until')->nullable()->after('repeat');
                }
                if (!Schema::hasColumn('events', 'class_id')) {
                    $table->unsignedBigInteger('class_id')->nullable()->after('repeat_until');
                }
                if (!Schema::hasColumn('events', 'section_id')) {
                    $table->unsignedBigInteger('section_id')->nullable()->after('class_id');
                }
                
                // Update event_type enum if needed (requires specific handling)
                // This might need a separate migration for ENUM changes
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}