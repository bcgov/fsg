<?php

namespace Modules\Institution\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProgramYear;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class ProgramYearController extends Controller
{
    /**
     * Show the specified resource.
     */
    public function setDefault(Request $request)
    {
        $programYear = ProgramYear::where('guid', $request->input('program_year_guid'))->first();

        Cache::forget('global_program_years');
        Cache::remember('global_program_years', now()->addHours(10), function () use ($programYear) {
            $programYears = ProgramYear::orderBy('id')->get();
            return [
                'list' => $programYears,
                'default' => $programYear->guid
            ];
        });

        return Response::json(['status' => true, 'body' => $programYear]);
    }
}
