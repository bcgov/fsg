<?php

namespace Modules\Institution\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProgramYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Response;

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
                'default' => $programYear->guid,
            ];
        });

        return Response::json(['status' => true, 'body' => $programYear]);
    }
}
