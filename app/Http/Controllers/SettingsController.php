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

    public function index()
    {
        // define working start
        $working_start = Setting::where('key', 'working_start')->first();
        if ($working_start) {
            $working_start = $working_start->value;
        } else {
            $working_start = '07:00:00';
        }

        // define working end
        $working_end = Setting::where('key', 'working_end')->first();
        if ($working_end) {
            $working_end = $working_end->value;
        } else {
            $working_end = '17:00:00';
        }

        // define menus
        $menus = get_menus();

        return view('admin.settings.index', compact('working_start', 'working_end', 'menus'));
    }

    public function store(Request $request)
    {
        // validation
        $validated = $request->validate([
            'working_start' => ['required', 'date_format:H:i:s'],
            'working_end' => ['required', 'date_format:H:i:s'],
            'menus' => ['required', 'array'],
        ]);

        // prepare menu
        $menus = [];
        foreach ($validated['menus'] as $menu) {
            $menus[] = json_decode($menu);
        }
        $validated['menus'] = json_encode($menus);

        // update settings
        foreach ($validated as $key => $value) {
            Setting::updateOrCreate([
                'key' => $key
            ], [
                'value' => $value
            ]);
        }

        return redirect()->back()->with('message', 'Pengaturan berhasil disimpan');
    }
}
