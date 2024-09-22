<?php

namespace App\Http\Middleware;

use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class ActivationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (in_array(auth()->user()->role, ['staff'])) {
            // if user inactive
            if (!auth()->user()->is_active) {
                abort(405);
            }

            // user try to access > get max time user active
            elseif (date('Y-m-d H:i:s') > Carbon::parse(auth()->user()->last_login_at)->format('Y-m-d') . ' ' . get_max_time_user_active()) {
                User::find(auth()->user()->id)->update([
                    'is_active' =>false,
                ]);
                abort(405);
            }
        }
        return $next($request);
    }
}
