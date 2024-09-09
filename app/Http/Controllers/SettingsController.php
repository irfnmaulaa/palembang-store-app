<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('super_admin');
    }

    public function index(Request $request)
    {
        $working_start = Setting::where('key', 'working_start')->first();
        if ($working_start) {
            $working_start = $working_start->value;
        } else {
            $working_start = '07:00:00';
        }

        $working_end = Setting::where('key', 'working_end')->first();
        if ($working_end) {
            $working_end = $working_end->value;
        } else {
            $working_end = '17:00:00';
        }

        return view('admin.settings.index', compact('working_start', 'working_end'));
    }

    public function store(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            Setting::updateOrCreate([
                'key' => $key
            ], [
                'value' => $value
            ]);
        }

        return redirect()->back()->with('message', 'Pengaturan berhasil disimpan');
    }
}
