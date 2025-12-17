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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
                        ->where('year_level', $request->year_level);
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
                            ->where('year_level', $request->year_level);
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
                        ->where('year_level', $request->year_level);
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

        /*
        |--------------------------------------------------------------------------
        | 1. LOAD DB DATA FIRST (DO NOT DELETE YET)
        |--------------------------------------------------------------------------
        */
        $sessions = $sessionGroup->courseSessions()->get();

        /*
        |--------------------------------------------------------------------------
        | 2. LOAD XLSX (bucket first, local fallback)
        |--------------------------------------------------------------------------
        */
        $disk = Storage::disk('facultime');
        $remotePath = "timetables/{$timetable->id}.xlsx";

        if ($disk->exists($remotePath)) {
            $tempPath = tempnam(sys_get_temp_dir(), 'tt_') . '.xlsx';
            file_put_contents($tempPath, $disk->get($remotePath));
            if (filesize($tempPath) < 1024) {
                throw new \Exception('Downloaded XLSX is too small / invalid');
            }
            $writeBackToBucket = true;
        } else {
            $tempPath = storage_path("app/exports/timetables/{$timetable->id}.xlsx");
            $writeBackToBucket = false;

            if (!file_exists($tempPath)) {
                return redirect()
                    ->route('timetables.session-groups.index', $timetable)
                    ->with('success', 'Session group deleted (no XLSX found).');
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 3. CLEAR ALL CELLS FOR THIS SESSION GROUP (ALL SHEETS)
        |--------------------------------------------------------------------------
        */
        try {
            $spreadsheet = IOFactory::load($tempPath);
            $needle = '_' . $sessionGroupId . '_';
            $changed = false;

            foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
                $highestRow = $sheet->getHighestRow();
                $highestCol = Coordinate::columnIndexFromString($sheet->getHighestColumn());

                // skip header row + time column
                for ($row = 2; $row <= $highestRow; $row++) {
                    for ($col = 2; $col <= $highestCol; $col++) {

                        $cellAddress = Coordinate::stringFromColumnIndex($col) . $row;
                        $cell = $sheet->getCell($cellAddress);
                        $value = trim((string) $cell->getValue());

                        if ($value !== '' && str_contains($value, $needle)) {
                            $cell->setValue('vacant');
                            $changed = true;
                        }
                    }
                }

            }

            /*
            |--------------------------------------------------------------------------
            | 4. SAVE XLSX BACK
            |--------------------------------------------------------------------------
            */
            if ($changed) {
                IOFactory::createWriter($spreadsheet, 'Xlsx')->save($tempPath);

                if ($writeBackToBucket) {
                    $disk->put($remotePath, fopen($tempPath, 'r'));
                    unlink($tempPath);
                }
            }
        } catch (\Throwable $e) {
            // XLSX failed → DO NOT DELETE DB
            return redirect()
                ->route('timetables.session-groups.index', $timetable)
                ->with('error', 'Failed to update timetable file. No records were deleted.');
        }

        /*
        |--------------------------------------------------------------------------
        | 5. DELETE DB RECORDS LAST (SAFE)
        |--------------------------------------------------------------------------
        */
        DB::transaction(function () use ($sessionGroup) {
            $sessionGroup->courseSessions()->delete();
            $sessionGroup->delete();
        });

        Logger::log('delete', 'session groups', [
            'session_group_id' => $sessionGroupId,
            'session_name'     => $sessionGroupName,
            'timetable_id'     => $timetable->id,
            'timetable_name'   => $timetable->timetable_name,
        ]);

        return redirect()
            ->route('timetables.session-groups.index', $timetable)
            ->with('success', 'Session group and all timetable cells cleared.');
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
