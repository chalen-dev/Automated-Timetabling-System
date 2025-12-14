<?php

namespace App\Livewire\Trays;

use App\Models\Records\Timetable;
use App\Models\Timetabling\CourseSession;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UnassignedCoursesTray extends Component
{
    public Timetable $timetable;

    /**
     * [
     *   [
     *     'session_group_id' => 123,
     *     'group_label' => 'CS A 1st Year (Morning)',
     *     'count' => 5,
     *     'items' => [
     *        ['course_session_id'=>..., 'course_title'=>..., 'terms_tried'=>..., 'reason'=>...],
     *     ],
     *   ],
     * ]
     */
    public array $unplacedGroups = [];

    public function mount(Timetable $timetable): void
    {
        $this->timetable = $timetable;
        $this->unplacedGroups = $this->loadUnassignedFromXlsx($timetable);
    }

    private function formatTermsTried(string $terms): string
    {
        $t = strtolower(trim($terms));
        if ($t === '') return 'Unknown';
        if ($t === '1st') return '1st Term';
        if ($t === '2nd') return '2nd Term';
        if ($t === 'both') return 'Both Terms';
        return $terms;
    }

    private function formatReasonTitle(string $reason): string
    {
        $r = strtolower(trim($reason));

        return match ($r) {
            'no_start_day_room_found' => 'No available slot/room',
            'course_not_allowed_for_session_program' => 'Program not allowed for this course',
            'invalid_slots_or_zero_days' => 'Invalid course configuration',
            'lab_course_must_be_2_hours' => 'Lab duration must be 2 hours',
            default => $this->humanizeFallback($reason),
        };
    }

    private function formatReasonHint(string $reason): string
    {
        $r = strtolower(trim($reason));

        return match ($r) {
            'no_start_day_room_found' => 'Couldn’t find a valid day/time/room combination that satisfies constraints.',
            'course_not_allowed_for_session_program' => 'This course is restricted to specific academic programs; this session group is not included.',
            'invalid_slots_or_zero_days' => 'The course has 0 required days or an invalid time/slot requirement.',
            'lab_course_must_be_2_hours' => 'This lab was configured as a lab but its class hours are not exactly 2.0.',
            default => '',
        };
    }

    private function humanizeFallback(string $text): string
    {
        $t = trim($text);
        if ($t === '') return 'Unknown reason';

        // Convert snake_case / kebab-case to words
        $t = str_replace(['_', '-'], ' ', strtolower($t));
        $t = preg_replace('/\s+/', ' ', $t);

        // Title Case
        return ucwords($t);
    }

    private function loadUnassignedFromXlsx(Timetable $timetable): array
    {
        $bucketPath = "timetables/{$timetable->id}.xlsx";
        $xlsxPath = null;
        $tempFile = null;

        // Try bucket first
        if (Storage::disk('facultime')->exists($bucketPath)) {
            $tempFile = tempnam(sys_get_temp_dir(), 'tt_unassigned_');
            file_put_contents($tempFile, Storage::disk('facultime')->get($bucketPath));
            $xlsxPath = $tempFile;
        } else {
            // Fallback to legacy local
            $legacyPath = storage_path("app/exports/timetables/{$timetable->id}.xlsx");
            if (file_exists($legacyPath)) {
                $xlsxPath = $legacyPath;
            }
        }

        if (!$xlsxPath || !file_exists($xlsxPath)) {
            if ($tempFile && file_exists($tempFile)) {
                @unlink($tempFile);
            }
            return [];
        }

        $rows = [];
        try {
            $spreadsheet = IOFactory::load($xlsxPath);
            $sheet = $spreadsheet->getSheetByName('Unassigned');

            if (!$sheet) {
                if ($tempFile && file_exists($tempFile)) {
                    @unlink($tempFile);
                }
                return [];
            }

            $table = $sheet->toArray(null, true, true, false);
            if (empty($table) || empty($table[0]) || !is_array($table[0])) {
                if ($tempFile && file_exists($tempFile)) {
                    @unlink($tempFile);
                }
                return [];
            }

            // Normalize headers
            $headerRow = array_map(function ($h) {
                return strtolower(trim((string) $h));
            }, $table[0]);

            $idx = function (string $name) use ($headerRow) {
                $pos = array_search($name, $headerRow, true);
                return ($pos === false) ? null : $pos;
            };

            $iCourseSessionId = $idx('course_session_id');
            $iCode            = $idx('code');
            $iTermsTried      = $idx('terms_tried');
            $iReason          = $idx('reason');

            // If the sheet isn't in the expected format, bail safely
            if ($iCourseSessionId === null || $iCode === null) {
                if ($tempFile && file_exists($tempFile)) {
                    @unlink($tempFile);
                }
                return [];
            }

            for ($r = 1; $r < count($table); $r++) {
                $row = $table[$r] ?? [];
                if (!is_array($row)) continue;

                $courseSessionId = (int) ($row[$iCourseSessionId] ?? 0);
                $code            = trim((string) ($row[$iCode] ?? ''));

                if ($courseSessionId <= 0 || $code === '') continue;

                $rows[] = [
                    'course_session_id' => $courseSessionId,
                    'code' => $code,
                    'terms_tried' => $iTermsTried !== null ? (string) ($row[$iTermsTried] ?? '') : '',
                    'reason' => $iReason !== null ? (string) ($row[$iReason] ?? '') : '',
                ];
            }
        } finally {
            if ($tempFile && file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }

        if (empty($rows)) {
            return [];
        }

        // Pull session_group_id from the encoded code: PROG_YEAR_SESSIONGROUPID_COURSESESSIONID
        $courseSessionIds = [];
        $sessionGroupToItems = [];

        foreach ($rows as $r) {
            $courseSessionIds[] = (int) $r['course_session_id'];

            $sessionGroupId = null;
            $parts = explode('_', $r['code']);
            if (count($parts) === 4) {
                $sessionGroupId = (int) $parts[2];
            }

            if (!$sessionGroupId) {
                $sessionGroupId = 0; // "Unknown" bucket
            }

            if (!isset($sessionGroupToItems[$sessionGroupId])) {
                $sessionGroupToItems[$sessionGroupId] = [];
            }

            $sessionGroupToItems[$sessionGroupId][] = $r;
        }

        $courseSessionIds = array_values(array_unique($courseSessionIds));

        // Load CourseSession + relations so we can show course title + group label/time
        $sessions = CourseSession::with(['course', 'sessionGroup.academicProgram'])
            ->whereIn('id', $courseSessionIds)
            ->get()
            ->keyBy('id');

        $groupsOut = [];

        foreach ($sessionGroupToItems as $sessionGroupId => $items) {
            $groupLabel = $sessionGroupId ? ("Session Group #{$sessionGroupId}") : 'Unknown Session Group';

            $example = null;
            foreach ($items as $it) {
                $example = $sessions->get((int) $it['course_session_id']);
                if ($example && $example->sessionGroup) break;
            }

            if ($example && $example->sessionGroup) {
                $sg = $example->sessionGroup;
                $abbr = $sg->academicProgram?->program_abbreviation ?? 'UNK';
                $groupLabel = trim($abbr . ' ' . $sg->session_name . ' ' . $sg->year_level . ' Year');

                if (!empty($sg->session_time)) {
                    $groupLabel .= ' (' . ucfirst((string) $sg->session_time) . ')';
                }
            }

            $enriched = [];
            foreach ($items as $it) {
                $cs = $sessions->get((int) $it['course_session_id']);
                $courseTitle = $cs && $cs->course
                    ? ($cs->course->course_title ?: $cs->course->course_name ?: '')
                    : '';

                $rawReason = trim((string) ($it['reason'] ?? ''));
                $rawTerms  = trim((string) ($it['terms_tried'] ?? ''));

                $enriched[] = [
                    'course_session_id' => (int) $it['course_session_id'],
                    'course_title' => $courseTitle,

                    'terms_tried' => $this->formatTermsTried($rawTerms),

                    'reason_title' => $this->formatReasonTitle($rawReason),
                    'reason_hint'  => $this->formatReasonHint($rawReason),

                    // keep raw values available (optional to display)
                    'reason_raw' => $rawReason,
                    'code' => (string) $it['code'],
                ];
            }

            $groupsOut[] = [
                'session_group_id' => (int) $sessionGroupId,
                'group_label' => $groupLabel,
                'count' => count($enriched),
                'items' => $enriched,
            ];
        }

        // Sort groups (unknown last, then by label)
        usort($groupsOut, function ($a, $b) {
            if (($a['session_group_id'] ?? 0) === 0 && ($b['session_group_id'] ?? 0) !== 0) return 1;
            if (($b['session_group_id'] ?? 0) === 0 && ($a['session_group_id'] ?? 0) !== 0) return -1;
            return strcmp((string) ($a['group_label'] ?? ''), (string) ($b['group_label'] ?? ''));
        });

        return $groupsOut;
    }

    public function render()
    {
        return view('livewire.trays.unassigned-courses-tray');
    }
}
