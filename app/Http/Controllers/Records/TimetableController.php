<?php

namespace App\Http\Controllers\Records;

use App\Helpers\Logger;
use App\Http\Controllers\Controller;
use App\Models\Records\Timetable;
use App\Models\Users\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class TimetableController extends Controller
{
    private $semesterOptions = [
        '1st' => '1st',
        '2nd' => '2nd'
    ];

    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {

            $owned = Timetable::with('user')
                ->where('user_id', $user->id)
                ->latest()
                ->get();

            $public = Timetable::with('user')
                ->where('visibility', 'public')
                ->where('user_id', '!=', $user->id)
                ->latest()
                ->get();

            $others = Timetable::with('user')
                ->where('user_id', '!=', $user->id)
                ->where('visibility', '!=', 'public')
                ->latest()
                ->get();


            return view('records.timetables.index', [
                'owned'  => $owned->whereNotNull('id')->values(),
                'public' => $public->whereNotNull('id')->values(),
                'others' => $others->whereNotNull('id')->values(),
                'private'=> collect(),
                'shared' => collect(),
            ]);

        } else {

            // ONE source of truth
            $visible = Timetable::visibleTo($user)
                ->with('user')
                ->latest()
                ->get();

            $private = $visible
                ->where('user_id', $user->id)
                ->where('visibility', 'private')
                ->values();

            $shared = $visible
                ->where('visibility', 'restricted')
                ->values();

            $public = $visible
                ->where('visibility', 'public')
                ->values();

            return view('records.timetables.index', [
                'private' => $private,
                'shared'  => $shared,
                'public'  => $public,
                'owned'   => collect(),
                'others'  => collect(),
            ]);
        }

    }




    public function create()
    {
        $semesterOptions = $this->semesterOptions;

        //Log
        Logger::log('create', 'timetable', null);

        return view('records.timetables.create', compact('semesterOptions'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'timetable_name' => 'required|string|max:30',
            'semester' => 'required|string|in:1st,2nd',
            'academic_year' => 'required|string',
            'timetable_description' => 'nullable|string',
        ]);

        $validatedData['user_id'] = auth()->id();
        $validatedData['visibility'] = 'private';

        $timetable = Timetable::create($validatedData);

        //Log
        Logger::log('store', 'timetable', [
            'timetable_id' => $timetable->id,
            'timetable_name' => $timetable->timetable_name,
            'semester' => $timetable->semester,
            'academic_year' => $timetable->academic_year,
            'timetable_description' => $timetable->timetable_description,
        ]);

        return redirect()->route('timetables.index')
            ->with('success', 'Timetable created successfully.');
    }

    public function show(Timetable $timetable)
    {
        $this->authorize('view', $timetable);

        $timetable->load('user');

        Logger::log('show', 'timetable', [
            'timetable_id' => $timetable->id,
            'timetable_name' => $timetable->timetable_name,
        ]);

        return view('records.timetables.show', compact('timetable'));
    }

    public function edit(Timetable $timetable)
    {
        $this->authorize('update', $timetable);

        $semesterOptions = $this->semesterOptions;

        return view('records.timetables.edit', compact('timetable', 'semesterOptions'));
    }


    public function update(Request $request, Timetable $timetable)
    {
        $this->authorize('update', $timetable);

        $validatedData = $request->validate([
            'timetable_name' => 'required|string|max:30',
            'semester' => 'required|string|in:1st,2nd',
            'academic_year' => 'required|string',
            'timetable_description' => 'nullable|string',
        ]);

        $validatedData['user_id'] = auth()->id();

        $timetable->update($validatedData);

        //Log
        Logger::log('update', 'timetable', $validatedData);

        return redirect()->route('timetables.index')
            ->with('success', 'Timetable updated successfully.');
    }

    public function copy(Timetable $timetable)
    {
        $this->authorize('copy', $timetable);

        $semesterOptions = $this->semesterOptions;

        Logger::log('copy', 'timetable', [
            'source_timetable_id' => $timetable->id,
            'source_name' => $timetable->timetable_name,
        ]);

        return view(
            'records.timetables.copy',
            compact('timetable', 'semesterOptions')
        );
    }

    public function storeCopy(Request $request, Timetable $timetable)
    {
        $this->authorize('copy', $timetable);

        $validated = $request->validate([
            'timetable_name' => 'required|string|max:30',
            'semester' => 'required|string|in:1st,2nd',
            'academic_year' => 'required|string',
            'timetable_description' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($timetable, $validated) {

            /* ----------------------------------------------------
             | 1. Create new timetable
             ---------------------------------------------------- */
            $newTimetable = Timetable::create([
                'timetable_name'        => $validated['timetable_name'],
                'semester'              => $validated['semester'],
                'academic_year'         => $validated['academic_year'],
                'timetable_description' => $validated['timetable_description'],
                'user_id'               => auth()->id(),
            ]);

            /* ----------------------------------------------------
             | 2. Copy session groups + course sessions
             ---------------------------------------------------- */
            foreach ($timetable->sessionGroups()->with('courseSessions')->get() as $group) {

                $newGroup = $group->replicate();
                $newGroup->timetable_id = $newTimetable->id;
                $newGroup->save();

                foreach ($group->courseSessions as $courseSession) {
                    $newSession = $courseSession->replicate();
                    $newSession->session_group_id = $newGroup->id;
                    $newSession->save();
                }
            }

            /* ----------------------------------------------------
             | 3. Copy timetable professors
             ---------------------------------------------------- */
            foreach ($timetable->professors as $professor) {
                $newTimetable->professors()->attach($professor->id);
            }

            /* ----------------------------------------------------
             | 4. Copy timetable rooms
             ---------------------------------------------------- */
            foreach ($timetable->rooms as $room) {
                $newTimetable->rooms()->attach($room->id);
            }

            /* ----------------------------------------------------
             | 5. COPY XLSX FILE (CANONICAL PATHS)
             ---------------------------------------------------- */

            $sourceBucketPath = "timetables/{$timetable->id}.xlsx";
            $destBucketPath   = "timetables/{$newTimetable->id}.xlsx";

            $sourceLocalPath = storage_path("app/exports/timetables/{$timetable->id}.xlsx");
            $destLocalPath   = storage_path("app/exports/timetables/{$newTimetable->id}.xlsx");

            /* ---- Bucket → Bucket copy ---- */
            if (Storage::disk('facultime')->exists($sourceBucketPath)) {
                try {
                    Storage::disk('facultime')->copy(
                        $sourceBucketPath,
                        $destBucketPath
                    );
                } catch (\Throwable $e) {
                    logger()->warning('Timetable XLSX copy (bucket) failed', [
                        'from' => $sourceBucketPath,
                        'to'   => $destBucketPath,
                        'error'=> $e->getMessage(),
                    ]);
                }
            }

            /* ---- Local → Local copy (canonical) ---- */
            if (file_exists($sourceLocalPath)) {
                try {
                    File::copy($sourceLocalPath, $destLocalPath);
                } catch (\Throwable $e) {
                    logger()->warning('Timetable XLSX copy (local) failed', [
                        'from' => $sourceLocalPath,
                        'to'   => $destLocalPath,
                        'error'=> $e->getMessage(),
                    ]);
                }
            }


            Logger::log('store-copy', 'timetable', [
                'source_timetable_id' => $timetable->id,
                'new_timetable_id'    => $newTimetable->id,
                'copied_xlsx'         => true,
            ]);

            return redirect()
                ->route('timetables.index')
                ->with('success', 'Timetable copied successfully (including Excel file).');
        });
    }


    public function destroy(Timetable $timetable)
    {
        $this->authorize('delete', $timetable);

        $timetableData = [
            'timetable_id' => $timetable->id,
            'timetable_name' => $timetable->timetable_name
        ];

        // Delete the corresponding XLSX file if it exists
        $filePath = "exports/timetables/{$timetable->id}.xlsx";

        // Prefer the local disk (storage/app root) — matches storage_path('app/...')
        if (Storage::disk('local')->exists($filePath)) {
            Storage::disk('local')->delete($filePath);
        } else {
            // fallback: try direct filesystem path (what the seeder cleans)
            $fullPath = storage_path("app/{$filePath}");
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }


        // Delete the timetable from the database
        $timetable->delete();

        //Log
        Logger::log('delete', 'timetable', $timetableData);

        return redirect()->route('timetables.index')
            ->with('success', 'Timetable deleted successfully.');
    }

}
