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
        $working_time = Setting::where('key', 'working_time')->first();
        return view('admin.settings.index', compact('working_time'));
    }
}
