<?php

namespace App\Http\Controllers\Timetabling;

use App\Helpers\Logger;
use App\Http\Controllers\Controller;
use App\Models\Records\AcademicProgram;
use App\Models\Records\Timetable;
use App\Models\Timetabling\CourseSession;
use App\Models\Timetabling\SessionGroup;
use App\Models\Users\UserLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SessionGroupController extends Controller
{
    protected $year_level_options = [
        '1st' => '1st',
        '2nd' => '2nd',
        '3rd' => '3rd',
        '4th' => '4th',
    ];

    protected $academic_term_options = [
        '1st' => '1st',
        '2nd' => '2nd',
        'semestral' => 'Semestral'
    ];

    protected $session_time_options = [
        'morning'   => 'Morning',
        'afternoon' => 'Afternoon',
        'evening'   => 'Evening',
    ];


    public function index(Timetable $timetable, Request $request, SessionGroup $sessionGroup)
    {
        $query = $timetable->sessionGroups()->with(['academicProgram', 'courseSessions.course']);

        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('session_name', 'like', "%{$search}%")
                    ->orWhere('year_level', 'like', "%{$search}%")
                    ->orWhereHas('academicProgram', function($q2) use ($search) {
                        $q2->where('program_abbreviation', 'like', "%{$search}%");
                    });
            });
        }

        $sessionGroups = $query->get();

        $sessionGroupsByProgram = $sessionGroups->groupBy('academic_program_id')->map(function ($groups) {
            return $groups->sortBy(function ($g) {
                $map = ['1st' => 1, '2nd' => 2, '3rd' => 3, '4th' => 4];
                return $map[$g->year_level] ?? 99;
            });
        });

        $courseSessionsBySessionGroup = $sessionGroups->mapWithKeys(function ($sessionGroup) {
            $termOrder = ['1st' => 1, '2nd' => 2, 'semestral' => 3];
            $sorted = $sessionGroup->courseSessions->sortBy(function ($cs) use ($termOrder) {
                return $termOrder[$cs->academic_term] ?? 99;
            });
            return [$sessionGroup->id => $sorted];
        });

        Logger::log('index', 'session groups', [
            'timetable_id' => $timetable->id,
            'timetable_name' => $timetable->timetable_name,
        ]);

        return view(
            'timetabling.timetable-session-groups.index',
            compact('timetable', 'sessionGroupsByProgram', 'courseSessionsBySessionGroup')
        );
    }

    public function create(Timetable $timetable)
    {
        $academic_program_options = AcademicProgram::all()->pluck('program_abbreviation', 'id')->toArray();
        $year_level_options = $this->year_level_options;
        $session_time_options = $this->session_time_options;

        Logger::log('create', 'session groups', [
            'timetable_id' => $timetable->id,
            'timetable_name' => $timetable->timetable_name,
        ]);

        return view(
            'timetabling.timetable-session-groups.create',
            compact('timetable', 'academic_program_options', 'year_level_options', 'session_time_options')
        );
    }

    public function store(Request $request, Timetable $timetable)
    {
        $validatedData = $request->validate([
            'session_name' => [
                'required',
                'string',
                'max:4',
                Rule::unique('session_groups')->where(function ($query) use ($request, $timetable) {
                    return $query->where('timetable_id', $timetable->id)
                        ->where('academic_program_id', $request->academic_program_id)
                        ->where('year_level', $request->year_level)
                        ->where('session_time', $request->session_time);
                }),
            ],
            'year_level'          => 'required|string',
            'academic_program_id' => 'required|exists:academic_programs,id',
            'short_description'   => 'nullable|string',
            'session_time'        => ['required', Rule::in(array_keys($this->session_time_options))],
        ]);

        $validatedData['timetable_id'] = $timetable->id;

        $sessionGroup = SessionGroup::create($validatedData);

        Logger::log('store', 'session groups', [
            'session_group_id' => $sessionGroup->id,
            'session_name' => $sessionGroup->session_name,
            'timetable_id' => $timetable->id,
            'timetable_name' => $timetable->timetable_name,
        ]);

        return redirect()->route('timetables.session-groups.index', $timetable)
            ->with('success', 'Class Session created successfully.');
    }

    public function edit(Timetable $timetable, SessionGroup $sessionGroup)
    {
        $academic_program_options = AcademicProgram::all()->pluck('program_abbreviation', 'id')->toArray();
        $year_level_options = $this->year_level_options;
        $session_time_options = $this->session_time_options;

        Logger::log('edit', 'session groups', [
            'timetable_id' => $timetable->id,
            'timetable_name' => $timetable->timetable_name,
        ]);

        return view(
            'timetabling.timetable-session-groups.edit',
            compact('sessionGroup', 'timetable', 'academic_program_options', 'year_level_options', 'session_time_options')
        );
    }

    public function update(Request $request, Timetable $timetable, SessionGroup $sessionGroup)
    {
        $validatedData = $request->validate([
            'session_name' => [
                'required',
                'string',
                'max:4',
                Rule::unique('session_groups')
                    ->ignore($sessionGroup->id)
                    ->where(function ($query) use ($request, $timetable) {
                        return $query->where('timetable_id', $timetable->id)
                            ->where('academic_program_id', $request->academic_program_id)
                            ->where('year_level', $request->year_level)
                            ->where('session_time', $request->session_time);
                    }),
            ],
            'year_level'          => 'required|string',
            'academic_program_id' => 'required|exists:academic_programs,id',
            'short_description'   => 'nullable|string',
            'session_time'        => ['required', Rule::in(array_keys($this->session_time_options))],
        ]);

        $sessionGroup->update($validatedData);

        Logger::log('update', 'session groups', [
            'session_group_id' => $sessionGroup->id,
            'session_name' => $sessionGroup->session_name,
            'timetable_id' => $timetable->id,
            'timetable_name' => $timetable->timetable_name,
        ]);

        return redirect()->route('timetables.session-groups.index', $timetable)
            ->with('success', 'Class Session updated successfully.');
    }

    public function show(Timetable $timetable, SessionGroup $sessionGroup)
    {
        $sessionGroup->load(['academicProgram', 'courseSessions.course']);

        $sessionFullName = trim(sprintf(
            '%s %s %s Year',
            $sessionGroup->academicProgram->program_abbreviation ?? 'Unknown',
            $sessionGroup->session_name,
            $sessionGroup->year_level
        ));

        Logger::log('show', 'session groups', [
            'session_group_id' => $sessionGroup->id,
            'session_name' => $sessionGroup->session_name,
            'timetable_id' => $timetable->id,
            'timetable_name' => $timetable->timetable_name,
        ]);

        return view('timetabling.timetable-session-groups.show', compact('timetable', 'sessionGroup', 'sessionFullName'));
    }

    public function copy(Timetable $timetable, SessionGroup $sessionGroup)
    {
        $academic_program_options = AcademicProgram::all()->pluck('program_abbreviation', 'id')->toArray();
        $year_level_options = $this->year_level_options;
        $session_time_options = $this->session_time_options;

        Logger::log('copy', 'session groups', [
            'source_session_group_id' => $sessionGroup->id,
            'source_session_name'     => $sessionGroup->session_name,
            'timetable_id'            => $timetable->id,
            'timetable_name'          => $timetable->timetable_name,
        ]);

        return view(
            'timetabling.timetable-session-groups.copy',
            compact('timetable', 'sessionGroup', 'academic_program_options', 'year_level_options', 'session_time_options')
        );
    }
    public function storeCopy(Request $request, Timetable $timetable, SessionGroup $sessionGroup)
    {
        // Validate like "store", but we’re creating a new row
        $validatedData = $request->validate([
            'session_name' => [
                'required',
                'string',
                'max:4',
                Rule::unique('session_groups')->where(function ($query) use ($request, $timetable) {
                    return $query->where('timetable_id', $timetable->id)
                        ->where('academic_program_id', $request->academic_program_id)
                        ->where('year_level', $request->year_level)
                        ->where('session_time', $request->session_time);
                }),
            ],
            'year_level'          => 'required|string',
            'academic_program_id' => 'required|exists:academic_programs,id',
            'short_description'   => 'nullable|string',
            'session_time'        => ['required', Rule::in(array_keys($this->session_time_options))],
        ]);

        $validatedData['timetable_id'] = $timetable->id;

        // Base the new group’s color on source (or null if none)
        $validatedData['session_color'] = $sessionGroup->session_color;

        // Create the new Session Group
        $newGroup = SessionGroup::create($validatedData);

        // Copy all course sessions from the source group to the new one
        $sessionGroup->load('courseSessions');

        foreach ($sessionGroup->courseSessions as $courseSession) {
            $newCourseSession = $courseSession->replicate(); // clone all attributes except id
            $newCourseSession->session_group_id = $newGroup->id;
            $newCourseSession->save();
        }

        Logger::log('store-copy', 'session groups', [
            'source_session_group_id' => $sessionGroup->id,
            'new_session_group_id'    => $newGroup->id,
            'new_session_name'        => $newGroup->session_name,
            'timetable_id'            => $timetable->id,
            'timetable_name'          => $timetable->timetable_name,
        ]);

        return redirect()->route('timetables.session-groups.index', $timetable)
            ->with('success', 'Class Session copied successfully (including its course sessions).');
    }
    public function destroy(Timetable $timetable, SessionGroup $sessionGroup)
    {
        $sessionGroupId   = $sessionGroup->id;
        $sessionGroupName = $sessionGroup->session_name;

        // Find all course sessions in THIS timetable that belong to this session group
        $sessions = CourseSession::where('session_group_id', $sessionGroupId)
            ->whereHas('sessionGroup', function ($q) use ($timetable) {
                $q->where('timetable_id', $timetable->id);
            })
            ->with(['sessionGroup.academicProgram'])
            ->get();

        foreach ($sessions as $session) {
            // Use the session's sessionGroup (should exist) to build the encoded token
            $group = $session->sessionGroup;
            if (! $group) {
                error_log("SessionGroup destroy: session {$session->id} missing sessionGroup relation; skipping XLSX clean.");
                // still attempt deletion below
            }

            $programAbbr = $group->academicProgram?->program_abbreviation ?? 'UNK';
            $yearLevel   = $group->year_level ?? '';
            $groupId     = $group->id ?? $sessionGroupId;
            $sessionId   = $session->id;

            $encoded = "{$programAbbr}_{$yearLevel}_{$groupId}_{$sessionId}";

            $xlsxPath = storage_path("app/exports/timetables/{$timetable->id}.xlsx");

            if (! file_exists($xlsxPath)) {
                error_log("SessionGroup destroy: XLSX not found for timetable {$timetable->id} (session {$sessionId})");
            } elseif (! is_writable($xlsxPath) && ! is_writable(dirname($xlsxPath))) {
                error_log("SessionGroup destroy: XLSX not writable for timetable {$timetable->id} (session {$sessionId})");
            } else {
                try {
                    $spreadsheet = IOFactory::load($xlsxPath);
                    $sheetCount  = $spreadsheet->getSheetCount();
                    $madeChange  = false;

                    for ($si = 0; $si < $sheetCount; $si++) {
                        $sheet = $spreadsheet->getSheet($si);
                        $table = $sheet->toArray(null, true, true, false);

                        if (empty($table) || !is_array($table[0])) {
                            continue;
                        }

                        $rowCount = count($table);
                        $colCount = count($table[0] ?? []);

                        // skip col 0 (time), skip row 0 (room header)
                        for ($r = 1; $r < $rowCount; $r++) {
                            for ($c = 1; $c < $colCount; $c++) {
                                $cellVal = trim((string)($table[$r][$c] ?? ''));

                                if ($cellVal === $encoded) {
                                    $excelRowIndex = $r + 1;
                                    $excelColIndex = $c + 1;
                                    $colLetter = Coordinate::stringFromColumnIndex($excelColIndex);
                                    $cellAddress = $colLetter . $excelRowIndex;

                                    $sheet->setCellValue($cellAddress, 'Vacant');
                                    $madeChange = true;
                                }
                            }
                        }
                    }

                    if ($madeChange) {
                        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                        $writer->save($xlsxPath);
                        error_log("SessionGroup destroy: replaced '{$encoded}' with 'Vacant' in timetable {$timetable->id}");
                    }
                } catch (\Throwable $e) {
                    error_log("SessionGroup destroy: XLSX cleaning error for session {$sessionId} — " . $e->getMessage());
                }
            }

            // Always attempt DB cleanup for the session
            try {
                $session->delete();
            } catch (\Throwable $e) {
                error_log("FAILED deleting CourseSession {$sessionId}: " . $e->getMessage());
            }
        }

        // Now delete the SessionGroup itself
        try {
            $sessionGroup->delete();
        } catch (\Throwable $e) {
            error_log("FAILED deleting SessionGroup {$sessionGroupId}: " . $e->getMessage());
            return redirect()->route('timetables.session-groups.index', $timetable)
                ->with('error', 'Failed to delete Class Session. Check logs for details.');
        }

        // User-facing audit log (keep using Logger:: for this)
        Logger::log('delete', 'session groups', [
            'session_group_id' => $sessionGroupId,
            'session_name'     => $sessionGroupName,
            'timetable_id'     => $timetable->id,
            'timetable_name'   => $timetable->timetable_name,
        ]);

        return redirect()->route('timetables.session-groups.index', $timetable)
            ->with('success', 'Class Session and its Course Sessions deleted successfully.');
    }
    public function updateColor(Request $request, Timetable $timetable, SessionGroup $sessionGroup)
    {
        $data = $request->validate([
            'session_color' => [
                'nullable',
                'string',
                'regex:/^#[0-9a-fA-F]{6}$/',
            ],
        ]);

        $sessionGroup->session_color = $data['session_color'];
        $sessionGroup->save();

        return response()->json([
            'status' => 'ok',
            'session_color' => $sessionGroup->session_color,
        ]);
    }


}
