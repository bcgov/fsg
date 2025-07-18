<?php

namespace Modules\Student\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Demographic;
use App\Models\ShareableEntity;
use App\Models\Student;
use App\Models\StudentDemographicShare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DemographicSharingController extends Controller
{
    /**
     * Display the demographic sharing page.
     */
    public function index()
    {
        $student = Student::where('user_guid', Auth::user()->guid)->first();
        
        if (!$student) {
            return redirect()->route('student.home')->with('error', 'Student profile not found.');
        }

        // Get all active demographics with student answers
        $demographics = Demographic::active()
            ->with([
                'studentDemographics' => function ($query) use ($student) {
                    $query->where('student_guid', $student->guid)
                        ->with('answers');
                }
            ])
            ->orderBy('question')
            ->get();

        // Get all active shareable entities
        $shareableEntities = ShareableEntity::active()
            ->orderBy('name')
            ->get();

        // Get existing shares for this student
        $existingShares = StudentDemographicShare::where('student_guid', $student->guid)
            ->with(['demographic', 'shareableEntity'])
            ->get()
            ->groupBy('demographic_id')
            ->map(function ($shares) {
                return $shares->keyBy('shareable_entity_id')->map(function ($share) {
                    return [
                        'entity_id' => $share->shareable_entity_id,
                        'is_shared' => $share->is_shared,
                        'shared_at' => $share->shared_at,
                        'revoked_at' => $share->revoked_at,
                    ];
                });
            })
            ->toArray();

        return Inertia::render('Student::DemographicSharing', [
            'demographics' => $demographics,
            'shareableEntities' => $shareableEntities,
            'existingShares' => $existingShares,
            'student' => $student,
        ]);
    }

    /**
     * Update sharing preference for a demographic and entity.
     */
    public function updateSharing(Request $request)
    {
        $request->validate([
            'demographic_id' => 'required|exists:demographics,id',
            'entity_id' => 'required|exists:shareable_entities,id',
            'is_shared' => 'required|boolean',
        ]);

        $student = Student::where('user_guid', Auth::user()->guid)->first();
        
        if (!$student) {
            return response()->json([
                'success' => false,
                'error' => 'Student profile not found.'
            ], 404);
        }

        // Check if student has answered this demographic
        $hasAnswered = $student->demographics()
            ->where('demographic_id', $request->demographic_id)
            ->exists();

        if (!$hasAnswered) {
            return response()->json([
                'success' => false,
                'error' => 'You must answer this demographic question before you can share it.'
            ], 400);
        }

        try {
            DB::transaction(function () use ($request, $student) {
                $share = StudentDemographicShare::updateOrCreate(
                    [
                        'student_guid' => $student->guid,
                        'demographic_id' => $request->demographic_id,
                        'shareable_entity_id' => $request->entity_id,
                    ],
                    [
                        'is_shared' => $request->is_shared,
                        'shared_at' => $request->is_shared ? now() : null,
                        'revoked_at' => $request->is_shared ? null : now(),
                    ]
                );
            });

            $action = $request->is_shared ? 'shared' : 'revoked';
            $message = "Sharing preference {$action} successfully.";

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating demographic sharing preference', [
                'student_guid' => $student->guid,
                'demographic_id' => $request->demographic_id,
                'entity_id' => $request->entity_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to update sharing preference. Please try again.'
            ], 500);
        }
    }

    /**
     * Get sharing summary for the student.
     */
    public function getSharingSummary()
    {
        $student = Student::where('user_guid', Auth::user()->guid)->first();
        
        if (!$student) {
            return response()->json([
                'success' => false,
                'error' => 'Student profile not found.'
            ], 404);
        }

        $summary = StudentDemographicShare::where('student_guid', $student->guid)
            ->where('is_shared', true)
            ->with(['demographic', 'shareableEntity'])
            ->get()
            ->groupBy('demographic_id')
            ->map(function ($shares) {
                $demographic = $shares->first()->demographic;
                $entities = $shares->pluck('shareableEntity');
                
                return [
                    'demographic' => $demographic->question,
                    'entities' => $entities->pluck('name'),
                    'count' => $entities->count(),
                    'shared_at' => $shares->max('shared_at'),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'total_shared' => $summary->count()
        ]);
    }

    /**
     * Revoke all sharing for a student.
     */
    public function revokeAllSharing()
    {
        $student = Student::where('user_guid', Auth::user()->guid)->first();
        
        if (!$student) {
            return response()->json([
                'success' => false,
                'error' => 'Student profile not found.'
            ], 404);
        }

        try {
            DB::transaction(function () use ($student) {
                StudentDemographicShare::where('student_guid', $student->guid)
                    ->where('is_shared', true)
                    ->update([
                        'is_shared' => false,
                        'revoked_at' => now()
                    ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'All sharing preferences have been revoked successfully.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error revoking all demographic sharing', [
                'student_guid' => $student->guid,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to revoke sharing preferences. Please try again.'
            ], 500);
        }
    }
}
