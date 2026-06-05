<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardSettingsController extends Controller
{
    public function index()
    {
        return view('dashboard.settings');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'business_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
        ]);

        $request->user()->update($data);

        return back()->with('success', 'Parametres mis a jour.');
    }
}
