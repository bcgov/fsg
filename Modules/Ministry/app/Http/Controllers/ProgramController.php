<?php

namespace Modules\Ministry\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramEditRequest;
use App\Http\Requests\ProgramStoreRequest;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Response;

class ProgramController extends Controller
{
    public function fetchPrograms(Request $request, ?Program $program = null)
    {
        $body = Program::where(['institution_guid' => $request->input('institution_guid'), 'active_status' => true])->get();
        if (! is_null($program)) {
            $body = $program;
        }

        return Response::json(['status' => true, 'body' => $body]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProgramStoreRequest $request): \Illuminate\Http\RedirectResponse
    {
        $program = Program::create($request->validated());

        $program = Program::find($program->id);

        return Redirect::route('ministry.institutions.show', [$program->institution->id, 'programs']);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProgramEditRequest $request): \Illuminate\Http\RedirectResponse
    {
        $program_id = Program::where('id', $request->id)->update($request->validated());
        $program = Program::find($request->id);

        return Redirect::route('ministry.institutions.show', [$program->institution->id, 'programs']);
    }
}
