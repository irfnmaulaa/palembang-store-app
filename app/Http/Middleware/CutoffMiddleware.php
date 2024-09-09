<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;

class CutoffMiddleware
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
        $current = date('H:i:s');

        $start = Setting::where('key', 'working_start')->first();
        if ($start) {
            $start = $start->value;
        } else {
            $start = '07:00:00';
        }

        $end = Setting::where('key', 'working_end')->first();
        if ($end) {
            $end = $end->value;
        } else {
            $end = '17:00:00';
        }

        if (($current < $start || $current > $end) && in_array(auth()->user()->role, ['admin', 'staff'])) {
            return redirect()->back()->with('messageError', 'Anda tidak dapat melakukan proses apapun di jam cutoff.');
        }

        return $next($request);
    }
}
