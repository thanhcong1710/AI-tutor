<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Lesson;
use App\Models\LessonSegment;
use App\Models\LessonQuestion;
use App\Models\LessonAssignment;
use App\Models\LearningSession;
use App\Models\StudentAnswer;
use App\Models\LearningAnalytics;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@aitutor.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'subscription_type' => 'premium',
        ]);

        // Create Teachers
        $teacher1 = User::create([
            'name' => 'John Teacher',
            'email' => 'teacher@aitutor.com',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'subscription_type' => 'premium',
        ]);

        $teacher2 = User::create([
            'name' => 'Sarah Educator',
            'email' => 'sarah@aitutor.com',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'subscription_type' => 'premium',
        ]);

        // Create Students
        $student1 = User::create([
            'name' => 'Alice Student',
            'email' => 'student@aitutor.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'platform' => 'web',
            'subscription_type' => 'free',
            'preferences' => [
                'language' => 'vi',
                'voice' => 'vi-VN-Wavenet-A',
                'speed' => 1.0,
            ],
        ]);

        $student2 = User::create([
            'name' => 'Bob Learner',
            'email' => 'bob@aitutor.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'platform' => 'web',
            'subscription_type' => 'premium',
            'preferences' => [
                'language' => 'en',
                'voice' => 'en-US-Wavenet-D',
                'speed' => 1.2,
            ],
        ]);

        // Create Lessons
        $lesson1 = Lesson::create([
            'teacher_id' => $teacher1->id,
            'title' => 'English Grammar: Present Simple Tense',
            'description' => 'Learn the basics of Present Simple tense with examples and exercises',
            'subject' => 'english',
            'level' => 'beginner',
            'estimated_duration' => 30,
            'content' => "The Present Simple tense is used to describe habits, unchanging situations, general truths, and fixed arrangements.\n\nFormation:\n- Positive: I/You/We/They + verb\n- He/She/It + verb + s/es\n\nExamples:\n- I work every day.\n- She works in a hospital.\n- They play football on weekends.",
            'status' => 'ready',
            'published_at' => now(),
        ]);

        $lesson2 = Lesson::create([
            'teacher_id' => $teacher1->id,
            'title' => 'Basic Math: Addition and Subtraction',
            'description' => 'Master the fundamentals of addition and subtraction',
            'subject' => 'math',
            'level' => 'beginner',
            'estimated_duration' => 25,
            'content' => "Addition and Subtraction are the most basic mathematical operations.\n\nAddition (+):\n- Combining two or more numbers\n- Example: 5 + 3 = 8\n\nSubtraction (-):\n- Taking away one number from another\n- Example: 10 - 4 = 6",
            'status' => 'ready',
            'published_at' => now(),
        ]);

        $lesson3 = Lesson::create([
            'teacher_id' => $teacher2->id,
            'title' => 'Introduction to Logic: Deductive Reasoning',
            'description' => 'Learn the principles of deductive reasoning and logical thinking',
            'subject' => 'logic',
            'level' => 'intermediate',
            'estimated_duration' => 40,
            'content' => "Deductive reasoning is a logical process where a conclusion is based on the concordance of multiple premises that are generally assumed to be true.\n\nStructure:\n1. Major premise\n2. Minor premise\n3. Conclusion\n\nExample:\n- All humans are mortal (major premise)\n- Socrates is a human (minor premise)\n- Therefore, Socrates is mortal (conclusion)",
            'status' => 'ready',
            'published_at' => now(),
        ]);

        // Create Segments for Lesson 1
        $segment1 = LessonSegment::create([
            'lesson_id' => $lesson1->id,
            'order' => 0,
            'title' => 'What is Present Simple?',
            'content' => 'The Present Simple tense is used to describe habits, unchanging situations, general truths, and fixed arrangements.',
            'ai_explanation' => 'Think of Present Simple as the tense you use when talking about things that happen regularly or are always true. For example, "The sun rises in the east" or "I drink coffee every morning". It\'s called "simple" because it\'s one of the easiest tenses to form!',
        ]);

        $segment2 = LessonSegment::create([
            'lesson_id' => $lesson1->id,
            'order' => 1,
            'title' => 'How to Form Present Simple',
            'content' => "Formation:\n- Positive: I/You/We/They + verb\n- He/She/It + verb + s/es",
            'ai_explanation' => 'The key rule to remember: for he, she, and it, you add "s" or "es" to the verb. For example: "I work" but "She works". This is one of the most important rules in English grammar!',
        ]);

        $segment3 = LessonSegment::create([
            'lesson_id' => $lesson1->id,
            'order' => 2,
            'title' => 'Examples and Practice',
            'content' => "Examples:\n- I work every day.\n- She works in a hospital.\n- They play football on weekends.",
            'ai_explanation' => 'Notice how the verb changes based on the subject. "I work" vs "She works". Practice makes perfect! Try creating your own sentences using your daily activities.',
        ]);

        // Create Questions for Segment 1
        LessonQuestion::create([
            'lesson_segment_id' => $segment1->id,
            'order' => 0,
            'type' => 'multiple_choice',
            'question' => 'What is the Present Simple tense used for?',
            'options' => [
                'A. Only for future events',
                'B. Habits and general truths',
                'C. Only for past events',
                'D. Complex scientific theories'
            ],
            'correct_answer' => 'B',
            'explanation' => 'Present Simple is primarily used for habits, routines, and general truths that are always true.',
            'points' => 1,
            'difficulty' => 'easy',
        ]);

        LessonQuestion::create([
            'lesson_segment_id' => $segment2->id,
            'order' => 0,
            'type' => 'multiple_choice',
            'question' => 'Which sentence is correct?',
            'options' => [
                'A. She work every day',
                'B. She works every day',
                'C. She working every day',
                'D. She is work every day'
            ],
            'correct_answer' => 'B',
            'explanation' => 'For third person singular (he, she, it), we add "s" to the verb in Present Simple.',
            'points' => 1,
            'difficulty' => 'medium',
        ]);

        LessonQuestion::create([
            'lesson_segment_id' => $segment3->id,
            'order' => 0,
            'type' => 'short_answer',
            'question' => 'Complete the sentence: "They _____ (play) football on weekends."',
            'options' => null,
            'correct_answer' => 'play',
            'explanation' => 'For "they", we use the base form of the verb without adding "s".',
            'points' => 2,
            'difficulty' => 'medium',
        ]);

        // Create Assignments
        LessonAssignment::create([
            'lesson_id' => $lesson1->id,
            'teacher_id' => $teacher1->id,
            'student_id' => $student1->id,
            'assigned_at' => now(),
            'due_date' => now()->addDays(7),
            'status' => 'assigned',
            'teacher_notes' => 'Please complete this lesson by next week. Focus on the verb forms!',
        ]);

        LessonAssignment::create([
            'lesson_id' => $lesson2->id,
            'teacher_id' => $teacher1->id,
            'student_id' => $student1->id,
            'assigned_at' => now(),
            'due_date' => now()->addDays(5),
            'status' => 'assigned',
        ]);

        LessonAssignment::create([
            'lesson_id' => $lesson3->id,
            'teacher_id' => $teacher2->id,
            'student_id' => $student2->id,
            'assigned_at' => now(),
            'due_date' => now()->addDays(10),
            'status' => 'assigned',
        ]);

        // Create a completed session for student1
        $session1 = LearningSession::create([
            'student_id' => $student1->id,
            'lesson_id' => $lesson1->id,
            'platform' => 'web',
            'status' => 'completed',
            'current_segment_id' => $segment3->id,
            'total_segments' => 3,
            'completed_segments' => 3,
            'total_questions' => 3,
            'correct_answers' => 2,
            'total_points' => 4,
            'earned_points' => 3,
            'duration_seconds' => 1200,
            'completion_percentage' => 100,
            'score_percentage' => 75,
            'started_at' => now()->subHours(2),
            'completed_at' => now()->subHour(),
        ]);

        // Create Analytics
        LearningAnalytics::create([
            'student_id' => $student1->id,
            'subject' => 'english',
            'level' => 'beginner',
            'total_sessions' => 1,
            'completed_sessions' => 1,
            'total_questions_answered' => 3,
            'correct_answers' => 2,
            'average_score' => 75,
            'total_time_spent_minutes' => 20,
            'current_streak_days' => 1,
            'longest_streak_days' => 1,
            'last_activity_date' => now(),
            'strengths' => ['Present Simple basics', 'Verb conjugation'],
            'weaknesses' => ['Complex sentences'],
            'learning_pace' => ['overall' => 'medium'],
        ]);

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('');
        $this->command->info('📧 Login credentials:');
        $this->command->info('Admin: admin@aitutor.com / password');
        $this->command->info('Teacher: teacher@aitutor.com / password');
        $this->command->info('Student: student@aitutor.com / password');
    }
}
