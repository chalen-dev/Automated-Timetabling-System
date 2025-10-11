<?php

namespace App\Http\Controllers\records;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Professor;
use App\Models\Specialization;
use App\Models\UserLog;
use Illuminate\Http\Request;

class SpecializationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Professor $professor)
    {
        $courses = Course::all();
        $specializations = $professor->specializations()->with('course')->get();

        $this->logAction('viewed_specializations', [
            'professor_id' => $professor->id,
            'professor_name' => $professor->name ?? 'N/A'
        ]);

        return view('records.specializations.index', compact('specializations', 'professor', 'courses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, Professor $professor)
    {
        $assignedCourseIds = $professor->specializations()->pluck('course_id')->toArray();

        $query = Course::whereNotIn('id', $assignedCourseIds);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('course_name', 'like', "%{$search}%")
                    ->orWhere('course_title', 'like', "%{$search}%");
            });
        }

        $courses = $query->get();

        $this->logAction('accessed_create_specialization_form', [
            'professor_id' => $professor->id,
            'professor_name' => $professor->name ?? 'N/A'
        ]);

        return view('records.specializations.create', compact('professor', 'courses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Professor $professor)
    {
        $validatedData = $request->validate([
            'courses'   => 'required|array',
            'courses.*' => 'exists:courses,id'
        ]);

        if (empty($validatedData['courses'])) {
            return view('records.specializations.create', [
                'professor' => $professor,
                'courses'   => Course::all(),
                'message'   => 'No courses were selected for this professor.'
            ]);
        }

        foreach ($validatedData['courses'] as $courseId) {
            $specialization = $professor->specializations()->create([
                'course_id' => $courseId,
            ]);

            // Log each course added
            $this->logAction('create_specialization', [
                'professor_id' => $professor->id,
                'professor_name' => $professor->name ?? 'N/A',
                'course_id' => $courseId,
                'specialization_id' => $specialization->id
            ]);
        }

        return redirect()->route('professors.specializations.index', $professor)
            ->with('success', 'Specializations added successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Professor $professor, Specialization $specialization)
    {
        $specializationData = [
            'professor_id' => $professor->id,
            'professor_name' => $professor->name ?? 'N/A',
            'course_id' => $specialization->course_id,
            'specialization_id' => $specialization->id
        ];

        $specialization->delete();

        $this->logAction('delete_specialization', $specializationData);

        return redirect()->route('professors.specializations.index', $professor)
            ->with('success', 'Specialization deleted successfully.');
    }

    /**
     * Log user actions.
     */
    protected function logAction(string $action, array $details = [])
    {
        if (auth()->check()) {
            UserLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'description' => json_encode($details),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }
}
