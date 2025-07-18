<?php

namespace App\Http\Controllers;

use App\Models\Demographic;
use App\Models\ShareableEntity;
use App\Models\Student;
use App\Models\StudentDemographicShare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class DemographicSharingController extends Controller
{
    /**
     * Display the demographics sharing page for a student.
     */
    public function index()
    {
        $student = Student::where('user_guid', Auth::user()->guid)->first();
        
        if (!$student) {
            return Redirect::route('student.home');
        }

        // Get all active demographics with student's answers
        $demographics = Demographic::active()
            ->with(['options', 'studentDemographics' => function ($query) use ($student) {
                $query->where('student_guid', $student->guid);
            }])
            ->get();

        // Get all active shareable entities
        $shareableEntities = ShareableEntity::active()->orderBy('name')->get();

        // Get existing sharing preferences for this student
        $existingShares = StudentDemographicShare::where('student_guid', $student->guid)
            ->with(['demographic', 'shareableEntity'])
            ->get()
            ->groupBy('demographic_id')
            ->mapWithKeys(function ($shares, $demographicId) {
                return [
                    $demographicId => $shares->pluck('shareableEntity.id', 'shareable_entity_id')
                        ->map(function ($entityId, $shareableEntityId) use ($shares) {
                            $share = $shares->firstWhere('shareable_entity_id', $shareableEntityId);
                            return [
                                'entity_id' => $entityId,
                                'is_shared' => $share->is_shared,
                                'shared_at' => $share->shared_at,
                                'revoked_at' => $share->revoked_at,
                            ];
                        })
                ];
            });

        return Inertia::render('Student::DemographicSharing', [
            'demographics' => $demographics,
            'shareableEntities' => $shareableEntities,
            'existingShares' => $existingShares,
            'student' => $student
        ]);
    }

    /**
     * Update demographic sharing preferences.
     */
    public function update(Request $request)
    {
        $student = Student::where('user_guid', Auth::user()->guid)->first();
        
        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $request->validate([
            'demographic_id' => 'required|exists:demographics,id',
            'entity_id' => 'required|exists:shareable_entities,id',
            'is_shared' => 'required|boolean',
        ]);

        $demographicId = $request->input('demographic_id');
        $entityId = $request->input('entity_id');
        $isShared = $request->input('is_shared');

        // Check if student has answered this demographic
        $hasAnswered = DB::table('student_demographics')
            ->where('student_guid', $student->guid)
            ->where('demographic_id', $demographicId)
            ->exists();

        if (!$hasAnswered) {
            return response()->json(['error' => 'Cannot share demographic without answering it first'], 400);
        }

        // Find or create the sharing record
        $share = StudentDemographicShare::updateOrCreate(
            [
                'student_guid' => $student->guid,
                'demographic_id' => $demographicId,
                'shareable_entity_id' => $entityId,
            ],
            [
                'is_shared' => $isShared,
                'shared_at' => $isShared ? now() : null,
                'revoked_at' => $isShared ? null : now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => $isShared ? 'Demographic shared successfully' : 'Sharing revoked successfully',
            'share' => $share
        ]);
    }

    /**
     * Get sharing statistics for ministry dashboard.
     */
    public function statistics()
    {
        $stats = DB::table('student_demographic_shares')
            ->join('demographics', 'student_demographic_shares.demographic_id', '=', 'demographics.id')
            ->join('shareable_entities', 'student_demographic_shares.shareable_entity_id', '=', 'shareable_entities.id')
            ->select(
                'demographics.question as demographic_question',
                'shareable_entities.name as entity_name',
                DB::raw('COUNT(CASE WHEN is_shared = true THEN 1 END) as shared_count'),
                DB::raw('COUNT(*) as total_count')
            )
            ->groupBy('demographics.id', 'shareable_entities.id', 'demographics.question', 'shareable_entities.name')
            ->get();

        return Inertia::render('Ministry::DemographicSharingStats', [
            'statistics' => $stats
        ]);
    }
}
