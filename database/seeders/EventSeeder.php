<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get admin user to assign as creator
        $admin = User::where('user_type', 'admin')->first();
        
        if (!$admin) {
            $admin = User::first();
        }

        // Academic Year Events
        $events = [
            // January Events
            [
                'title' => 'School Re-opening Ceremony',
                'description' => 'Official opening of the new academic term. All students and staff required to attend.',
                'event_date' => Carbon::create(date('Y'), 1, 10),
                'start_time' => '08:00:00',
                'end_time' => '10:00:00',
                'venue' => 'School Assembly Hall',
                'event_type' => 'academic',
                'is_public' => true,
            ],
            [
                'title' => 'Mid-Term Exams - Term 1',
                'description' => 'Mid-term examinations for all classes.',
                'event_date' => Carbon::create(date('Y'), 1, 25),
                'start_time' => '09:00:00',
                'end_time' => '16:00:00',
                'venue' => 'Classrooms',
                'event_type' => 'exam',
                'is_public' => true,
            ],

            // February Events
            [
                'title' => 'Annual Sports Day',
                'description' => 'Inter-house sports competition. Parents are invited to attend.',
                'event_date' => Carbon::create(date('Y'), 2, 15),
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'venue' => 'School Sports Field',
                'event_type' => 'sports',
                'is_public' => true,
            ],
            [
                'title' => 'Parents-Teachers Meeting',
                'description' => 'First term parents-teachers meeting to discuss student progress.',
                'event_date' => Carbon::create(date('Y'), 2, 20),
                'start_time' => '14:00:00',
                'end_time' => '17:00:00',
                'venue' => 'School Auditorium',
                'event_type' => 'meeting',
                'is_public' => true,
            ],

            // March Events
            [
                'title' => 'Cultural Day Celebration',
                'description' => 'Annual cultural festival showcasing traditional dances, music, and food.',
                'event_date' => Carbon::create(date('Y'), 3, 10),
                'start_time' => '10:00:00',
                'end_time' => '18:00:00',
                'venue' => 'School Grounds',
                'event_type' => 'cultural',
                'is_public' => true,
            ],
            [
                'title' => 'End of Term 1 Exams',
                'description' => 'Final examinations for term 1.',
                'event_date' => Carbon::create(date('Y'), 3, 20),
                'start_time' => '09:00:00',
                'end_time' => '16:00:00',
                'venue' => 'Classrooms',
                'event_type' => 'exam',
                'is_public' => true,
            ],

            // April Events
            [
                'title' => 'Term Break',
                'description' => 'School closed for term break.',
                'event_date' => Carbon::create(date('Y'), 4, 1),
                'event_type' => 'holiday',
                'is_public' => true,
            ],
            [
                'title' => 'Term 2 Begins',
                'description' => 'Second term academic activities commence.',
                'event_date' => Carbon::create(date('Y'), 4, 15),
                'venue' => 'School Premises',
                'event_type' => 'academic',
                'is_public' => true,
            ],

            // May Events
            [
                'title' => 'Science Fair',
                'description' => 'Annual science exhibition by students.',
                'event_date' => Carbon::create(date('Y'), 5, 12),
                'start_time' => '09:00:00',
                'end_time' => '16:00:00',
                'venue' => 'Science Laboratory Block',
                'event_type' => 'academic',
                'is_public' => true,
            ],
            [
                'title' => 'Career Guidance Day',
                'description' => 'Professionals from various fields invited to guide students.',
                'event_date' => Carbon::create(date('Y'), 5, 25),
                'start_time' => '10:00:00',
                'end_time' => '15:00:00',
                'venue' => 'School Library',
                'event_type' => 'academic',
                'is_public' => true,
            ],

            // June Events
            [
                'title' => 'Mid-Year Break',
                'description' => 'Half-year holiday break.',
                'event_date' => Carbon::create(date('Y'), 6, 15),
                'event_type' => 'holiday',
                'is_public' => true,
            ],
            [
                'title' => 'Staff Development Workshop',
                'description' => 'Professional development training for teachers.',
                'event_date' => Carbon::create(date('Y'), 6, 20),
                'start_time' => '09:00:00',
                'end_time' => '16:00:00',
                'venue' => 'Staff Room',
                'event_type' => 'meeting',
                'is_public' => false,
            ],

            // July Events
            [
                'title' => 'Inter-School Debate Competition',
                'description' => 'Annual debate competition with neighboring schools.',
                'event_date' => Carbon::create(date('Y'), 7, 5),
                'start_time' => '10:00:00',
                'end_time' => '17:00:00',
                'venue' => 'School Auditorium',
                'event_type' => 'academic',
                'is_public' => true,
            ],

            // August Events
            [
                'title' => 'Independence Day Celebration',
                'description' => 'National independence day celebrations.',
                'event_date' => Carbon::create(date('Y'), 8, 7),
                'start_time' => '08:00:00',
                'end_time' => '12:00:00',
                'venue' => 'Assembly Ground',
                'event_type' => 'cultural',
                'is_public' => true,
            ],
            [
                'title' => 'Final Exams - Term 2',
                'description' => 'End of term 2 examinations.',
                'event_date' => Carbon::create(date('Y'), 8, 20),
                'start_time' => '09:00:00',
                'end_time' => '16:00:00',
                'venue' => 'Classrooms',
                'event_type' => 'exam',
                'is_public' => true,
            ],

            // September Events
            [
                'title' => 'Term 3 Begins',
                'description' => 'Final term of the academic year starts.',
                'event_date' => Carbon::create(date('Y'), 9, 3),
                'venue' => 'School Premises',
                'event_type' => 'academic',
                'is_public' => true,
            ],
            [
                'title' => 'School Founders Day',
                'description' => 'Celebration of school anniversary.',
                'event_date' => Carbon::create(date('Y'), 9, 15),
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'venue' => 'School Grounds',
                'event_type' => 'cultural',
                'is_public' => true,
            ],

            // October Events
            [
                'title' => 'Graduation Ceremony',
                'description' => 'Graduation ceremony for final year students.',
                'event_date' => Carbon::create(date('Y'), 10, 10),
                'start_time' => '15:00:00',
                'end_time' => '19:00:00',
                'venue' => 'Main Auditorium',
                'event_type' => 'academic',
                'is_public' => true,
            ],
            [
                'title' => 'Final Year Exams',
                'description' => 'Final examinations for the academic year.',
                'event_date' => Carbon::create(date('Y'), 10, 20),
                'start_time' => '09:00:00',
                'end_time' => '16:00:00',
                'venue' => 'Examination Hall',
                'event_type' => 'exam',
                'is_public' => true,
            ],

            // November Events
            [
                'title' => 'Prize Giving Day',
                'description' => 'Annual prize distribution ceremony for academic excellence.',
                'event_date' => Carbon::create(date('Y'), 11, 5),
                'start_time' => '10:00:00',
                'end_time' => '14:00:00',
                'venue' => 'School Assembly Hall',
                'event_type' => 'academic',
                'is_public' => true,
            ],
            [
                'title' => 'School Closing Day',
                'description' => 'End of academic year. Reports distributed to students.',
                'event_date' => Carbon::create(date('Y'), 11, 30),
                'start_time' => '09:00:00',
                'end_time' => '12:00:00',
                'venue' => 'Classrooms',
                'event_type' => 'academic',
                'is_public' => true,
            ],

            // December Events
            [
                'title' => 'Long Vacation Begins',
                'description' => 'School closed for end-of-year holidays.',
                'event_date' => Carbon::create(date('Y'), 12, 1),
                'event_type' => 'holiday',
                'is_public' => true,
            ],
            [
                'title' => 'Christmas Celebration',
                'description' => 'Christmas carols and celebrations.',
                'event_date' => Carbon::create(date('Y'), 12, 20),
                'start_time' => '18:00:00',
                'end_time' => '21:00:00',
                'venue' => 'School Chapel',
                'event_type' => 'cultural',
                'is_public' => true,
            ],
        ];

        // Create each event
        foreach ($events as $eventData) {
            // Check if event already exists for this date
            $existing = Event::where('title', $eventData['title'])
                ->whereDate('event_date', $eventData['event_date'])
                ->first();

            if (!$existing) {
                Event::create(array_merge($eventData, [
                    'created_by' => $admin->id,
                ]));
            }
        }

        // Add some random events for testing
        $this->createRandomEvents($admin->id);

        $this->command->info('Events seeded successfully!');
        $this->command->info('Total events created: ' . Event::count());
    }

    /**
     * Create random events for testing.
     *
     * @param int $creatorId
     * @return void
     */
    private function createRandomEvents($creatorId)
    {
        $eventTitles = [
            'Mathematics Club Meeting',
            'Library Orientation',
            'Cleanliness Drive',
            'Music Club Practice',
            'Drama Rehearsal',
            'Art Exhibition',
            'Computer Lab Maintenance',
            'Environmental Awareness Program',
            'Health Check-up Camp',
            'Book Fair',
        ];

        $venues = [
            'Room 101', 'Room 102', 'Room 201', 'Room 202',
            'Library', 'Auditorium', 'Sports Field', 'Chemistry Lab',
            'Computer Lab', 'Art Room', 'Music Room', 'Staff Room'
        ];

        $eventTypes = ['academic', 'exam', 'sports', 'cultural', 'meeting', 'other'];

        // Create 10 random events for the current month
        for ($i = 0; $i < 10; $i++) {
            $daysFromNow = rand(1, 30);
            
            Event::create([
                'title' => $eventTitles[array_rand($eventTitles)],
                'description' => 'Regular school activity or event.',
                'event_date' => now()->addDays($daysFromNow),
                'start_time' => sprintf('%02d:00:00', rand(8, 15)),
                'end_time' => sprintf('%02d:00:00', rand(16, 18)),
                'venue' => $venues[array_rand($venues)],
                'event_type' => $eventTypes[array_rand($eventTypes)],
                'created_by' => $creatorId,
                'is_public' => rand(0, 1) === 1,
            ]);
        }
    }
}