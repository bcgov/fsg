<?php

namespace Modules\Institution\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProgramYear;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Response;

class ProgramYearController extends Controller
{
    /**
     * Show the specified resource.
     */
    public function setDefault(Request $request)
    {
        $user = User::find(Auth::user()->id);

        $programYear = ProgramYear::where('guid', $request->input('program_year_guid'))->first();

        Cache::forget('global_program_years_' . $user->institution->guid);
        Cache::remember('global_program_years_' . $user->institution->guid, now()->addHours(10), function () use ($programYear, $user) {
            $programYears = ProgramYear::orderBy('id')->get();

            $programs = $user->institution->programs
                ->sortBy('program_name') // Sort by program_name in ascending order
                ->pluck('program_name', 'guid')
                ->toArray();
            return [
                'list' => $programYears,
                'default' => $programYear->guid,
                'programs' => $programs,
            ];
        });

        return Response::json(['status' => true, 'body' => $programYear]);
    }
}
