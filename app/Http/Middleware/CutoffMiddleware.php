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
        $start   = get_start_time_admin_verify();
        $end     = get_max_time_admin_verify();

        if (($current < $start || $current > $end) && in_array(auth()->user()->role, ['admin'])) {
            return redirect()->back()->with('messageError', 'Admin tidak dapat melakukan proses verifikasi/hapus data transaksi di jam cutoff.');
        }

        return $next($request);
    }
}
