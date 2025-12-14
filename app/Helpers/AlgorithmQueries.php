<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Schema;

class AlgorithmQueries
{
    public static function get(){
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite'){
            return [
                'timetable-professors' => "SELECT tp.id AS timetable_professor_id, t.id AS timetable_id, t.timetable_name,
                                               p.id AS professor_id, (p.first_name || ' ' || p.last_name) AS professor_name,
                                               p.professor_type, p.position, ap.program_name AS academic_program
                                        FROM timetable_professors tp
                                        JOIN timetables t ON t.id = tp.timetable_id
                                        JOIN professors p ON p.id = tp.professor_id
                                        JOIN academic_programs ap ON ap.id = p.academic_program_id
                                        WHERE t.id = :timetableId
                                        "
                ,

                'timetable-rooms' => "SELECT tr.id AS timetable_room_id, t.id AS timetable_id, t.timetable_name, r.id AS room_id,
                                               r.room_name, r.room_type, r.room_capacity, r.course_type_exclusive_to,
                                               red.exclusive_days,
                                               reap.exclusive_programs
                                        FROM timetable_rooms tr
                                        JOIN timetables t ON t.id = tr.timetable_id
                                        JOIN rooms r ON r.id = tr.room_id
                                        LEFT JOIN (
                                            SELECT room_id,
                                                   GROUP_CONCAT(exclusive_day) AS exclusive_days
                                            FROM (
                                                SELECT room_id, exclusive_day
                                                FROM room_exclusive_days
                                                ORDER BY room_id,
                                                         CASE exclusive_day
                                                            WHEN 'mon' THEN 1
                                                            WHEN 'tue' THEN 2
                                                            WHEN 'wed' THEN 3
                                                            WHEN 'thu' THEN 4
                                                            WHEN 'fri' THEN 5
                                                            WHEN 'sat' THEN 6
                                                            WHEN 'sun' THEN 7
                                                            ELSE 8
                                                         END
                                            )
                                            GROUP BY room_id
                                        ) red ON red.room_id = r.id
                                        LEFT JOIN (
                                            SELECT room_id,
                                                   GROUP_CONCAT(program_abbreviation) AS exclusive_programs
                                            FROM (
                                                SELECT reap.room_id AS room_id, ap.program_abbreviation AS program_abbreviation
                                                FROM room_exclusive_academic_programs reap
                                                JOIN academic_programs ap ON ap.id = reap.academic_program_id
                                                WHERE ap.program_abbreviation IS NOT NULL AND TRIM(ap.program_abbreviation) <> ''
                                                ORDER BY reap.room_id, ap.program_abbreviation
                                            )
                                            GROUP BY room_id
                                        ) reap ON reap.room_id = r.id
                                        WHERE t.id = :timetableId
                                    "
                ,

                'session-groups' => "
                    SELECT
                        sg.id AS session_group_id,
                        t.id AS timetable_id,
                        t.timetable_name,
                        ap.program_name AS academic_program,
                        sg.session_name,
                        sg.year_level,
                        sg.session_time
                    FROM session_groups sg
                    JOIN academic_programs ap ON ap.id = sg.academic_program_id
                    JOIN timetables t ON t.id = sg.timetable_id
                    WHERE t.id = :timetableId
                "
                ,

                'course-sessions' => "SELECT sg.id AS session_group_id, sg.session_name, sg.year_level, ap.program_name AS academic_program,
                                        ap.program_abbreviation, cs.id AS course_session_id, cs.academic_term,
                                        c.id AS course_id, c.course_title, c.course_name, c.course_type, c.class_hours,
                                        c.total_lecture_class_days, c.total_laboratory_class_days, c.unit_load, c.duration_type,
                                        cap.course_programs
                                 FROM session_groups sg
                                 JOIN academic_programs ap ON ap.id = sg.academic_program_id
                                 JOIN course_sessions cs ON cs.session_group_id = sg.id
                                 JOIN courses c ON c.id = cs.course_id
                                 JOIN timetables t ON t.id = sg.timetable_id
                                 LEFT JOIN (
                                     SELECT course_id,
                                            GROUP_CONCAT(program_abbreviation) AS course_programs
                                     FROM (
                                         SELECT cap.course_id AS course_id, ap.program_abbreviation AS program_abbreviation
                                         FROM course_academic_programs cap
                                         JOIN academic_programs ap ON ap.id = cap.academic_program_id
                                         WHERE ap.program_abbreviation IS NOT NULL AND TRIM(ap.program_abbreviation) <> ''
                                         ORDER BY cap.course_id, ap.program_abbreviation
                                     )
                                     GROUP BY course_id
                                 ) cap ON cap.course_id = c.id
                                 WHERE t.id = :timetableId
                                 ORDER BY ap.program_name, sg.year_level, sg.session_name, c.course_name"
                ,
            ];
        }
        elseif ($driver === 'mysql'){
            return [
                'timetable-professors' => "
                    SELECT
                        tp.id AS timetable_professor_id,
                        t.id AS timetable_id,
                        t.timetable_name,
                        p.id AS professor_id,
                        CONCAT(p.first_name, ' ', p.last_name) AS professor_name,
                        p.professor_type,
                        p.position,
                        ap.program_name AS academic_program
                    FROM timetable_professors tp
                    JOIN timetables t ON t.id = tp.timetable_id
                    JOIN professors p ON p.id = tp.professor_id
                    JOIN academic_programs ap ON ap.id = p.academic_program_id
                    WHERE t.id = :timetableId
                ",

                'timetable-rooms' => "
                    SELECT
                        tr.id AS timetable_room_id,
                        t.id AS timetable_id,
                        t.timetable_name,
                        r.id AS room_id,
                        r.room_name,
                        r.room_type,
                        r.room_capacity,
                        r.course_type_exclusive_to,
                        red.exclusive_days,
                        reap.exclusive_programs
                    FROM timetable_rooms tr
                    JOIN timetables t ON t.id = tr.timetable_id
                    JOIN rooms r ON r.id = tr.room_id
                    LEFT JOIN (
                        SELECT
                            room_id,
                            GROUP_CONCAT(
                                exclusive_day
                                ORDER BY FIELD(
                                    exclusive_day,
                                    'mon','tue','wed','thu','fri','sat','sun'
                                )
                            ) AS exclusive_days
                        FROM room_exclusive_days
                        GROUP BY room_id
                    ) red ON red.room_id = r.id
                    LEFT JOIN (
                        SELECT
                            reap.room_id AS room_id,
                            GROUP_CONCAT(
                                ap.program_abbreviation
                                ORDER BY ap.program_abbreviation
                                SEPARATOR ','
                            ) AS exclusive_programs
                        FROM room_exclusive_academic_programs reap
                        JOIN academic_programs ap ON ap.id = reap.academic_program_id
                        WHERE ap.program_abbreviation IS NOT NULL AND TRIM(ap.program_abbreviation) <> ''
                        GROUP BY reap.room_id
                    ) reap ON reap.room_id = r.id
                    WHERE t.id = :timetableId
                ",

                'session-groups' => "
                    SELECT
                        sg.id AS session_group_id,
                        t.id AS timetable_id,
                        t.timetable_name,
                        ap.program_name AS academic_program,
                        sg.session_name,
                        sg.year_level,
                        sg.session_time
                    FROM session_groups sg
                    JOIN academic_programs ap ON ap.id = sg.academic_program_id
                    JOIN timetables t ON t.id = sg.timetable_id
                    WHERE t.id = :timetableId
                ",

                'course-sessions' => "
                    SELECT
                        sg.id AS session_group_id,
                        sg.session_name,
                        sg.year_level,
                        ap.program_name AS academic_program,
                        ap.program_abbreviation,
                        cs.id AS course_session_id,
                        cs.academic_term,
                        c.id AS course_id,
                        c.course_title,
                        c.course_name,
                        c.course_type,
                        c.class_hours,
                        c.total_lecture_class_days,
                        c.total_laboratory_class_days,
                        c.unit_load,
                        c.duration_type,
                        cap.course_programs
                    FROM session_groups sg
                    JOIN academic_programs ap ON ap.id = sg.academic_program_id
                    JOIN course_sessions cs ON cs.session_group_id = sg.id
                    JOIN courses c ON c.id = cs.course_id
                    JOIN timetables t ON t.id = sg.timetable_id
                    LEFT JOIN (
                        SELECT
                            cap.course_id AS course_id,
                            GROUP_CONCAT(
                                ap.program_abbreviation
                                ORDER BY ap.program_abbreviation
                                SEPARATOR ','
                            ) AS course_programs
                        FROM course_academic_programs cap
                        JOIN academic_programs ap ON ap.id = cap.academic_program_id
                        WHERE ap.program_abbreviation IS NOT NULL AND TRIM(ap.program_abbreviation) <> ''
                        GROUP BY cap.course_id
                    ) cap ON cap.course_id = c.id
                    WHERE t.id = :timetableId
                    ORDER BY ap.program_name, sg.year_level, sg.session_name, c.course_name
                ",
            ];
        }
        return [];
    }
}
