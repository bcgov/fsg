<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CustomUpController extends Controller
{
    public function index()
    {
        // Assuming you're using the default 'file' session driver
        DB::table('sessions')->where('last_activity', '<', now()->subMinutes(config('session.lifetime'))->timestamp)->delete();

        // Count active sessions
        $activeSessions = DB::table('sessions')->count();

        return response()->view('custom_up', [
            'activeSessions' => $activeSessions,
            'requestTime' => now()->format('H:i:s'),
        ]);
    }
}
