<?php

namespace Modules\Ministry\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$roles
     * @return \Inertia\Response
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $roles = empty($roles) ? [null] : $roles;

        if (! Auth::check()) {
            //            return Inertia::render('Auth/Login', [
            //                'loginAttempt' => true,
            //                'hasAccess' => false,
            //                'status' => 'Please login again.',
            //            ]);
            return redirect()->route('login');
        }

        $user = Auth::user();
        if (! $user->hasRole(Role::SUPER_ADMIN) && ! $user->hasRole(Role::Ministry_ADMIN)) {
            return Inertia::render('Auth/Login', [
                'loginAttempt' => true,
                'hasAccess' => false,
                'status' => 'Please contact the Ministry Admin to verify your access.',
            ]);
        }

        return $next($request);
    }
}
