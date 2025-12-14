<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Records\Course;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'course_title' => 'CCE 101/L',
                'course_name' => 'Introduction to Computing',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'

                //academic_programs => [1, 2] //CS or It or something
            ],
            [
                'course_title' => 'GE 15',
                'course_name' => 'Environmental Science',
                'course_type' => 'minor',
                'class_hours' => 1,
                'total_lecture_class_days' => 3,
                'total_laboratory_class_days' => 0,
                'unit_load' => 3,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'GE 3',
                'course_name' => 'The Contemporary World',
                'course_type' => 'minor',
                'class_hours' => 1,
                'total_lecture_class_days' => 3,
                'total_laboratory_class_days' => 0,
                'unit_load' => 3,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'PAHF 1',
                'course_name' => 'Movement Competency Training',
                'course_type' => 'pe',
                'class_hours' => 4,
                'total_lecture_class_days' => 1,
                'total_laboratory_class_days' => 0,
                'unit_load' => 3,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'CCE 109/L',
                'course_name' => 'Fundamentals of Programming',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'CS 8',
                'course_name' => 'Social Issues and Professional Practice',
                'course_type' => 'major',
                'class_hours' => 1,
                'total_lecture_class_days' => 3,
                'total_laboratory_class_days' => 0,
                'unit_load' => 3,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'GE 2',
                'course_name' => 'Purposive Communication and Interactive Learning',
                'course_type' => 'minor',
                'class_hours' => 1,
                'total_lecture_class_days' => 3,
                'total_laboratory_class_days' => 0,
                'unit_load' => 3,
                'duration_type' => 'semestral'
            ],
            [
                'course_title' => 'NSTP 1',
                'course_name' => 'National Service Training Program 1',
                'course_type' => 'nstp',
                'class_hours' => 1,
                'total_lecture_class_days' => 1,
                'total_laboratory_class_days' => 0,
                'unit_load' => 3,
                'duration_type' => 'semestral'
            ],
            [
                'course_title' => 'CCE 104/L',
                'course_name' => 'Information Management',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'CS 26/L',
                'course_name' => 'Software Development Fundamentals',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'PAHF 3',
                'course_name' => 'Dance and Sports 1',
                'course_type' => 'pe',
                'class_hours' => 4,
                'total_lecture_class_days' => 1,
                'total_laboratory_class_days' => 0,
                'unit_load' => 3,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'CS 3/L',
                'course_name' => 'Discrete Structures 2',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'HCI 101',
                'course_name' => 'Human Computer Interaction',
                'course_type' => 'major',
                'class_hours' => 1,
                'total_lecture_class_days' => 3,
                'total_laboratory_class_days' => 0,
                'unit_load' => 3,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'MTH 103/L',
                'course_name' => 'Probabilities and Statistics',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'MTH 105',
                'course_name' => 'Integral Calculus',
                'course_type' => 'major',
                'class_hours' => 1,
                'total_lecture_class_days' => 3,
                'total_laboratory_class_days' => 0,
                'unit_load' => 3,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'BSM 325',
                'course_name' => 'Numerical Analysis',
                'course_type' => 'major',
                'class_hours' => 1,
                'total_lecture_class_days' => 3,
                'total_laboratory_class_days' => 0,
                'unit_load' => 3,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'CS 12/L',
                'course_name' => 'Software Engineering 1',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'CST 9/L',
                'course_name' => 'CS Professional Track 3',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'GE 5',
                'course_name' => 'Science, Technology, and Society',
                'course_type' => 'minor',
                'class_hours' => 1,
                'total_lecture_class_days' => 3,
                'total_laboratory_class_days' => 0,
                'unit_load' => 3,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'CS 11/L',
                'course_name' => 'Architecture and Organization',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'CS 15/L',
                'course_name' => 'Programming Languages',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'GE 7',
                'course_name' => 'Art Appreciation',
                'course_type' => 'minor',
                'class_hours' => 1,
                'total_lecture_class_days' => 3,
                'total_laboratory_class_days' => 0,
                'unit_load' => 3,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'PHYS 101/L',
                'course_name' => 'College Physics 1',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'CCE 106/L',
                'course_name' => 'Application Development and Emerging Technologies',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'CS 18/L',
                'course_name' => 'CS Thesis Writing 1',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'CS 19/L',
                'course_name' => 'Operating Systems',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'CS 21/L',
                'course_name' => 'Networks and Communications',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'CS 24/L',
                'course_name' => 'CS Professional Track 6',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'
            ],

            // IT
            [
                'course_title' => 'GE 4',
                'course_name' => 'Mathematics in the Modern World',
                'course_type' => 'major',
                'class_hours' => 1,
                'total_lecture_class_days' => 3,
                'total_laboratory_class_days' => 0,
                'unit_load' => 3,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'CCE 102/L',
                'course_name' => 'Computer Programming 1',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'CCE 105/L',
                'course_name' => 'Data Structures and Algorithms',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'IT 4',
                'course_name' => 'Calculus 1',
                'course_type' => 'major',
                'class_hours' => 1,
                'total_lecture_class_days' => 3,
                'total_laboratory_class_days' => 0,
                'unit_load' => 3,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'IT 3/L',
                'course_name' => 'Networking 1',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'IT 5/L',
                'course_name' => 'IT Elective 2',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'IT 11/L',
                'course_name' => 'Networking 2',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'IT 14/L',
                'course_name' => 'Professional Track for IT 5',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'GE 11',
                'course_name' => 'The Entrepreneurial Mind',
                'course_type' => 'minor',
                'class_hours' => 1,
                'total_lecture_class_days' => 3,
                'total_laboratory_class_days' => 0,
                'unit_load' => 3,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'IT 10/L',
                'course_name' => 'IT Elective 3',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'IT 12/L',
                'course_name' => 'Systems Integration and Architecture',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'IT 13/L',
                'course_name' => 'Professional Track for IT 4',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'IT 23/L',
                'course_name' => 'Systems Administration and Maintenance',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'
            ],
            [
                'course_title' => 'IT 24/L',
                'course_name' => 'Capstone Project 2',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'
            ],

        ];

        Course::insertOrIgnore($data);
    }
}
