<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Records\Course;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // 1
            [
                'course_title' => 'CCE 101/L',
                'course_name' => 'Introduction to Computing',
                'course_type' => 'major',
                'class_hours' => 2,
                'total_lecture_class_days' => 2,
                'total_laboratory_class_days' => 2,
                'unit_load' => 4.5,
                'duration_type' => 'term'
            ],
            // 2
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
            // 3
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
            // 4
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
            // 5
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
            // 6
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
            // 7
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
            // 8
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
            // 9
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
            // 10
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
            // 11
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
            // 12
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
            // 13
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
            // 14
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
            // 15
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
            // 16
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
            // 17
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
            // 18
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
            // 19
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
            // 20
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
            // 21
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
            // 22
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
            // 23
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
            // 24
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
            // 25
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
            // 26
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
            // 27
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
            // 28
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

            // 29
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
            // 30
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
            // 31
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
            // 32
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
            // 33
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
            // 34
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
            // 35
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
            // 36
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
            // 37
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
            // 38
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
            // 39
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
            // 40
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
            // 41
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
            // 42
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
